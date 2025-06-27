<?php

namespace App\AiAgents\Tools;

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

        foreach (static::$schema as $param => $meta) {
            $exists = array_key_exists($param, $input);
            $required = $meta['required'] ?? true;

            if ($required && !$exists) {
                $errors[$param][] = 'required';
                continue;
            }

            if (!$exists) {
                continue; // optional and missing – fine
            }

            $value = $input[$param];
            $type  = $meta['type'] ?? 'string';

            // basic scalar / array type check
            if (!$this->valueMatchesType($value, $type)) {
                $errors[$param][] = "must_be_{$type}";
            }

            // enum list check
            if (isset($meta['enum']) && !in_array($value, $meta['enum'], true)) {
                $errors[$param][] = 'invalid_enum_value';
            }

            // array item typing and length constraints
            if ($type === 'array') {
                $itemsType = $meta['items']['type'] ?? null;
                $maxItems  = $meta['maxItems'] ?? null;
                $minItems  = $meta['minItems'] ?? null;

                if ($itemsType) {
                    foreach ($value as $i => $v) {
                        if (!$this->valueMatchesType($v, $itemsType)) {
                            $errors[$param][] = "item_{$i}_must_be_{$itemsType}";
                        }
                    }
                }

                $count = count($value);
                if ($maxItems && $count > $maxItems) {
                    $errors[$param][] = "too_many_items";
                }
                if ($minItems && $count < $minItems) {
                    $errors[$param][] = "too_few_items";
                }
            }
        }

        $unknownInputs = array_diff_key($input, static::$schema);
        if (!empty($unknownInputs)) {
            $errors['unknown_inputs'] = array_keys($unknownInputs);
        }

        return $errors;
    }

    /**
     * Simple type matcher for scalar types used in prompts.
     */
    protected function valueMatchesType(mixed $value, string $type): bool
    {
        return match ($type) {
            'integer'  => is_int($value),
            'float'    => is_float($value) || is_numeric($value),
            'array'    => is_array($value),
            'boolean'  => is_bool($value),
            'string'   => is_string($value),
            default    => true,
        };
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

