<?php

namespace iggyvolz\minecraft\Packet;

use Amp\ByteStream\ReadableBuffer;
use Amp\ByteStream\ReadableStream;
use iggyvolz\minecraft\Definitions\Definition;
use iggyvolz\minecraft\Definitions\StringDef;
use iggyvolz\minecraft\Definitions\VarintDef;
use Iggyvolz\SimpleAttributeReflection\AttributeReflection;

abstract class Packet implements \Stringable
{
    private const CLIENT_PACKETS = [
        0 => [
            0x00 => HandshakePacket::class,
        ],
        1 => [
            0x00 => StatusRequest::class,
            0x01 => StatusRequest::class,
        ]
    ];

    public static function read(ReadableStream $input, ClientState &$state): self
    {
        $packetContents = StringDef::read($input);
        echo bin2hex($packetContents) . PHP_EOL;
        $packet = new ReadableBuffer($packetContents);
        $packetId = VarintDef::read($packet);
        $packetClass = self::CLIENT_PACKETS[$state->value][$packetId] ?? throw new \RuntimeException("Unknown packet 0x" . dechex($packetId) . " in state $state->name");
        $constr = ($cls = new \ReflectionClass($packetClass))->getConstructor();
        $args = [];
        var_dump($args);
        foreach($constr->getParameters() as $parameter) {
            if($definition = AttributeReflection::getAttribute($parameter, Definition::class)) {
                echo $definition::class . PHP_EOL;
                $args[] = $definition->read($packet);
            } else {
                throw new \LogicException("No definition for parameter " . $parameter->getName() . " in $packetClass::__construct");
            }
        }
        /** @var Packet $packet */
        $packet = $cls->newInstance(...$args);
        if($packet instanceof ChangesClientState) {
            $state = $packet->newClientState();
        }
        return $packet;
    }
}