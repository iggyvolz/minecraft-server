<?php
require_once __DIR__ . "/vendor/autoload.php";
$stream = new \Amp\ByteStream\WritableBuffer();
\iggyvolz\minecraft\StreamReaderWriter::writeSingle($stream, 2**-149, true);
$stream->close();
$newStream = new \Amp\ByteStream\ReadableBuffer($stream->buffer());
var_dump(\iggyvolz\minecraft\StreamReaderWriter::readSingle($newStream));
