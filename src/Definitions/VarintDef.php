<?php

namespace iggyvolz\minecraft\Definitions;

use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;
use RuntimeException;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
// https://wiki.vg/Protocol#VarInt_and_VarLong
/** @template-extends Definition<int> */
class VarintDef extends Definition
{
    public static function read(ReadableStream $input): int
    {
        $value = 0;
        $length = 0;

        while (true) {
            $currentByte = ByteDef::read($input);
            $value |= ($currentByte & 0x7F) << ($length * 7);

            $length += 1;
            if ($length > 5) {
                throw new RuntimeException("VarInt is too big");
            }

            if (($currentByte & 0x80) != 0x80) {
                break;
            }
        }
        // force a sign extension - only needed since PHP always has 64-bit integers
        $value <<= 32;
        $value >>= 32;
        return $value;
    }

    public static function write(WritableStream $output, mixed $data): void
    {
        if(!is_int($data)) throw new \TypeError();
        while (true) {
            if (($data & ~0x7F) == 0) {
                ByteDef::write($output, $data);
                return;
            }

            ByteDef::write($output, ($data & 0x7F) | 0x80);
            // Note: >>> means that the sign bit is shifted with the rest of the number rather than being left alone
            $data >>= 7;
            $data &= 0b00000001111111111111111111111111;
        }
    }
}