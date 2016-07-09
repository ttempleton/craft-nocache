<?php
namespace Craft;

class NoCache_Node extends \Twig_Node
{
	public function compile(\Twig_Compiler $compiler)
	{
		$compiler
			->addDebugInfo($this)
			->write('$craft = \Craft\craft();')
		;
	}
}
