<?php

namespace iggyvolz\minecraft\Packet;

interface ChangesClientState
{
    public function newClientState(): ClientState;
}