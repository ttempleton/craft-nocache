<?php
namespace ttempleton\nocache\twig;

use Twig\Extension\AbstractExtension;

/**
 * Class Extension
 *
 * @package ttempleton\nocache\twig
 * @author Benjamin Fleming
 * @author Thomas Templeton
 * @since 2.0.0
 */
class Extension extends AbstractExtension
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
