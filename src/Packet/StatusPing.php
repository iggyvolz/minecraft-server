<?php

namespace iggyvolz\minecraft\Packet;

use iggyvolz\minecraft\Definitions\LongDef;

class StatusPing extends Packet
{
    public function __construct(
        #[LongDef]
        public readonly int $payload,
    )
    {
    }

    public function pong(): StatusPong
    {
        return new StatusPong($this->payload);
    }

    public function __toString()
    {
        return "Status Ping with payload $this->payload";
    }

}