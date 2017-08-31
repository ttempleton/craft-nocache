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
	 * @param $templateId
	 * @param string|array $contextId
	 * @return string - The rendered output of the template
	 */
	public function render($templateId, $contextId)
	{
		$environment = craft()->templates->getTwig();
		$className = $this->getClassName($templateId);
		$compiledTemplate = $this->getCompilePath($templateId);

		if(!file_exists($compiledTemplate))
		{
			return false;
		}

		require_once $compiledTemplate;

		$template = new $className($environment);
		$context = $environment->getGlobals();
		$cachedContext = is_string($contextId) ? craft()->cache->get("nocache_{$templateId}_{$contextId}") : $contextId;
		$cachedContext = is_array($cachedContext) ? $cachedContext : [];

		// Merge the cached context (if it exists) onto the current context before rendering the body
		// Make sure that the original context takes priority over the cached context, so variables that have been
		// updated are used instead (such as the `now` global variable)
		if(!empty($cachedContext))
		{
			$context = array_merge($context, $cachedContext);
		}

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
		require_once __DIR__ . '/../twigextensions/NoCache_Node_Module.php';

		// Create a module node as it'll compile to a complete compiled template class, as opposed to just compiling the
		// node directly, which will only generate the internals of the render method for that class.
		// Modules expect a Twig template file to be the source of their compilation. Since this last parameter is
		// required to be a valid template file, just pass it the template that the `nocache` block is apart of.
		// This is technically incorrect as the compiler uses this file as a way of mapping errors to line numbers,
		// because the internals of the `nocache` block have been isolated from the template and are being treated
		// as it's own separate template. This means the mapping of errors to line numbers will be off.
		$module = new NoCache_Node_Module(new \Twig_Node_Body([$node]), $id, $compiler->getFilename());

		$environment = craft()->templates->getTwig();

		// Compile the module node and get it's source code
		$nodeCompiler = new \Twig_Compiler($environment);
		$nodeCompiler->compile($module);
		$source = $nodeCompiler->getSource();

		// Finally create the compiled template file
		$file = IOHelper::createFile($this->getCompilePath($id));
		$file->write($source, false);
	}
}
