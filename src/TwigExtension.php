<?php
namespace benf\nocache;

use Twig_Extension;

use benf\nocache\TokenParser;

/**
 * Class TwigExtension
 * @package benf\nocache
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
