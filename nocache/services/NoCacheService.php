<?php
namespace Craft;

/**
 * Class NoCacheService
 *
 * @package Craft
 */
class NoCacheService extends BaseApplicationComponent
{
	/**
	 * Returns the path of the NoCache compiled templates directory.
	 * If an ID is passed, the directory with the compiled template filename will be returned.
	 *
	 * @param string|null $id - The template ID
	 * @return string
	 */
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

	/**
	 * Returns the class name of the compiled template.
	 *
	 * @param string $id - The template ID
	 * @return string
	 */
	public function getClassName($id)
	{
		return '__NoCacheTemplate_' . $id;
	}

	/**
	 * Returns the file name of the compiled template.
	 *
	 * @param string $id - The template ID
	 * @return string
	 */
	public function getFileName($id)
	{
		return 'nocache_' . $id . '.php';
	}

	/**
	 * Returns the ID of the compiled template from it's filename.
	 *
	 * @param string $fileName - The template filename
	 * @return string
	 */
	public function getIdFromFileName($fileName)
	{
		return substr($fileName, strlen('nocache_'), -strlen('.php'));
	}

	/**
	 * Returns if caching is enabled for the request.
	 *
	 * @return bool
	 */
	public function isCacheEnabled()
	{
		$config = craft()->config;
		$request = craft()->request;

		// See `app/atc/templating/twigextensions/Cache_Node.php` line 47
		return $config->get('enableTemplateCaching') && !$request->isLivePreview() && !$request->getToken();
	}

	/**
	 * Renders a NoCache compiled template.
	 *
	 * @param $id - The template ID
	 * @return string - The rendered output of the template
	 */
	public function render($id)
	{
		$environment = craft()->templates->getTwig();
		$className = $this->getClassName($id);

		require_once $this->getCompilePath($id);

		$template = new $className($environment);
		$context = $environment->getGlobals();

		return $template->render($context);
	}

	/**
	 * Compiles a Twig node independently to it's own compiled template file.
	 * This method is used to save the internals of a `nocache` tag for later use.
	 *
	 * @param $id - The template ID
	 * @param \Twig_Compiler $compiler
	 * @param \Twig_Node $node
	 */
	public function compile($id, \Twig_Compiler $compiler, \Twig_Node $node)
	{
		$className = $this->getClassName($id);

		// Create a module node as it'll compile to a complete compiled template class, as opposed to just compiling the
		// node directly, which will only generate the internals of the render method for that class
		// Also, pass a bunch of empty nodes and objects as they're required parameters, but of no use in this situation
		$module = new \Twig_Node_Module(
			new \Twig_Node_Body([$node]),
			null,
			new \Twig_Node(),
			new \Twig_Node(),
			new \Twig_Node(),
			[],
			// Modules expect a Twig template file to be the source of their compilation. Since this parameter is
			// required to be a valid template file, just pass it the template that the `nocache` block is apart of.
			// This is technically incorrect as the compiler uses this file as a way of mapping errors to line numbers,
			// because the internals of the `nocache` block have been isolated from the template and are being treated
			// as it's own separate template. This means the mapping of errors to line numbers will be off.
			$compiler->getFilename()
		);

		$environment = craft()->templates->getTwig();

		// Compile the module node and get it's source code
		$nodeCompiler = new \Twig_Compiler($environment);
		$nodeCompiler->compile($module);
		$source = $nodeCompiler->getSource();

		// The compiled module node will use the ID of the passed template as it's class name, but it really needs to be
		// marked with the ID of the `nocache` block. Yeah, pretty crude to perform a string replace on the compiled
		// code but get over it already will you? Jeez...
		$source = preg_replace('/class __TwigTemplate_[a-zA-Z0-9]+/', "class {$className}", $source);

		// Finally create the compiled template file
		$file = IOHelper::createFile($this->getCompilePath($id));
		$file->write($source, false);
	}

	/**
	 * Cleans out the NoCache compiled templates directory.
	 * You can specify whether to only empty out templates that are not being used.
	 *
	 * @param bool|true $onlyUnused
	 */
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

					// Make sure to remove the saved context variable from the cache
					craft()->cache->delete('nocache_' . $fileId);
				}
			}
		}
		else
		{
			IOHelper::clearFolder($compilePath);
		}
	}

	/**
	 * Finds all the used NoCache ID's by searching the template cache rows for any `nocache` placeholder tags.
	 *
	 * @return array
	 */
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
