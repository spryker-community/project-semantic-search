<?php

declare(strict_types=1);

namespace Spryker\IndexProject\DataProvider\Parser;

use PhpParser\Error;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use RuntimeException;

/**
 * ClassMethodParser is responsible for parsing PHP class files and extracting method information.
 */
class ClassMethodParser implements ClassMethodParserInterface
{
    /**
     * @param \PhpParser\Parser $parser PHP-Parser instance for parsing PHP code
     * @param \PhpParser\PrettyPrinter\Standard $prettyPrinter Pretty printer for generating code strings
     * @param \Spryker\IndexProject\DataProvider\Parser\DocCommentParserInterface $docCommentParser Parser for handling doc comments
     */
    public function __construct(
        private readonly Parser $parser,
        private readonly Standard $prettyPrinter,
        private readonly DocCommentParserInterface $docCommentParser,
    ) {
    }

    /**
     * Parse a PHP file content and extract methods details
     *
     * @param string $code PHP code content to parse
     *
     * @return array<array<string, mixed>>|null Associative array with namespace, classname, and code
     * @throws \RuntimeException When a parsing error occurs
     */
    public function parse(string $code): ?array
    {
        try {
            $ast = $this->parseAndResolveNames($code);
            
            return $this->extractInfo($ast);
        } catch (Error $e) {
            throw new RuntimeException("Parse error: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Parse PHP code and resolve names in the AST
     *
     * @param string $code PHP code to parse
     *
     * @return array<\PhpParser\Node> Abstract Syntax Tree with resolved names
     */
    private function parseAndResolveNames(string $code): array
    {
        $ast = $this->parser->parse($code);
        if ($ast === null) {
            return [];
        }

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new NameResolver());
        
        return $traverser->traverse($ast);
    }

    /**
     * Extract class and method information from AST
     *
     * @param array<\PhpParser\Node> $ast Abstract Syntax Tree
     *
     * @return array<array<string, mixed>>|null Associative array with namespace, classname, and code
     */
    private function extractInfo(array $ast): ?array
    {
        $namespaceAndClass = $this->findNamespaceAndClass($ast);
        
        if ($namespaceAndClass === null || $namespaceAndClass['classLike'] === null) {
            return null;
        }
        
        return $this->extractMethodsFromClass(
            $namespaceAndClass['namespace'],
            $namespaceAndClass['classLike']
        );
    }

    /**
     * Find namespace and class information in the AST
     *
     * @param array<\PhpParser\Node> $ast Abstract Syntax Tree
     *
     * @return array<string, mixed>|null Namespace and class information
     */
    private function findNamespaceAndClass(array $ast): ?array
    {
        $namespace = null;
        $classLike = null;

        foreach ($ast as $node) {
            if ($node instanceof Namespace_) {
                $namespace = $node->name->toString();

                foreach ($node->stmts as $stmt) {
                    if ($stmt instanceof ClassLike) {
                        $classLike = $stmt;
                        break 2;
                    }
                }
            } elseif ($node instanceof ClassLike) {
                $classLike = $node;
                break;
            }
        }

        if ($classLike === null) {
            return null;
        }

        return [
            'namespace' => $namespace,
            'classLike' => $classLike,
        ];
    }

    /**
     * Extract methods from a class
     *
     * @param string|null $namespace Namespace of the class
     * @param \PhpParser\Node\Stmt\ClassLike $classLike Class node
     *
     * @return array<array<string, mixed>> Method information
     */
    private function extractMethodsFromClass(?string $namespace, ClassLike $classLike): array
    {
        $instanceName = $classLike->name->toString();
        $methods = [];

        foreach ($classLike->stmts as $stmt) {
            if ($stmt instanceof ClassMethod) {
                $method = $this->extractMethodInfo($stmt);
                $method['namespace'] = $namespace;
                $method['classname'] = $instanceName;
                $method['method_name'] = $instanceName . '::' . $method['name'];
                $method['name'] = $namespace ? '\\' . $namespace . '\\' . $instanceName . '::' . $method['name'] : $instanceName . '::' . $method['name'];
                $method['code'] = $this->generateSearchCode($method);
                $methods[] = $method;
            }
        }

        return $methods;
    }

    /**
     * Extract method information
     *
     * @param \PhpParser\Node\Stmt\ClassMethod $method Method node
     *
     * @return array<string, mixed> Method information
     */
    private function extractMethodInfo(ClassMethod $method): array
    {
        $methodCode = $this->prettyPrinter->prettyPrint([$method]);
        $methodCode = str_replace('public ', '', $methodCode);

        return [
            'name' => $method->name->toString(),
            'code' => $methodCode,
            'line' => $method->getStartLine(),
            'doc_comment' => $method->getDocComment()?->getText(),
        ];
    }

    /**
     * Generate search code in the requested format
     *
     * @param array<string, mixed> $method Method information
     *
     * @return string Generated search code
     */
    private function generateSearchCode(array $method): string
    {
        $code = $method['method_name'];
        $doc = $this->docCommentParser->extractSpecificationBeforeFirstTag($method['doc_comment'] ?? '');
        
        if ($doc) {
            $code .= PHP_EOL . $doc;
        }

        return $code;
    }
}
