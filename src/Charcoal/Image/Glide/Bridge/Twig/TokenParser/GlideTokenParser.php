<?php

declare(strict_types=1);

namespace Charcoal\Image\Glide\Bridge\Twig\TokenParser;

use Charcoal\Image\Glide\Bridge\Twig\Node\GlideNode;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * Token parser for the 'glide' tag
 *
 * ```twig
 * {% glide 'image.jpg' { width: 200 } %}
 *     <img src="{{ url }}" width="{{ width }}" height="{{ height }}">
 * {% endglide %}
 * ```
 */
class GlideTokenParser extends AbstractTokenParser
{
    public function parse(Token $token): Node
    {
        $stream = $this->parser->getStream();

        $path    = $stream->expect(Token::NAME_TYPE)->getValue();
        $options = $this->parser->getExpressionParser()->parseExpression();

        $stream->expect(Token::BLOCK_END_TYPE);

        $body = $this->parser->subparse([$this, 'decideGlideEnd'], true);

        $stream->expect(Token::BLOCK_END_TYPE);

        return new GlideNode($path, $options, $body, $token->getLine(), $this->getTag());
    }

    public function decideGlideEnd(Token $token): bool
    {
        return $token->test('endglide');
    }

    public function getTag(): string
    {
        return 'glide';
    }
}
