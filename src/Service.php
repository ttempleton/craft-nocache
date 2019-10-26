<?php
namespace ttempleton\nocache;

use yii\base\Component;

use Craft;
use craft\helpers\FileHelper;

use Twig\Compiler as TwigCompiler;
use Twig\Node\Node as TwigNode;
use Twig\Node\BodyNode as TwigBodyNode;
use Twig\Node\ModuleNode as TwigModuleNode;
use Twig\Source as TwigSource;

/**
 * Class Service
 *
 * @package ttempleton\nocache
 * @author Benjamin Fleming
 * @author Thomas Templeton
 * @since 2.0.0
 */
class Service extends Component
{
	/**
	 * Returns the path of the NoCache compiled templates directory.
	 * If an ID is passed, the directory with the compiled template filename will be returned.
	 *
	 * @param string|null $id - The template ID
	 * @return string
	 */
	public function getCompilePath(string $id = null): string
	{
		$path = Craft::$app->getPath()->getCompiledTemplatesPath(false) . DIRECTORY_SEPARATOR . 'nocache' . DIRECTORY_SEPARATOR;
		FileHelper::createDirectory($path);

		if ($id)
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
	public function getClassName(string $id): string
	{
		return '__NoCacheTemplate_' . $id;
	}

	/**
	 * Returns the file name of the compiled template.
	 *
	 * @param string $id - The template ID
	 * @return string
	 */
	public function getFileName(string $id): string
	{
		return 'nocache_' . $id . '.php';
	}

	/**
	 * Returns the ID of the compiled template from it's filename.
	 *
	 * @param string $fileName - The template filename
	 * @return string
	 */
	public function getIdFromFileName(string $fileName): string
	{
		return substr($fileName, strlen('nocache_'), -strlen('.php'));
	}

	/**
	 * Returns whether caching is enabled for the request.
	 *
	 * @return bool
	 */
	public function isCacheEnabled(): bool
	{
		$generalConfig = Craft::$app->getConfig()->getGeneral();
		$request = Craft::$app->getRequest();

		// See `craftcms/cms/src/web/twig/nodes/CacheNode.php` line 54
		return $generalConfig->enableTemplateCaching && !$request->getIsLivePreview() && !$request->getIsConsoleRequest() && !$request->getToken();
	}

	/**
	 * Renders a NoCache compiled template.
	 *
	 * @param $templateId
	 * @param string|array $contextId
	 * @return string|null - The rendered output of the template
	 */
	public function render($templateId, $contextId)
	{
		$environment = Craft::$app->getView()->getTwig();
		$className = $this->getClassName($templateId);
		$compiledTemplate = $this->getCompilePath($templateId);

		if (!file_exists($compiledTemplate))
		{
			return null;
		}

		require_once $compiledTemplate;

		$template = new $className($environment);
		$context = $environment->getGlobals();
		$cachedContext = is_string($contextId) ? Craft::$app->getCache()->get("nocache_{$templateId}_{$contextId}") : $contextId;
		$cachedContext = is_array($cachedContext) ? $cachedContext : [];

		// Merge the cached context (if it exists) onto the current context before rendering the body
		// Make sure that the original context takes priority over the cached context, so variables that have been
		// updated are used instead (such as the `now` global variable)
		if (!empty($cachedContext))
		{
			$context = array_merge($context, $cachedContext);
		}

		return $template->render($context);
	}

	/**
	 * Compiles a Twig node independently to it's own compiled template file.
	 * This method is used to save the internals of a `nocache` tag for later use.
	 *
	 * @param string $id - The template ID
	 * @param TwigNode $node
	 */
	public function compile(string $id, TwigNode $node)
	{
		// Create a module node as it'll compile to a complete compiled template class, as opposed to just compiling the
		// node directly, which will only generate the internals of the render method for that class.
		// Modules expect a Twig template file to be the source of their compilation. Since this last parameter is
		// required to be a valid template file, just pass it the template that the `nocache` block is apart of.
		// This is technically incorrect as the compiler uses this file as a way of mapping errors to line numbers,
		// because the internals of the `nocache` block have been isolated from the template and are being treated
		// as it's own separate template. This means the mapping of errors to line numbers will be off.
		$module = new TwigModuleNode(
			new TwigBodyNode([$node]),
			null,
			new TwigNode(),
			new TwigNode(),
			new TwigNode(),
			[],
			new TwigSource('', $node->getSourceContext()->getName())
		);

		$environment = Craft::$app->getView()->getTwig();

		// Compile the module node, get its source code and set the correct class name
		$nodeCompiler = new TwigCompiler($environment);
		$nodeCompiler->compile($module);
		$source = preg_replace(
			'/class (\w+) extends/i',
			'class ' . $this->getClassName($id) . ' extends',
			$nodeCompiler->getSource(),
			1
		);

		// Finally create the compiled template file
		$path = $this->getCompilePath($id);
		FileHelper::writeToFile($path, $source);
	}
}
