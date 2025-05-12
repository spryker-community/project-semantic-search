<?php

declare(strict_types=1);

namespace Spryker\IndexProject\DataProvider\Parser;

/**
 * Interface for parsing PHP class files to extract method information.
 */
interface ClassMethodParserInterface
{
    /**
     * Parse PHP code and extract methods details
     *
     * @param string $code PHP code content to parse
     *
     * @return array<array<string, mixed>>|null Associative array with namespace, classname, and code
     * @throws \RuntimeException When a parsing error occurs
     */
    public function parse(string $code): ?array;
}
