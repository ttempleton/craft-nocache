<?php
namespace ttempleton\nocache;

use Twig_Extension;

/**
 * Class TwigExtension
 *
 * @package ttempleton\nocache
 * @author Benjamin Fleming
 * @author Thomas Templeton
 * @since 2.0.0
 */
class TwigExtension extends Twig_Extension
{
	public function getName()
	{
		return 'nocache';
	}

	/**
	 * @return array
	 */
	public function getTokenParsers()
	{
		return [
			new TokenParser(),
		];
	}
}
