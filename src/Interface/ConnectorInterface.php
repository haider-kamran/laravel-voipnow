<?php

namespace HyderKamran\VoipNow\Interface;
interface ConnectorInterface
{
    public function connect(array $config);
}