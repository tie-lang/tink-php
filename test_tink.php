<?php
// test_tink.php —— unit tests for tink.php. Run: php test_tink.php
require __DIR__ . '/tink.php';

$failures = 0;

function check(bool $cond, string $name): void {
    global $failures;
    if ($cond) {
        echo "[PASS] ", $name, "\n";
    } else {
        $failures++;
        echo "[FAIL] ", $name, "\n";
    }
}

// crc32 check vector
check(tink\crc32("123456789") === 0xCBF43926, "crc32 vector");

// frame roundtrip
$p = "\x01\x02\x03";
$frame = tink\frame_encode($p);
check(strlen($frame) === strlen($p) + 8, "frame length");
$got = tink\frame_next($frame, 0);
check($got !== null, "frame present");
if ($got !== null) {
    [$payload, $next] = $got;
    check($next === strlen($frame), "frame next == length");
    check($payload === $p, "frame payload roundtrip");
}

// empty frame roundtrip
$fe = tink\frame_encode("");
$ge = tink\frame_next($fe, 0);
check($ge !== null && $ge[1] === strlen($fe) && $ge[0] === "", "empty frame roundtrip");

// CRC tamper rejected
$ft = tink\frame_encode($p);
$ft[4] = chr(ord($ft[4]) + 1); // tamper payload[0]
check(tink\frame_next($ft, 0) === null, "crc tamper rejected");

// frame_skip matches length
$fs = tink\frame_encode($p);
check(tink\frame_skip($fs, 0) === strlen($fs), "frame_skip matches length");

// out of bounds
check(tink\frame_next($frame, strlen($frame)) === null, "frame_next out of bounds");
check(tink\frame_skip($frame, strlen($frame)) === null, "frame_skip out of bounds");
check(tink\frame_next("", 0) === null, "frame_next empty input");

if ($failures > 0) {
    echo $failures, " checks FAILED\n";
    exit(1);
}
echo "all tests passed\n";