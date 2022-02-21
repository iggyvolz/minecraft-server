<?php

namespace iggyvolz\minecraft\Definitions;

use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;
use iggyvolz\minecraft\Packet\ClientState;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
/** @template-extends Definition<ClientState> */
class ClientStateDef extends Definition
{
    public static function read(ReadableStream $input): ClientState
    {
        return ClientState::from(VarintDef::read($input));
    }

    public static function write(WritableStream $output, mixed $data): void
    {
        if(!$data instanceof ClientState) throw new \TypeError();
        VarintDef::write($output, $data->value);
    }
}