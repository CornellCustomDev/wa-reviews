<?php

namespace App\Enums;

trait NamedEnum
{
    public static function fromName(string $name): ?static
    {
        $named_cases = collect(self::cases())->keyBy('name');
        return $named_cases->get($name);
    }

    public function value(): string|int
    {
        return $this->value ?? $this->name;
    }

    public static function names(): array
    {
        return array_column(self::cases(), 'name');
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
