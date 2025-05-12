<?php

declare(strict_types=1);

namespace Spryker\IndexProject\DataProvider;

class ModuleConfigDataProvider extends AbstractByMethodDataProvider
{
    public function getDataType(): string
    {
        return 'Config';
    }
}
