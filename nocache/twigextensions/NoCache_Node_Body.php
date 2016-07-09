<?php
namespace Craft;

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
		$compiler
			->write("\$context += \\Craft\\craft()->cache->get('nocache_{$this->id}');")
			->subcompile($this->getNode('body'));
	}
}
