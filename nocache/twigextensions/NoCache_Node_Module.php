<?php
namespace Craft;

class NoCache_Node_Module extends \Twig_Node_Module
{
	protected $id;

	public function __construct(\Twig_Node $node, $id, $fileName)
	{
		// Pass in some empty objects to satisfy the required parameters
		parent::__construct(
			new \Twig_Node_Body([$node]),
			null,
			new \Twig_Node(),
			new \Twig_Node(),
			new \Twig_Node(),
			[],
			$fileName
		);

		$this->id = $id;
	}

	/**
	 * Override the class header so the class name can be changed to reference the NoCache block instead of the
	 * template.
	 *
	 * @param \Twig_Compiler $compiler
	 */
	protected function compileClassHeader(\Twig_Compiler $compiler)
	{
		$className = craft()->noCache->getClassName($this->id);

		// Craft 2.7.7 upgraded to Twig 1.38 which needs these use statements
		if (\Twig_Environment::VERSION_ID >= 13800)
		{
			$compiler
				->write("\n\n")
				->write("use Twig\Environment;\n")
				->write("use Twig\Error\LoaderError;\n")
				->write("use Twig\Error\RuntimeError;\n")
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
			->write('/* '.str_replace('*/', '* /', $this->getAttribute('filename'))." */\n")
			->write('class ' . $className)->raw(sprintf(" extends %s\n", $compiler->getEnvironment()->getBaseTemplateClass()))
			->write("{\n")
				->indent();
	}
}
