<?php

declare(strict_types=1);

namespace Prism\Prism\ValueObjects\Messages;

use Prism\Prism\Concerns\HasProviderOptions;
use Prism\Prism\Contracts\Message;
use Prism\Prism\ValueObjects\Messages\Support\Document;
use Prism\Prism\ValueObjects\Messages\Support\Image;
use Prism\Prism\ValueObjects\Messages\Support\OpenAIFile;
use Prism\Prism\ValueObjects\Messages\Support\Text;

class UserMessage implements Message
{
    use HasProviderOptions;

    /**
     * @param  array<int, Text|Image|Document|OpenAIFile>  $additionalContent
     * @param  array<string, mixed>  $additionalAttributes
     */
    public function __construct(
        public readonly string $content,
        public array $additionalContent = [],
        public readonly array $additionalAttributes = [],
    ) {
        $this->additionalContent[] = new Text($content);
    }

    public function text(): string
    {
        $result = '';

        foreach ($this->additionalContent as $content) {
            if ($content instanceof Text) {
                $result .= $content->text;
            }
        }

        return $result;
    }

    /**
     * @return Image[]
     */
    public function images(): array
    {
        return collect($this->additionalContent)
            ->where(fn ($part): bool => $part instanceof Image)
            ->toArray();
    }

    /**
     * Note: Prism currently only supports Documents with Anthropic.
     *
     * @return Document[]
     */
    public function documents(): array
    {
        return collect($this->additionalContent)
            ->where(fn ($part): bool => $part instanceof Document)
            ->toArray();
    }

    /**
     * Note: Prism currently only supports previously uploaded Files with OpenAI.
     *
     * @return OpenAIFile[]
     */
    public function files(): array
    {
        return collect($this->additionalContent)
            ->where(fn ($part): bool => $part instanceof OpenAIFile)
            ->toArray();
    }
}
