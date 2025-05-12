<?php

declare(strict_types=1);

namespace Spryker\IndexProject\DataProvider;

class ClientDataProvider extends AbstractByMethodDataProvider
{
    public function getDataType(): string
    {
        return 'ClientInterface';
    }
}
