<?php
namespace Craft;

require_once 'NoCache_Node.php';

/**
 * Class NoCache_TokenParser
 *
 * @package Craft
 */
class NoCache_TokenParser extends \Twig_TokenParser
{
	public function getTag()
	{
		return 'nocache';
	}

	public function parse(\Twig_Token $token)
	{
		$parser = $this->parser;
		$stream = $parser->getStream();

		$context = null;
		if($stream->nextIf(\Twig_Token::NAME_TYPE, 'with'))
		{
			$context = $this->parser->getExpressionParser()->parseExpression();
		}

		$stream->expect(\Twig_Token::BLOCK_END_TYPE);

		$body = $parser->subparse([$this, 'decideEnd']);

		$stream->next();
		$stream->expect(\Twig_Token::BLOCK_END_TYPE);

		return new NoCache_Node(
			$body,
			$context,
			$token->getLine(),
			$this->getTag()
		);
	}

	public function decideEnd(\Twig_Token $token)
	{
		return $token->test(['endnocache']);
	}
}
