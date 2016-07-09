<?php
namespace Craft;

require_once 'NoCache_Node_Body.php';

class NoCache_Node extends \Twig_Node
{
	private $_body;

	public function __construct(\Twig_Node $body, \Twig_Node_Expression $context = null, $line, $tag = null)
	{
		parent::__construct(['body' => $body, 'context' => $context], [], $line, $tag);

		$this->_body = new NoCache_Node_Body($body, $context, $line, $tag);
	}

	public function compile(\Twig_Compiler $compiler)
	{
		$compiler->addDebugInfo($this);

		if(craft()->noCache->isCacheEnabled())
		{
			$id = StringHelper::randomString();

			craft()->noCache->compile($id, $compiler, $this->_body);

			$compiler->write("echo '<!--nocache-{$id}-->';");
		}
		else
		{
			$this->_body->compile($compiler);
		}
	}
}
