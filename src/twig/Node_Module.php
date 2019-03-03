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

		$compiler
			->write("\n\n")
			// If the filename contains */, add a blank to avoid a PHP parse error
			->write('/* '.str_replace('*/', '* /', $this->getTemplateName())." */\n")
			->write('class ' . $className)->raw(sprintf(" extends %s\n", $compiler->getEnvironment()->getBaseTemplateClass()))
			->write("{\n")
				->indent();
	}
}
