<?php
namespace ttempleton\nocache\twig;

use Twig\Compiler as TwigCompiler;
use Twig\Node\Node as TwigNode;
use Twig\Node\BodyNode as TwigBodyNode;
use Twig\Node\ModuleNode as TwigModuleNode;
use Twig\Source as TwigSource;
use Twig\Environment as TwigEnvironment;

use ttempleton\nocache\Plugin as NoCache;

/**
 * Class Node_Module
 *
 * @package ttempleton\nocache\twig
 * @author Benjamin Fleming
 * @author Thomas Templeton
 * @since 2.0.0
 */
class Node_Module extends TwigModuleNode
{
	protected $id;

	public function __construct(TwigNode $node, string $id, string $fileName)
	{
		// Pass in some empty objects to satisfy the required parameters
		parent::__construct(
			new TwigBodyNode([$node]),
			null,
			new TwigNode(),
			new TwigNode(),
			new TwigNode(),
			[],
			new TwigSource('', $fileName)
		);

		$this->id = $id;
	}

	/**
	 * Override the class header so the class name can be changed to reference the NoCache block instead of the
	 * template.
	 *
	 * @param TwigCompiler $compiler
	 */
	protected function compileClassHeader(TwigCompiler $compiler)
	{
		$className = NoCache::$plugin->methods->getClassName($this->id);

		// Craft 3.1.18 upgraded to Twig 2.7 which needs these
		if (TwigEnvironment::VERSION_ID >= 20700)
		{
			$compiler
				->write("\n\n")
				->write("use Twig\Environment;\n")
				->write("use Twig\Error\LoaderError;\n")
				->write("use Twig\Error\RuntimeError;\n")
				->write("use Twig\Extension\SandboxExtension;\n")
				->write("use Twig\Markup;\n")
				->write("use Twig\Sandbox\SecurityError;\n")
				->write("use Twig\Sandbox\SecurityNotAllowedTagError;\n")
				->write("use Twig\Sandbox\SecurityNotAllowedFilterError;\n")
				->write("use Twig\Sandbox\SecurityNotAllowedFunctionError;\n")
				->write("use Twig\Source;\n")
				->write("use Twig\Template;");
		}

		$compiler
			->write("\n\n")
			// If the filename contains */, add a blank to avoid a PHP parse error
			->write('/* '.str_replace('*/', '* /', $this->getTemplateName())." */\n")
			->write('class ' . $className)->raw(sprintf(" extends %s\n", $compiler->getEnvironment()->getBaseTemplateClass()))
			->write("{\n")
				->indent();

		// Craft 3.1.29 upgraded to Twig 2.11 which needs these
		if (TwigEnvironment::VERSION_ID >= 21100)
		{
			$compiler
				->write("private \$source;\n")
				->write("private \$macros = [];\n\n");
		}
	}
}
