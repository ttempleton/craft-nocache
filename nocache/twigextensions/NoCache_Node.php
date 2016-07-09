<?php
namespace Craft;

class NoCache_Node extends \Twig_Node
{
	public function __construct(\Twig_Node $body, $line, $tag = null)
	{
		parent::__construct(['body' => $body], [], $line, $tag);
	}

	public function compile(\Twig_Compiler $compiler)
	{
		$body = $this->getNode('body');

		$compiler->addDebugInfo($this);

		if(craft()->noCache->isCacheEnabled())
		{
			$id = StringHelper::randomString();

			craft()->noCache->compile($id, $compiler, $body);

			$compiler->write("echo '<!--nocache-{$id}-->';");
		}
		else
		{
			$compiler->subcompile($body);
		}
	}
}
