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

	public function getIdFromFileName($fileName)
	{
		return substr($fileName, strlen('nocache_'), -strlen('.php'));
	}

	public function isCacheEnabled()
	{
		$request = craft()->request;

		return !$request->isLivePreview() && !$request->getToken();
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

	public function compile($id, \Twig_Compiler $compiler, \Twig_Node $node)
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

	public function clearCompiled($onlyUnused = true)
	{
		$compilePath = $this->getCompilePath();

		if($onlyUnused)
		{
			$usedIds = $this->_getUsedCompileIds();
			$files = IOHelper::getFolderContents($compilePath, false);

			foreach($files as $filePath)
			{
				$fileName = IOHelper::getFileName($filePath);
				$fileId = $this->getIdFromFileName($fileName);

				if(!in_array($fileId, $usedIds))
				{
					IOHelper::deleteFile($filePath);

					craft()->cache->delete('nocache_' . $fileId);
				}
			}
		}
		else
		{
			IOHelper::clearFolder($compilePath);
		}
	}

	private function _getUsedCompileIds()
	{
		$ids = [];
		$caches = craft()->db->createCommand()
			->select('body')
			->from('templatecaches')
			->queryAll();

		foreach($caches as $cache)
		{
			$body = $cache['body'];
			preg_match_all('/<!--nocache-([a-z0-9]+)-->/i', $body, $matches);

			if(isset($matches[1]))
			{
				foreach($matches[1] as $id)
				{
					$ids[] = $id;
				}
			}
		}

		return $ids;
	}
}
