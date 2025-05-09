<?php

namespace LarAgent\Messages;

use LarAgent\Core\Abstractions\Message;
use LarAgent\Core\Contracts\Message as MessageInterface;
use LarAgent\Core\Enums\Role;

class ToolResultMessage extends Message implements MessageInterface
{
    // Public properties are returned in the array representation of the object

    public function __construct(array $message, array $metadata = [])
    {
        parent::__construct(Role::TOOL->value, '', $metadata);
        $this->buildFromArray($message);
    }
}
