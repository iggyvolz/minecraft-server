<?php

namespace iggyvolz\minecraft\Packet;

class StatusRequest extends Packet
{
    public function __construct()
    {
    }

    public function __toString()
    {
        return "Status Request";
    }
}