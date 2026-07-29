<?php

declare(strict_types=1);

namespace OpenRouter\DTO;

/**
 * A single chat message, sent as part of a request or received as part of a
 * response. `content` may be a plain string or an array of multimodal
 * content parts (text/image_url/input_audio/...), matching the API shape.
 */
final class ChatMessage
{
    /**
     * @param string|array<int, array<string, mixed>>|null $content
     * @param array<int, array<string, mixed>>|null $toolCalls
     * @param array<string, mixed> $raw
     */
    public function __construct(
        public readonly string $role,
        public readonly string|array|null $content = null,
        public readonly ?string $name = null,
        public readonly ?string $toolCallId = null,
        public readonly ?array $toolCalls = null,
        public readonly ?string $reasoning = null,
        public readonly array $raw = [],
    ) {
    }

    /**
     * @param string|array<int, array<string, mixed>> $content
     */
    public static function system(string|array $content): self
    {
        return new self('system', $content);
    }

    /**
     * @param string|array<int, array<string, mixed>> $content
     */
    public static function user(string|array $content): self
    {
        return new self('user', $content);
    }

    /**
     * @param string|array<int, array<string, mixed>>|null $content
     * @param array<int, array<string, mixed>>|null $toolCalls
     */
    public static function assistant(string|array|null $content = null, ?array $toolCalls = null): self
    {
        return new self('assistant', $content, toolCalls: $toolCalls);
    }

    public static function tool(string $content, string $toolCallId): self
    {
        return new self('tool', $content, toolCallId: $toolCallId);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            role: (string) ($data['role'] ?? ''),
            content: $data['content'] ?? null,
            name: $data['name'] ?? null,
            toolCallId: $data['tool_call_id'] ?? null,
            toolCalls: $data['tool_calls'] ?? null,
            reasoning: $data['reasoning'] ?? null,
            raw: $data,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = ['role' => $this->role];

        if ($this->content !== null) {
            $data['content'] = $this->content;
        }

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }

        if ($this->toolCallId !== null) {
            $data['tool_call_id'] = $this->toolCallId;
        }

        if ($this->toolCalls !== null) {
            $data['tool_calls'] = $this->toolCalls;
        }

        return $data;
    }
}
