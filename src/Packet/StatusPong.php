<?php

namespace iggyvolz\minecraft\Packet;

use iggyvolz\minecraft\Definitions\LongDef;

class StatusPong extends Packet
{
    public function __construct(
        #[LongDef]
        public readonly int $payload,
    )
    {
    }

    public function __toString()
    {
        return "Status Pong with payload $this->payload";
    }

}