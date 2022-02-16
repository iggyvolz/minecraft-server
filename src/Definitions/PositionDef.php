<?php

namespace iggyvolz\minecraft\Definitions;

use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;
use iggyvolz\minecraft\Position;

/** @template-extends Definition<Position> */
class PositionDef extends Definition
{
    public static function read(ReadableStream $input): Position
    {
        $number = LongDef::read($input);
        return new Position(
            ($number >> 38) & ((1 << 26) - 1),
            $number & 0xFFF,
            ($number >> 12) & 0x3FFFFFF
        );
    }

    public static function write(WritableStream $output, mixed $data): void
    {
        if(!$data instanceof Position) throw new \TypeError();
        LongDef::write($output,
            (($data->x & 0x3FFFFFF) << 38) | (($data->z & 0x3FFFFFF) << 12) | ($data->y & 0xFFF));
    }
}