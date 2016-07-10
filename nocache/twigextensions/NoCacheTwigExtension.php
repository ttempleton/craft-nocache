<?php
namespace Craft;

require_once 'NoCache_TokenParser.php';

/**
 * Class NoCacheTwigExtension
 *
 * @package Craft
 */
class NoCacheTwigExtension extends \Twig_Extension
{
	public function getName()
	{
		return 'nocache';
	}

	public function getTokenParsers()
	{
		return [
			new NoCache_TokenParser(),
		];
	}
}
