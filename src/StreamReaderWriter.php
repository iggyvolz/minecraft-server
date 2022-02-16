<?php

namespace iggyvolz\minecraft;

use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;
use Dont\JustDont;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use RuntimeException;
use WeakMap;

// https://wiki.vg/Protocol#VarInt_and_VarLong
final class StreamReaderWriter
{
    use JustDont;


    private static ?WeakMap $buffers = null;
    // Fixed-size read
    public static function read(ReadableStream $input, string $length): string
    {
        self::$buffers ??= new WeakMap();
        self::$buffers[$input] ??= "";
        while(strlen(self::$buffers[$input]) < $length) {
            self::$buffers[$input] .= $input->read() ?? throw new \RuntimeException("FixedSizeStream closed");
        }
        $data = substr(self::$buffers[$input], 0, $length);
        self::$buffers[$input] = substr(self::$buffers[$input], $length);
        return $data;
    }



    private static function stringToInt(string $string, bool $bigEndian, bool $signed): int
    {
        $result = 0;
        if($signed && ord($string[$bigEndian ? strlen($string) - 1 : 0]) & 0x7f) {
            $result = -1;
        }
        for($i = $bigEndian ? strlen($string) - 1 : 0; $bigEndian ? ($i >= 0) : ($i < strlen($string)); $bigEndian ? $i-- : $i++)
        {
            $result <<= 8;
            $result |= ord($string[$i]);
        }
        return $result;
    }

    private static function intToString(int $int, bool $bigEndian, int $numBytes): string
    {
        $result = "";
        for($i = 0; $i < $numBytes; $i++) {
            $byte = chr($int);
            if($bigEndian) {
                $result .= $byte;
            } else {
                $result = "$byte$result";
            }
            $int >>= 8;
        }
        return $result;
    }



    private static function readNumber(ReadableStream $stream, int $bytes, bool $bigEndian, bool $signed): int
    {
        return self::stringToInt(self::read($stream, $bytes), $bigEndian, $signed);
    }

    private static function writeNumber(WritableStream $stream, int $number, bool $bigEndian, int $bytes): void
    {
        $stream->write(self::intToString($number, $bigEndian, $bytes));
    }


    public static function readBoolean(ReadableStream $input, bool $bigEndian = true): bool
    {
        return self::readByte($input, $bigEndian) === 1;
    }

    public function writeBoolean(WritableStream $output, bool $value, bool $bigEndian = true): void
    {
        self::writeByte($output, $value ? 1 : 0, $bigEndian);
    }

    public static function readByte(ReadableStream $input, bool $bigEndian = true): int
    {
        return self::readNumber($input, 1, $bigEndian, true);
    }

    public static function writeByte(WritableStream $stream, int $value, bool $bigEndian = true): void
    {
        self::writeNumber($stream, $value, $bigEndian, 1);
    }

    public static function readUByte(ReadableStream $input, bool $bigEndian = true): int
    {
        return self::readNumber($input, 1, $bigEndian, false);
    }

    public static function writeUByte(WritableStream $stream, int $value, bool $bigEndian = true): void
    {
        self::writeNumber($stream, $value, $bigEndian, 1);
    }

    public static function readShort(ReadableStream $input, bool $bigEndian = true): int
    {
        return self::readNumber($input, 2, $bigEndian, true);
    }

    public static function writeShort(WritableStream $stream, int $value, bool $bigEndian = true): void
    {
        self::writeNumber($stream, $value, $bigEndian, 2);
    }

    public static function readUShort(ReadableStream $input, bool $bigEndian = true): int
    {
        return self::readNumber($input, 2, $bigEndian, false);
    }

    public static function writeUShort(WritableStream $stream, int $value, bool $bigEndian = true): void
    {
        self::writeNumber($stream, $value, $bigEndian, 2);
    }

    public static function readInt(ReadableStream $input, bool $bigEndian = true): int
    {
        return self::readNumber($input, 4, $bigEndian, true);
    }

    public static function writeInt(WritableStream $stream, int $value, bool $bigEndian = true): void
    {
        self::writeNumber($stream, $value, $bigEndian, 4);
    }

    public static function readLong(ReadableStream $input, bool $bigEndian = true): int
    {
        return self::readNumber($input, 8, $bigEndian, true);
    }

    public static function writeLong(WritableStream $stream, int $value, bool $bigEndian = true): void
    {
        self::writeNumber($stream, $value, $bigEndian, 8);
    }


