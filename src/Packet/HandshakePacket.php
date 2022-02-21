<?php

namespace iggyvolz\minecraft\Packet;

use iggyvolz\minecraft\Definitions\ClientStateDef;
use iggyvolz\minecraft\Definitions\StringDef;
use iggyvolz\minecraft\Definitions\UShortDef;
use iggyvolz\minecraft\Definitions\VarintDef;

class HandshakePacket extends Packet implements ChangesClientState
{
    public function __construct(
        #[VarintDef]
        public readonly int $protocolVersion,
        #[StringDef]
        public readonly string $serverAddress,
        #[UShortDef]
        public readonly int $serverPort,
        #[ClientStateDef]
        public readonly ClientState $nextState,
    )
    {

    }

    public function __toString()
    {
        return "Handshake: protocol version $this->protocolVersion, server address: $this->serverAddress, server port: $this->serverPort";
    }

    public function newClientState(): ClientState
    {
        return $this->nextState;
    }
}