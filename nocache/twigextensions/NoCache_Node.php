<?php
namespace Craft;

require_once 'NoCache_Node_Body.php';

class NoCache_Node extends \Twig_Node
{
	public function __construct(\Twig_Node $body, $line, $tag = null)
	{
		parent::__construct(['body' => $body], [], $line, $tag);
	}

	public function compile(\Twig_Compiler $compiler)
	{
		$compiler->addDebugInfo($this);

		$id = StringHelper::randomString();
		$body = new NoCache_Node_Body($this->getNode('body'), $id, $this->lineno, $this->tag);

		if(craft()->noCache->isCacheEnabled())
		{
			craft()->noCache->compile($id, $compiler, $body);

			$compiler
				->write("\\Craft\\craft()->cache->set('nocache_{$id}', \$context);")
				->write("echo '<!--nocache-{$id}-->';")
			;
		}
		else
		{
			$body->compile($compiler);
		}
	}
}
