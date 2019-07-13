<?php
namespace ttempleton\nocache\twig;

use Twig\Compiler as TwigCompiler;
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
	protected $id;

	public function __construct(TwigNode $body, string $id, int $line, string $tag = null)
	{
		parent::__construct(['body' => $body], [], $line, $tag);

		$this->id = $id;
		$setMethod = method_exists($this, 'setSourceContext') ? 'setSourceContext' : 'setTemplateName';
		$setContent = method_exists($this, 'setSourceContext') ? $body->getSourceContext() : $body->getTemplateName();
		$this->$setMethod($setContent);
	}

	public function compile(TwigCompiler $compiler)
	{
		$compiler->subcompile($this->getNode('body'));
	}
}
