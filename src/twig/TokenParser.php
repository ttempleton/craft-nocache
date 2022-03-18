<?php

namespace ttempleton\nocache\twig;

use Twig\Node\Node as TwigNode;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * Class TokenParser
 *
 * @package ttempleton\nocache\twig
 * @author Benjamin Fleming
 * @author Thomas Templeton
 * @since 2.0.0
 */
class TokenParser extends AbstractTokenParser
{
    private int $counter = 0;

    /**
     * @inheritdoc
     */
    public function getTag(): string
    {
        return 'nocache';
    }

    /**
     * @inheritdoc
     */
    public function parse(Token $token): Node
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        $context = null;
        if ($stream->nextIf(Token::NAME_TYPE, 'with')) {
            $context = $parser->getExpressionParser()->parseExpression();
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        $body = $parser->subparse([$this, 'decideEnd']);
        $body->setSourceContext($stream->getSourceContext());

        $stream->next();
        $stream->expect(Token::BLOCK_END_TYPE);

        return new Node(
            $body,
            $context ?? new TwigNode(),
            $token->getLine(),
            $this->getTag(),
            $this->counter++
        );
    }

    /**
     * @param Token $token
     * @return bool
     */
    public function decideEnd(Token $token): bool
    {
        return $token->test(['endnocache']);
    }
}
