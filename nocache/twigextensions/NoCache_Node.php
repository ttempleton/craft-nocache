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
		$id = StringHelper::randomString();
		$body = $this->getNode('body');
		$className = '__NoCacheTemplate_' . $id;
		$fileName = 'nocache_' . $id . '.php';

		$module = new \Twig_Node_Module(
			new \Twig_Node_Body([$body]),
			null,
			new \Twig_Node(),
			new \Twig_Node(),
			new \Twig_Node(),
			[],
			$compiler->getFilename()
		);

		$environment = craft()->templates->getTwig();
		$bodyCompiler = new \Twig_Compiler($environment);
		$bodyCompiler->compile($module);
		$source = $bodyCompiler->getSource();

		$source = preg_replace('/class __TwigTemplate_[a-zA-Z0-9]+/', "class {$className}", $source);

		IOHelper::createFile(craft()->path->getCompiledTemplatesPath() . $fileName)
			->write($source, false);

		$compiler
			->addDebugInfo($this)
			->write("echo '<!--nocache-{$id}-->';")
		;
	}
}
