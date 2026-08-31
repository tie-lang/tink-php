<?php
/**
 * tink.php —— tink data-flow node frame protocol (universal, language-agnostic).
 *
 * Frame = [len u32 BE][payload][crc u32 BE]; crc = CRC32-IEEE (0xEDB88320).
 * Mirrors std/tink.tie (tie standard library) and the other-language tink
 * libraries; pure functions over strings (byte vectors), IO (stdin/stdout)
 * left to the caller. PHP 7+, no dependencies.
 *
 *   $frame = tink\frame_encode("hi");
 *   [$payload, $next] = tink\frame_next($frame, 0);
 */

namespace tink;

/**
 * CRC32-IEEE over a string (bit-loop, no table; matches hash('crc32b')).
 * Check vector: crc32("123456789") == 0xCBF43926.
 */
function crc32(string $data): int {
    $crc = 0xFFFFFFFF;
    $len = strlen($data);
    for ($i = 0; $i < $len; $i++) {
        $crc ^= ord($data[$i]);
        for ($k = 0; $k < 8; $k++) {
            $crc = ($crc & 1) ? (($crc >> 1) ^ 0xEDB88320) : ($crc >> 1);
        }
    }
    return ($crc ^ 0xFFFFFFFF) & 0xFFFFFFFF;
}

/**
 * Encode a payload into a full frame: [len u32 BE][payload][crc u32 BE].
 * Returns a string of strlen(payload) + 8 bytes.
 */
function frame_encode(string $payload): string {
    $n = strlen($payload);
    $out = chr(($n >> 24) & 0xFF) . chr(($n >> 16) & 0xFF) . chr(($n >> 8) & 0xFF) . chr($n & 0xFF);
    $out .= $payload;
    $c = crc32($payload);
    $out .= chr(($c >> 24) & 0xFF) . chr(($c >> 16) & 0xFF) . chr(($c >> 8) & 0xFF) . chr($c & 0xFF);
    return $out;
}

/**
 * Parse one frame at $pos (verifies CRC). Returns [payload, next_pos] (both
 * positions are 0-based byte offsets) or null on out-of-bounds / CRC mismatch.
 * The payload is a copy (substring).
 */
function frame_next(string $bytes, int $pos): ?array {
    if ($pos < 0 || strlen($bytes) < $pos + 8) {
        return null;
    }
    $n = be32($bytes, $pos);
    $end = $pos + 8 + $n;
    if (strlen($bytes) < $end) {
        return null;
    }
    $payload = substr($bytes, $pos + 4, $n);
    $want = be32($bytes, $end - 4);
    if (crc32($payload) !== $want) {
        return null;
    }
    return [$payload, $end];
}

/**
 * Skip one frame at $pos without copying or verifying (zero-copy).
 * Returns next_pos, or null on out-of-bounds.
 */
function frame_skip(string $bytes, int $pos): ?int {
    if ($pos < 0 || strlen($bytes) < $pos + 8) {
        return null;
    }
    $n = be32($bytes, $pos);
    $end = $pos + 8 + $n;
    if (strlen($bytes) < $end) {
        return null;
    }
    return $end;
}

/** Read a big-endian u32 at byte offset $off. */
function be32(string $b, int $off): int {
    return (ord($b[$off]) << 24) | (ord($b[$off + 1]) << 16) |
           (ord($b[$off + 2]) << 8) | ord($b[$off + 3]);
}