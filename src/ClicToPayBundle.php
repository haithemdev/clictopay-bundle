<?php

namespace Hdev\ClicToPayBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ClicToPayBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
