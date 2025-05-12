<?php

declare(strict_types=1);

namespace Spryker\IndexProject\DataProvider;

class ServiceDataProvider extends AbstractByMethodDataProvider
{
    public function getDataType(): string
    {
        return 'ServiceInterface';
    }
}
