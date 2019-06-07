<?php
namespace ttempleton\nocache\twig;

use Twig_Compiler;
use Twig_Node;
use Twig_Node_Body;
use Twig_Node_Module;
use Twig_Source;

use ttempleton\nocache\Plugin as NoCache;

/**
 * Class Node_Module
 *
 * @package ttempleton\nocache\twig
 * @author Benjamin Fleming
 * @author Thomas Templeton
 * @since 2.0.0
 */
class Node_Module extends Twig_Node_Module
{
	protected $id;

	public function __construct(Twig_Node $node, string $id, string $fileName)
	{
		// Pass in some empty objects to satisfy the required parameters
		parent::__construct(
			new Twig_Node_Body([$node]),
			null,
			new Twig_Node(),
			new Twig_Node(),
			new Twig_Node(),
			[],
			new Twig_Source('', $fileName)
		);

		$this->id = $id;
	}

	/**
	 * Override the class header so the class name can be changed to reference the NoCache block instead of the
	 * template.
	 *
	 * @param Twig_Compiler $compiler
	 */
	protected function compileClassHeader(Twig_Compiler $compiler)
	{
		$className = NoCache::$plugin->methods->getClassName($this->id);

		// Craft 3.1.18 onward requires Twig ^2.7.2 which needs these use statements
		// Checking whether `craft\errors\DeprecationException` exists as that was also added in 3.1.18
		// (doesn't seem necessary to check Twig as Twig 2.7 breaks prior versions of Craft)
		if (class_exists('craft\errors\DeprecationException'))
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
		if (\Twig_Environment::VERSION_ID >= 21100)
		{
			$compiler
				->write("private \$source;\n")
				->write("private \$macros = [];\n\n");
		}
	}
}
