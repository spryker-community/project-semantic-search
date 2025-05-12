<?php

declare(strict_types=1);

namespace Spryker\IndexProject\DataProvider;

class PluginInterfaceDataProvider extends AbstractByMethodDataProvider
{
    public function getDataType(): string
    {
        return 'PluginInterface';
    }
}
