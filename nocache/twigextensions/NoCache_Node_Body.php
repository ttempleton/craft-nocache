<?php
namespace Craft;

/**
 * Class NoCache_Node_Body
 * This will serve as the node that'll actually render the contents of a `nocache` block
 *
 * @package Craft
 */
class NoCache_Node_Body extends \Twig_Node
{
	protected $id;

	public function __construct(\Twig_Node $body, $id, $line, $tag = null)
	{
		parent::__construct(['body' => $body], [], $line, $tag);

		$this->id = $id;
	}

	public function compile(\Twig_Compiler $compiler)
	{
		$compiler->subcompile($this->getNode('body'));
	}
}
