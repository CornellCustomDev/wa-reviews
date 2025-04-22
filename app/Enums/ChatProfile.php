<?php

namespace App\Enums;

enum ChatProfile: string
{
    case Reasoning = 'reasoning';
    case Chat = 'chat';
    case Task = 'task';
    case Default = 'default';
}
