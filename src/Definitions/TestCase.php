<?php

namespace iggyvolz\minecraft\Definitions;

use Amp\ByteStream\ReadableBuffer;
use Amp\ByteStream\WritableBuffer;
use Tester\Assert;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
final class TestCase
{
    public function __construct(
        public readonly string $input,
        public readonly mixed $output,
        public readonly bool $oneWay = false, // Only test input => output
        public readonly bool $approximate = false, // Test approximate equality
    )
    {
    }

    public function test(Definition $definition): void
    {
        $buf = new ReadableBuffer($this->input);
        $readValue = $definition->read($buf);
        // NAN !== NAN
        if(is_nan($this->output) && is_nan($readValue) && $this->approximate) {
            Assert::true(true);
        } elseif($this->approximate) {
            Assert::equal($this->output, $readValue);
        } else {
            Assert::same($this->output, $readValue);
        }
        Assert::false($buf->isReadable());

        if(!$this->oneWay) {
            $buf = new WritableBuffer();
            $definition->write($buf, $this->output);
            $buf->close();
            $readValue = $buf->buffer();

            // NAN !== NAN
            if(is_nan($this->input) && is_nan($readValue) && $this->approximate) {
                Assert::true(true);
            } elseif($this->approximate) {
                Assert::equal($this->input, $readValue);
            } else {
                Assert::same($this->input, $readValue);
            }
        }
    }
}