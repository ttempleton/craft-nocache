<?php
namespace Craft;

class NoCache_Node_Body extends \Twig_Node
{
	public function __construct(\Twig_Node $body, \Twig_Node_Expression $context = null, $line, $tag = null)
	{
		parent::__construct(['body' => $body, 'context' => $context], [], $line, $tag);
	}

	public function compile(\Twig_Compiler $compiler)
	{
		$compiler
			->write('$context += ')->subcompile($this->getNode('context'))->write(';')
			->subcompile($this->getNode('body'));
	}
}
