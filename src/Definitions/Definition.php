<?php

namespace iggyvolz\minecraft\Definitions;

use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;
use Dont\JustDont;
use WeakMap;

/** @template T */
abstract class Definition
{
    public final function __construct(){}
    /** @return T */
    public abstract static function read(ReadableStream $input): mixed;
    /** @param T $data */
    public abstract static function write(WritableStream $output, mixed $data): void;



    private static ?WeakMap $buffers = null;
    // Fixed-size read
    protected static function readData(ReadableStream $input, string $length): string
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



    protected static function readNumber(ReadableStream $stream, int $bytes, bool $bigEndian, bool $signed): int
    {
        return self::stringToInt(self::readData($stream, $bytes), $bigEndian, $signed);
    }

    protected static function writeNumber(WritableStream $stream, int $number, bool $bigEndian, int $bytes): void
    {
        $stream->write(self::intToString($number, $bigEndian, $bytes));
    }

    protected static function stringToInt(string $string, bool $bigEndian, bool $signed): int
    {
        $result = 0;
        if($signed && (ord($string[$bigEndian ? strlen($string) - 1 : 0]) & 0x80)) {
            $result = -1;
        }
        for($i = $bigEndian ? strlen($string) - 1 : 0; $bigEndian ? ($i >= 0) : ($i < strlen($string)); $bigEndian ? $i-- : $i++)
        {
            $result <<= 8;
            $result |= ord($string[$i]);
        }
        return $result;
    }

    protected static function intToString(int $int, bool $bigEndian, int $numBytes): string
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
}