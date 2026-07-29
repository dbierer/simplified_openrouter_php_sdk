<?php

declare(strict_types=1);

namespace OpenRouter\Streaming;

use Generator;
use Psr\Http\Message\StreamInterface;

/**
 * Parses a text/event-stream body into a sequence of decoded JSON payloads,
 * stopping at the "[DONE]" sentinel OpenRouter (and OpenAI) use to terminate
 * a stream.
 */
final class SseParser
{
    /**
     * @return Generator<int, array<string, mixed>>
     */
    public static function parse(StreamInterface $body): Generator
    {
        $buffer = '';

        while (!$body->eof() || $buffer !== '') {
            if (!$body->eof()) {
                $buffer .= $body->read(8192);
            }

            while (($pos = strpos($buffer, "\n\n")) !== false) {
                $event = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 2);

                $data = self::extractData($event);

                if ($data === null) {
                    continue;
                }

                if ($data === '[DONE]') {
                    return;
                }

                $decoded = json_decode($data, true);

                if (is_array($decoded)) {
                    yield $decoded;
                }
            }

            if ($body->eof() && !str_contains($buffer, "\n\n")) {
                $data = self::extractData($buffer);
                $buffer = '';

                if ($data !== null && $data !== '[DONE]') {
                    $decoded = json_decode($data, true);

                    if (is_array($decoded)) {
                        yield $decoded;
                    }
                }
            }
        }
    }

    private static function extractData(string $event): ?string
    {
        $lines = explode("\n", $event);
        $data = [];

        foreach ($lines as $line) {
            $line = rtrim($line, "\r");

            if (str_starts_with($line, 'data:')) {
                $data[] = ltrim(substr($line, 5), ' ');
            }
        }

        if ($data === []) {
            return null;
        }

        return implode("\n", $data);
    }
}
