<?php

declare(strict_types=1);

namespace Spryker;

trait ConfigResolverTrait
{
    public function getConfig(): Config
    {
        return Config::init();
    }
}
