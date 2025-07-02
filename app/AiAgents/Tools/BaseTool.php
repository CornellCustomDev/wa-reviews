<?php

namespace App\AiAgents\Tools;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use LarAgent\Tool;
use Throwable;

/**
 * Abstract convenience layer over LarAgent\Tool for rapid tool creation.
 *
 * Sub‑classes should:
 *   1. Override `protected static array $schema` with each parameter’s metadata.
 *   2. Optionally set `$name` and `$description`.
 *   3. Implement `handle(array $input): mixed` – the only required method.
 */
abstract class BaseTool extends Tool
{
    /**
     * Human‑readable identifier for the tool.
     * Defaults to snake_case class basename without the “Tool” suffix.
     */
    protected string $name = '';

    /**
     * One‑sentence description shown to the LLM.
     */
    protected string $description = '';

    /**
     * Whether to enable OpenAI strict mode.
     * See: https://platform.openai.com/docs/guides/function-calling?api-mode=chat#strict-mode
     */
    protected bool $strict = true;

    /**
     * Parameter schema.
     *
     * Format:
     *   'paramName' => [
     *       'type'        => 'string|integer|array|boolean|float',
     *       'description' => '…',
     *       'required'    => bool,            // default true
     *       'enumClass'   => MyEnum::class,   // optional – auto‑converted
     *       'enum'        => [...],           // optional – static enum list
     *   ]
     */
    protected static array $schema = [];

    public function __construct()
    {
        $name = $this->name ?: Str::of(class_basename(static::class))->replaceLast('Tool', '')->snake();
        $description = $this->description ?: Str::headline(class_basename(static::class));

        parent::__construct($name, $description);

        $this->properties = static::$schema;
        foreach (static::$schema as $name => $property) {
            $this->addProperty(
                $name,
                $property['type'] ?? 'string',
                $property['description'] ?? '',
                $property['enum'] ?? []
            );

            // Base "addProperty" does not handle items, minItems, maxItems, so we handle those manually
            if ($property['type'] == 'array' && isset($property['items'])) {
                $this->properties[$name]['items'] = $property['items'];
                if (isset($property['minItems'])) {
                    $this->properties[$name]['minItems'] = $property['minItems'];
                }
                if (isset($property['maxItems'])) {
                    $this->properties[$name]['maxItems'] = $property['maxItems'];
                }
            }

            // @TODO: Handle allowed string and number properties
            // Per https://platform.openai.com/docs/guides/structured-outputs?api-mode=chat#supported-properties

            if ($this->strict) {
                $this->setRequired($name);
            }
        }
    }

    public function useStrict(): bool
    {
        return $this->strict;
    }

    /**
     * @inheritDoc
     *
     * Overrides LarAgent\Tool::toArray() to address OpenAI strict mode
     * See: https://platform.openai.com/docs/guides/function-calling?api-mode=chat#strict-mode
     */
    public function toArray(): array
    {
        $toolDefinition = parent::toArray();

        if ($this->strict) {
            $toolDefinition['function']['strict'] = true;
            $toolDefinition['function']['parameters']['additionalProperties'] = false;
        }

        return $toolDefinition;
    }

    /**
     * Entry point executed by LarAgent.
     * Handles validation, enum coercion and error wrapping.
     */
    public function execute(array $input): mixed
    {
        $errors = $this->validateInput($input);

        if (! empty($errors)) {
            return ['error' => 'validation_failed', 'details' => $errors];
        }

        try {
            return $this->handle($input);
        } catch (Throwable $e) {
            report($e);
            return [
                'error'   => 'unhandled_exception',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validates $input against the declared schema.
     */
    protected function validateInput(array $input): array
    {
        $errors = [];
        $required = $this->getRequired();

        foreach($this->getProperties() as $name => $meta) {
            // Check for required properties
            if (Arr::exists($required, $name) && !Arr::has($input, $name)) {
                $errors[$name][] = 'required';
                continue;
            }

            $type = $meta['type'];
            $inputVal = $input[$name];

            if (!$this->valueMatchesType($inputVal, $type)) {
                $errors[$name][] = "must_be_{$type}";
                continue;
            }

            if ($error = $this->valueMeetsConstraints($inputVal, $meta)) {
                $errors[$name][] = $error;
            }
        }

        if ($this->strict) {
            $unknownInputs = array_diff_key($input, $this->getProperties());
            if (!empty($unknownInputs)) {
                $errors['unknown_inputs'] = array_keys($unknownInputs);
            }
        }

        return $errors;
    }

    /**
     * Simple type matcher for scalar types used in prompts.
     *
     * See: https://platform.openai.com/docs/guides/structured-outputs?api-mode=chat#supported-schemas
     */
    protected function valueMatchesType(mixed $value, string|array $type): bool
    {
        if (is_array($type)) {
            // At least one type must match
            return collect($type)->contains(fn ($t) => $this->valueMatchesType($value, $t));
        }

        return match ($type) {
            'string'   => is_string($value),
            'number'   => is_numeric($value),
            'boolean'  => is_bool($value),
            'integer'  => is_int($value) || (is_numeric($value) && (int)$value == $value),
            'object'   => is_object($value),
            'array'    => is_array($value),
            // @TODO: Need to do more to confirm enum types?
            'enum'     => is_string($value),
            'null'     => empty($value),
            // If strict, only allow known types
            default    => $this->strict ? false : true,
        };
    }

    /**
     * Checks if the value meets any additional constraints defined in the schema.
     *
     * See: https://platform.openai.com/docs/guides/structured-outputs?api-mode=chat#supported-properties
     */
    protected function valueMeetsConstraints(mixed $value, array $meta): ?string
    {
        $type = $meta['type'];

        // @TODO: Implement string, number, and enum constraints

        if ($type === 'array') {
            $itemsType = $meta['items']['type'] ?? null;
            foreach ($value as $v) {
                if (!$this->valueMatchesType($v, $itemsType)) {
                    return "array_item_must_be_{$itemsType}";
                }
            }

            $count = count($value);
            $minItems  = $meta['minItems'] ?? null;
            if ($minItems && $count < $minItems) {
                return "too_few_array_items";
            }

            $maxItems  = $meta['maxItems'] ?? null;
            if ($maxItems && $count > $maxItems) {
                return "too_many_array_items";
            }
        }

        return null;
    }

    /**
     * Static entry point for usage outside an AI agent context.
     */
    public static function call(?array $input = []): mixed
    {
        return (new static())->execute($input);
    }

    /**
     * Sub‑classes implement the core behavior here.
     */
    abstract protected function handle(array $input): mixed;
}

