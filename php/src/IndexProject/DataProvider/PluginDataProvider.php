<?php

declare(strict_types=1);

namespace Spryker\IndexProject\DataProvider;

class PluginDataProvider extends AbstractByMethodDataProvider
{
    public function getDataType(): string
    {
        return 'Plugin';
    }
}
