<?php

namespace LarAgent\Messages;

use LarAgent\Core\Abstractions\Message;
use LarAgent\Core\Contracts\Message as MessageInterface;
use LarAgent\Core\Enums\Role;

class UserMessage extends Message implements MessageInterface
{
    public function __construct(string $content, array $metadata = [])
    {
        parent::__construct(Role::USER->value, $content, $metadata);
    }

    public function withImage(string $imageUrl): self
    {
        $content = $this->getContent();
        $imageArray = [
            'type' => 'image_url',
            'image_url' => [
                'url' => $imageUrl,
            ],
        ];

        if (is_string($content)) {
            $this->setContent([
                [
                    'type' => 'text',
                    'text' => $content,
                ],
                [...$imageArray],
            ]);
        }

        if (is_array($content)) {
            $content[] = $imageArray;
            $this->setContent($content);
        }

        return $this;
    }
}
