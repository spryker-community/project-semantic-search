<?php

declare(strict_types=1);

namespace Spryker\IndexProject\DataProvider;

interface DataProviderInterface
{
    public function getDataType(): string;

    public function getData(): array;
}
