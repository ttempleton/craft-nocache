<?php
namespace ttempleton\nocache\twig;

use Twig_Compiler;
use Twig_Node;

/**
 * Class Node_Body
 * This will serve as the node that'll actually render the contents of a `nocache` block
 *
 * @package ttempleton\nocache\twig
 * @author Benjamin Fleming
 * @author Thomas Templeton
 * @since 2.0.0
 */
class Node_Body extends Twig_Node
{
	protected $id;

	public function __construct(Twig_Node $body, string $id, int $line, string $tag = null)
	{
		parent::__construct(['body' => $body], [], $line, $tag);

		$this->id = $id;
		$setMethod = method_exists($this, 'setSourceContext') ? 'setSourceContext' : 'setTemplateName';
		$setContent = method_exists($this, 'setSourceContext') ? $body->getSourceContext() : $body->getTemplateName();
		$this->$setMethod($setContent);
	}

	public function compile(Twig_Compiler $compiler)
	{
		$compiler->subcompile($this->getNode('body'));
	}
}
