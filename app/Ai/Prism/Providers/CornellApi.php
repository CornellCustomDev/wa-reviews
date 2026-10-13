<?php

namespace App\Ai\Prism\Providers;

use Generator;
use Prism\Prism\Providers\OpenAI\OpenAI;
use Prism\Prism\Streaming\Events\StreamEvent;
use Prism\Prism\Text\Request as TextRequest;

class CornellApi extends OpenAI
{
    /**
     * Stream through a handler that tolerates the gateway's non-OpenAI SSE lines.
     *
     * @return Generator<StreamEvent>
     */
    #[\Override]
    public function stream(TextRequest $request): Generator
    {
        $handler = new CornellStream($this->client(
            $request->clientOptions(),
            $request->clientRetry()
        ));

        return $handler->handle($request);
    }
}
