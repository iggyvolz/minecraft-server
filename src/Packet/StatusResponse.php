<?php

namespace iggyvolz\minecraft\Packet;

use iggyvolz\minecraft\Definitions\StringDef;

class StatusResponse extends Packet
{
    public function __construct(
        #[StringDef]
        public readonly string $response,
    )
    {

    }
    public static function create(string $versionName, int $versionProtocol, int $playersMax, int $playersOnline, string $description): self {
        return new self(json_encode([
            "version" => [
                "name" => $versionName,
                "protocol" => $versionProtocol,
            ],
            "players" => [
                "max" => $playersMax,
                "online" => $playersOnline,
            ],
            "description" => [
                "text" => $description
            ],
        ], JSON_THROW_ON_ERROR));
    }

    public function __toString()
    {
        return "StatusResponse " . $this->response;
    }
}