    // https://wiki.vg/Protocol#VarInt_and_VarLong
    public static function readVarint(ReadableStream $input, bool $bigEndian = true): int
    {
        $value = 0;
        $length = 0;

        while (true) {
            $currentByte = self::readByte($input, $bigEndian);
            $value |= ($currentByte & 0x7F) << ($length * 7);

            $length += 1;
            if ($length > 5) {
                throw new RuntimeException("VarInt is too big");
            }

            if (($currentByte & 0x80) != 0x80) {
                break;
            }
        }
        // force a sign extension - only needed since PHP always has 64-bit integers
        $value <<= 32;
        $value >>= 32;
        return $value;
    }
    public static function readVarlong(ReadableStream $input): int {
        $value = 0;
        $length = 0;

        while (true) {
            $currentByte = self::readByte($input);
            $value |= ($currentByte & 0x7F) << ($length * 7);

            $length += 1;
            if ($length > 10) {
                throw new RuntimeException("VarLong is too big");
            }

            if (($currentByte & 0x80) != 0x80) {
                break;
            }
        }
        return $value;
    }
    public static function writeVarInt(WritableStream $output, int $value): void {
        while (true) {
            if (($value & ~0x7F) == 0) {
                self::writeByte($output, $value);
                return;
            }

            self::writeByte($output, ($value & 0x7F) | 0x80);
            // Note: >>> means that the sign bit is shifted with the rest of the number rather than being left alone
            $value = self::unsignedRightShift7($value);
        }
    }
    public static function writeVarLong(WritableStream $output, int $value): void {
        while (true) {
            if (($value & ~0x7F) == 0) {
                self::writeByte($output, $value);
                return;
            }

            self::writeByte($output, ($value & 0x7F) | 0x80);
            // Note: >>> means that the sign bit is shifted with the rest of the number rather than being left alone
            $value = self::unsignedRightShift7($value);
        }
    }



    public static function readFloat(ReadableStream $input, bool $bigEndian = true): float
    {
        $int = self::readInt($input, $bigEndian);
        $signBit = 1 & ($int >> 31);
//        echo "Sign bit: " . decbin($signBit) . PHP_EOL;
        $exponent = 0xff & ($int >> 23);
//        echo "Exponent: " . decbin($exponent) . PHP_EOL;
        $significand = 0x7fffff & $int;
//        echo "Significand: " . decbin($significand) . PHP_EOL;
        $decodedSignificand = 0;
        for($i = 0; $i<24; $i++) {
            if($significand & (1 << 23)) {
                $decodedSignificand += 2**(-$i);
            }
            $significand <<= 1;
        }
//        echo "Decoded Significand: $decodedSignificand\n";
        if($exponent === 0) {
            if($decodedSignificand === 0) {
                return 0;
            } else {
                return ((-1) ** $signBit) * (2**-126) * $decodedSignificand;
            }
        } elseif($exponent === 0xff) {
            if($decodedSignificand === 0) {
                return ((-1) ** $signBit) * INF;
            } else {
                return NAN;
            }
        } else {
            return ((-1) ** $signBit) * 2**($exponent - 127) * (1 + $decodedSignificand);
        }
    }

    public function writeFloat(float $value): void
    {
        $this->socket->write(pack("G", $value));
    }

    public function readDouble(): float
    {
        return unpack("E", $this->socket->read(4));
    }

    public function writeDouble(float $value): void
    {
        $this->socket->write(pack("E", $value));
    }
    public function readPosition(): Position
    {
        $number = $this->readLong();
        return new Position(
            ($number >> 38) & ((1 << 26) - 1),
            $number & 0xFFF,
            ($number >> 12) & 0x3FFFFFF
        );
    }
    public function writePosition(Position $position): void
    {
        $this->writeLong(
            (($position->x & 0x3FFFFFF) << 38) | (($position->z & 0x3FFFFFF) << 12) | ($position->y & 0xFFF)
        );
    }
    public function readUuid(): UuidInterface
    {
        return Uuid::fromBytes($this->socket->read(16));
    }

    public function writeUuid(UuidInterface $uuid): void
    {
        $this->socket->write($uuid->getBytes());
    }

    public function readString(): string
    {
        return $this->socket->read($this->readVarint());
    }

    public function writeString(string $string): void
    {
        $this->socket->write(strlen($string));
        $this->socket->write($string);
    }




    // dear god php.... please... logical right shift
    // https://stackoverflow.com/questions/41134337/unsigned-right-shift-zero-fill-right-shift-in-php-java-javascript-equiv/43359819#43359819
    private static function unsignedRightShift7(int $value): int {
        if ($value < 0)
        {
            $value >>= 1;
            $value &= 0x7fffffff;
            $value |= 0x40000000;
            return $value >> 6;
        } else {
            return $value >> 7;
        }
    }
}