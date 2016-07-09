<?php
namespace Craft;

require 'NoCache_TokenParser.php';

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
