<?php

namespace iggyvolz\minecraft\Packet;

use Amp\ByteStream\ReadableBuffer;
use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;
use iggyvolz\minecraft\Definitions\Definition;
use iggyvolz\minecraft\Definitions\StringDef;
use iggyvolz\minecraft\Definitions\VarintDef;
use iggyvolz\minecraft\MinecraftClient;
use Iggyvolz\SimpleAttributeReflection\AttributeReflection;
use Psr\Log\LoggerInterface;

abstract class Packet implements \Stringable
{
    private const CLIENT_PACKETS = [
        0 => [
            0x00 => HandshakePacket::class,
        ],
        1 => [
            0x00 => StatusRequest::class,
            0x01 => StatusPing::class,
        ]
    ];

    public static function read(ReadableStream $input, ClientState $state): self
    {
        $packetContents = StringDef::read($input);
//        echo bin2hex($packetContents) . PHP_EOL;
        $packet = new ReadableBuffer($packetContents);
        $packetId = VarintDef::read($packet);
        $packetClass = self::CLIENT_PACKETS[$state->value][$packetId] ?? throw new \RuntimeException("Unknown packet 0x" . dechex($packetId) . " in state $state->name");
        $constr = ($cls = new \ReflectionClass($packetClass))->getConstructor();
        $args = [];
        foreach($constr->getParameters() as $parameter) {
            if($definition = AttributeReflection::getAttribute($parameter, Definition::class)) {
//                echo $definition::class . PHP_EOL;
                $args[] = $definition->read($packet);
            } else {
                throw new \LogicException("No definition for parameter " . $parameter->getName() . " in $packetClass::__construct");
            }
        }
        return $cls->newInstance(...$args);
    }

    public function write(WritableStream $output, LoggerInterface $logger): void
    {
        $logger->info("Writing $this\n");
        $constr = (new \ReflectionClass(static::class))->getConstructor();
        foreach($constr->getParameters() as $parameter) {
            if($definition = AttributeReflection::getAttribute($parameter, Definition::class)) {
                $definition->write($output, $this->{$parameter->name});
            } else {
                throw new \LogicException("No definition for parameter " . $parameter->getName() . " in ".static::class."::__construct");
            }
        }
    }
}