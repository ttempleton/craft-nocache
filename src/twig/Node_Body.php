<?php

namespace ttempleton\nocache\twig;

use Twig\Compiler;
use Twig\Node\Node as TwigNode;

/**
 * Class Node_Body
 * This will serve as the node that'll actually render the contents of a `nocache` block
 *
 * @package ttempleton\nocache\twig
 * @author Benjamin Fleming
 * @author Thomas Templeton
 * @since 2.0.0
 */
class Node_Body extends TwigNode
{
    /**
     * @param TwigNode $body
     * @param int $line
     * @param string|null $tag
     */
    public function __construct(TwigNode $body, int $line, ?string $tag = null)
    {
        parent::__construct(['body' => $body], [], $line, $tag);

        $this->setSourceContext($body->getSourceContext());
    }

    /**
     * @inheritdoc
     */
    public function compile(Compiler $compiler): void
    {
        $compiler->subcompile($this->getNode('body'));
    }
}
