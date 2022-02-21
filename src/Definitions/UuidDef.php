<?php

namespace iggyvolz\minecraft\Definitions;

use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
/** @template-extends Definition<UuidInterface> */
class UuidDef extends Definition
{

    public static function read(ReadableStream $input): UuidInterface
    {
        return Uuid::fromBytes(self::readData($input, 16));
    }

    public static function write(WritableStream $output, mixed $data): void
    {
        if(!$data instanceof UuidInterface) throw new \TypeError();
        $output->write($data->getBytes());
    }
}