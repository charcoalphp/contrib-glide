<?php

declare(strict_types=1);

namespace Charcoal\Image\Glide\Bridge\Twig\Node;

use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Node;

class GlideNode extends Node
{
    public function __construct(
        AbstractExpression $path,
        ?AbstractExpression $options,
        Node $body,
        int $lineno,
        string $tag
    ) {
        $nodes = [
            'path' => $key,
            'body' => $body,
        ];

        if (null !== $options) {
            $nodes['options'] = $options;
        }

        parent::__construct($nodes, [], $lineno, $tag);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this);

        $parentContextName = $compiler->getVarName();

        $compiler->write(sprintf("\$%s = \$context;\n", $parentContextName));

        $compiler
            ->write('$cached = $this->env->getRuntime(\'Twig\Extra\Cache\CacheRuntime\')->getCache()->get(')
            ->subcompile($this->getNode('key'))
            ->raw(", function (\Symfony\Contracts\Cache\ItemInterface \$item) use (\$context, \$macros) {\n")
            ->indent();

        if ($this->hasNode('options')) {
            $node = $this->getNode('options');
            $varsName = $compiler->getVarName();
            $compiler
                ->write(sprintf('$%s = ', $varsName))
                ->subcompile($node)
                ->raw(";\n")
                ->write(sprintf("if (!twig_test_iterable(\$%s)) {\n", $varsName))
                ->indent()
                ->write("throw new RuntimeError('Variables passed to the \"with\" tag must be a hash.', ")
                ->repr($node->getTemplateLine())
                ->raw(", \$this->getSourceContext());\n")
                ->outdent()
                ->write("}\n")
                ->write(sprintf("\$%s = twig_to_array(\$%s);\n", $varsName, $varsName))
            ;

            if ($this->getAttribute('only')) {
                $compiler->write("\$context = [];\n");
            }

            $compiler->write(sprintf("\$context = \$this->env->mergeGlobals(array_merge(\$context, \$%s));\n", $varsName));
        }

        $compiler
            ->subcompile($this->getNode('body'))
            ->write(sprintf("\$context = \$%s;\n", $parentContextName));
        ;
    }
}
