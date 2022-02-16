<?php

namespace iggyvolz\minecraft\Definitions;

use Amp\ByteStream\ReadableStream;
use Amp\ByteStream\WritableStream;

abstract class AFloatDef extends Definition
{
    public abstract static function read(ReadableStream $input): float;

    protected static function readFloat(
        ReadableStream $input,
        // NO IMPLICIT BIT
        int $significandBits,
        int $exponentBits,
        bool $bigEndian
    ): float
    {
        if((1 + $significandBits + $exponentBits) % 8 !== 0) throw new \InvalidArgumentException("Must use a multiple of 8 bits!");
        $bytes = (1 + $significandBits + $exponentBits) / 8;
        $int = self::readNumber($input, $bytes, $bigEndian, true);
        $signBit = 1 & ($int >> ($significandBits + $exponentBits));
//        echo "Sign bit: " . decbin($signBit) . PHP_EOL;
        $exponent = ((1 << $exponentBits) - 1) & ($int >> ($significandBits));
//        echo "Exponent: " . decbin($exponent) . PHP_EOL;
        $significand = ((1 << $significandBits) - 1) & $int;
//        echo "Significand: " . decbin($significand) . PHP_EOL;
        $decodedSignificand = 0;
        for($i = 0; $i<$significandBits + 1; $i++) {
            if($significand & (1 << $significandBits)) {
                $decodedSignificand += 2**(-$i);
            }
            $significand <<= 1;
        }
//        echo "Decoded Significand: $decodedSignificand\n";
        $bias = (2 ** ($exponentBits - 1)) - 1;
        if($exponent === 0) {
            if($decodedSignificand === 0) {
                return 0;
            } else {
                return ((-1) ** $signBit) * (2**-($bias-1)) * $decodedSignificand;
            }
        } elseif($exponent === ((1 << $exponentBits) - 1)) {
            if($decodedSignificand === 0) {
                return ((-1) ** $signBit) * INF;
            } else {
                return NAN;
            }
        } else {
            return ((-1) ** $signBit) * 2**($exponent - $bias) * (1 + $decodedSignificand);
        }
    }

    protected static function writeFloat(WritableStream $output, int $significandBits, int $exponentBits, float $value, bool $bigEndian): void
    {
        if((1 + $significandBits + $exponentBits) % 8 !== 0) throw new \InvalidArgumentException("Must use a multiple of 8 bits!");
        $bytes = (1 + $significandBits + $exponentBits) / 8;
        if($value === 0.0) {

            self::writeNumber($output, 0, $bigEndian, $bytes);
            return;
        } elseif($value === INF) {
            // Set everything to 1 except sign bit and significand
            self::writeNumber($output, (-1 << $significandBits) & (~(1 << $significandBits + $exponentBits)), $bigEndian, $bytes);
            return;
        } elseif($value === -INF) {
            // Set everything to 1 except significand
            self::writeNumber($output, -1 << $significandBits, $bigEndian, $bytes);
            return;
        } elseif($value === NAN) {
            // Set everything to 1 except all but last bit of significand
            self::writeNumber($output, ((-1 << $significandBits) & (~(1 << $significandBits + $exponentBits))) | 1, $bigEndian, $bytes);
            return;
        } elseif($value < 0) {
            $negative = true;
            $value = -$value;
        } else {
            $negative = false;
        }
        $exponent = -1;

        // TODO this can be optimized by doing logs rather than a loop
        while($value >= 1) {
            $value /= 2;
            $exponent++;
        }
        while($value < 0.5) {
            $value *= 2;
            $exponent--;
        }
        $fractionalPartBinary = 0;
        for($i=0; $i<$significandBits + 1;$i++) {
            $value *= 2;
            $fractionalPartBinary <<= 1;
            if($value >= 1) {
                $fractionalPartBinary |= 1;
                $value -= 1;
            }
        }
//        echo "Fractional part: " . decbin($fractionalPartBinary) . PHP_EOL;
//        echo "Exponent:  " . $exponent . PHP_EOL;
        $bias = (2 ** ($exponentBits - 1)) - 1;
        $biasedExponent = $exponent + $bias;
        if($biasedExponent <= 0) {
//            echo "Biased exponent:  " . $biasedExponent . PHP_EOL;
            // Subnormal number
            $fractionalPartBinary >>= ((-$biasedExponent) + 1);
            $finalNumber = (($negative ? 1 : 0) << $significandBits + $exponentBits) | $fractionalPartBinary & ((1 << $significandBits) - 1);
//            echo "Final result: " . str_pad(decbin($finalNumber), 32, '0', STR_PAD_LEFT) . PHP_EOL;
            self::writeNumber($output, $finalNumber, $bigEndian, $bytes);
        } else {
//            echo "Biased exponent:  " . decbin($biasedExponent) . PHP_EOL;
            $finalNumber = (($negative ? 1 : 0) << $significandBits + $exponentBits) | ($biasedExponent << $significandBits) | $fractionalPartBinary & ((1 << $significandBits) - 1);
//            echo "Final result: " . str_pad(decbin($finalNumber), 32, '0', STR_PAD_LEFT) . PHP_EOL;
            self::writeNumber($output, $finalNumber, $bigEndian, $bytes);
        }
    }
}