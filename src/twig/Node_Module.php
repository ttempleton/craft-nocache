<?php
namespace ttempleton\nocache\twig;

use Twig\Compiler as TwigCompiler;
use Twig\Node\Node as TwigNode;
use Twig\Node\BodyNode as TwigBodyNode;
use Twig\Node\ModuleNode as TwigModuleNode;
use Twig\Source as TwigSource;

/**
 * Class Node_Module
 *
 * @package ttempleton\nocache\twig
 * @author Benjamin Fleming
 * @author Thomas Templeton
 * @since 2.0.0
 * @deprecated since 2.0.5, will be removed in 3.0
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
}
