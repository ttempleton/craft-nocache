<?php
namespace ttempleton\nocache\twig;

use Twig_Extension;

/**
 * Class Extension
 *
 * @package ttempleton\nocache\twig
 * @author Benjamin Fleming
 * @author Thomas Templeton
 * @since 2.0.0
 */
class Extension extends Twig_Extension
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
