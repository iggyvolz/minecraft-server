<?php

namespace iggyvolz\minecraft\Definitions;

use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
/** @template-extends Definition<string> */
class StringDef extends Definition
{
    public static function read(ReadableStream $input): string
    {
        echo "StringDef::read\n";
        echo "Length: " . ($length = VarintDef::read($input)) . PHP_EOL;
        return self::readData($input, $length);
    }

    public static function write(WritableStream $output, mixed $data): void
    {
        if(!is_string($data)) throw new \TypeError();
        VarintDef::write($output, strlen($data));
        $output->write($data);
    }
}