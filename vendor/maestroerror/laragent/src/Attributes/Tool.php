<?php

namespace LarAgent\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_FUNCTION)]
class Tool
{
    public function __construct(
        public readonly string $description,
        public readonly array $parameterDescriptions = []
    ) {}
}
