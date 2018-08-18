<?php
namespace benf\nocache;

use Twig_Compiler;
use Twig_Node;

/**
 * Class Node_Body
 * This will serve as the node that'll actually render the contents of a `nocache` block
 * @package benf\nocache
 */
class Node_Body extends Twig_Node
{
	protected $id;

	public function __construct(Twig_Node $body, string $id, int $line, string $tag = null)
	{
		parent::__construct(['body' => $body], [], $line, $tag);

		$this->id = $id;
		$this->setTemplateName($body->getTemplateName());
	}

	public function compile(Twig_Compiler $compiler)
	{
		$compiler->subcompile($this->getNode('body'));
	}
}
