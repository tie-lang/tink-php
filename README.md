# tink-php

tink data-flow node frame protocol — PHP namespace (no dependencies).
Universal and language-agnostic: any component that obeys the frame protocol
can join a tink pipeline.

```
帧 = [ len: u32 BE ][ payload: len 字节 ][ crc: u32 BE ]
len = payload 字节数
crc = CRC32-IEEE(payload)（多项式 0xEDB88320）
```

Mirrors `std/tink.tie` (tie standard library) and the other-language tink
libraries; pure functions over strings (byte vectors), IO (stdin/stdout) left
to the caller. Names follow the PHP convention (`tink\` namespace,
snake_case functions).

## API (`namespace tink`)

| function | description |
| --- | --- |
| `tink\crc32(string) -> int` | CRC32-IEEE over a byte string. Check vector: `crc32("123456789") == 0xCBF43926` |
| `tink\frame_encode(string) -> string` | encode a payload into a full frame `[len][payload][crc]` |
| `tink\frame_next(string, int) -> array \| null` | parse one frame at `pos`, verify CRC; `[payload, next]` on success, `null` on out-of-bounds / mismatch |
| `tink\frame_skip(string, int) -> int \| null` | skip one frame at `pos` without copying or verifying; `null` on out-of-bounds |

## Usage

```php
use function tink\frameEncode; // or fully-qualified tink\frame_encode
$frame = tink\frame_encode("\x01\x02\x03");
$got = tink\frame_next($frame, 0); // [payload, next] or null
```

## Test

```bash
php test_tink.php
```

## Cross-language

tink 帧协议各语言实现（API 语义与校验向量一致）：

| language | library |
| --- | --- |
| tie | `std/tink.tie` |
| Rust | `tink-rust`（tink crate） |
| C | `tink-c`（`tink.h` + `tink.c`） |
| Python | `tink-python`（`tink.py`） |
| JavaScript | `tink-js`（`tink.js` + `tink.d.ts`） |
| C++ | `tink-cpp`（`tink.hpp`） |
| Java | `tink-java`（`org.tielang.tink`） |
| C# | `tink-csharp`（namespace `Tink`） |
| Go | `tink-go`（package `tink`） |
| Zig | `tink-zig`（`tink.zig`） |
| Lua | `tink-lua`（`tink.lua`） |
| GDScript | `tink-godot`（`tink.gd`） |
| F# | `tink-fsharp`（`Tink.fs`） |
| PowerShell | `tink-powershell`（`tink.ps1`） |
| Kotlin | `tink-kotlin`（`Tink.kt`） |
| Ruby | `tink-ruby`（`tink.rb`） |
| Julia | `tink-julia`（`tink.jl`） |
| Nim | `tink-nim`（`tink.nim`） |
| Dart | `tink-dart`（`tink.dart`） |
| Crystal | `tink-crystal`（`tink.cr`） |
| PHP | this library（`tink-php`） |

## License

本仓库使用 **Tie Public License v1.2 (TPL 1.2)**，完整文本见 [LICENSE](LICENSE)。
This repository is distributed under the **Tie Public License v1.2 (TPL 1.2)** — see [LICENSE](LICENSE) for the full text.