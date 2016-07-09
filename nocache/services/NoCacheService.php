<?php
namespace Craft;

class NoCacheService extends BaseApplicationComponent
{
	public function getCompilePath($id = null)
	{
		$path = craft()->path->getCompiledTemplatesPath() . 'nocache/';
		IOHelper::ensureFolderExists($path);

		if($id)
		{
			$path .= $this->getFileName($id);
		}

		return $path;
	}

	public function getClassName($id)
	{
		return '__NoCacheTemplate_' . $id;
	}

	public function getFileName($id)
	{
		return 'nocache_' . $id . '.php';
	}

	public function isCacheEnabled()
	{
		$request = craft()->request;

		return !$request->isLivePreview() && !$request->getToken() && $request->isSiteRequest();
	}

	public function render($id)
	{
		$environment = craft()->templates->getTwig();
		$className = $this->getClassName($id);

		require_once $this->getCompilePath($id);

		$template = new $className($environment);
		$context = $environment->getGlobals();

		return $template->render($context);
	}

	public function compile($id, $compiler, $node)
	{
		$className = $this->getClassName($id);
		$module = new \Twig_Node_Module(
			new \Twig_Node_Body([$node]),
			null,
			new \Twig_Node(),
			new \Twig_Node(),
			new \Twig_Node(),
			[],
			$compiler->getFilename()
		);

		$environment = craft()->templates->getTwig();
		$nodeCompiler = new \Twig_Compiler($environment);
		$nodeCompiler->compile($module);

		$source = $nodeCompiler->getSource();
		$source = preg_replace('/class __TwigTemplate_[a-zA-Z0-9]+/', "class {$className}", $source);

		$file = IOHelper::createFile($this->getCompilePath($id));
		$file->write($source, false);
	}

	public function clearCompiled($excludeIds = [])
	{

	}
}
