<?php

declare(strict_types=1);

namespace OpenRouter\DTO;

/**
 * Builders for the `input_references` array accepted by /images (likeness/style images)
 * and /audio/speech (reference voice clip + its transcript, for voice cloning).
 */
final class InputReference
{
    /** @return array<string, mixed> */
    public static function imageFromBase64(string $base64, string $mime = 'image/png'): array
    {
        return ['type' => 'image_url', 'image_url' => ['url' => "data:$mime;base64,$base64"]];
    }

    /** @return array<string, mixed> */
    public static function imageFromFile(string $path): array
    {
        return self::imageFromBase64(base64_encode(self::read($path)), mime_content_type($path) ?: 'image/png');
    }

    /** @return array<string, mixed> */
    public static function imageFromUrl(string $url): array
    {
        return ['type' => 'image_url', 'image_url' => ['url' => $url]];
    }

    /** @return array<string, mixed> */
    public static function audioFromFile(string $path, string $mime = 'audio/wav'): array
    {
        return ['type' => 'input_audio', 'input_audio' => ['data' => "data:$mime;base64," . base64_encode(self::read($path))]];
    }

    /** @return array<string, mixed> */
    public static function text(string $text): array
    {
        return ['type' => 'text', 'text' => $text];
    }

    /**
     * Voice-cloning references: the clip plus the exact words spoken in it.
     * @return array<int, array<string, mixed>>
     */
    public static function voice(string $audioPath, string $transcript, string $mime = 'audio/wav'): array
    {
        return [self::audioFromFile($audioPath, $mime), self::text(trim($transcript))];
    }

    private static function read(string $path): string
    {
        $data = @file_get_contents($path);
        if ($data === false) {
            throw new \InvalidArgumentException("Cannot read $path");
        }

        return $data;
    }
}
