<?php
namespace ttempleton\nocache;

use Twig_Node;
use Twig_Token;
use Twig_TokenParser;

/**
 * Class TokenParser
 *
 * @package ttempleton\nocache
 * @author Benjamin Fleming
 * @author Thomas Templeton
 * @since 2.0.0
 */
class TokenParser extends Twig_TokenParser
{
	public function getTag()
	{
		return 'nocache';
	}

	public function parse(Twig_Token $token)
	{
		$parser = $this->parser;
		$stream = $parser->getStream();

		$context = null;
		if ($stream->nextIf(Twig_Token::NAME_TYPE, 'with'))
		{
			$context = $parser->getExpressionParser()->parseExpression();
		}

		$stream->expect(Twig_Token::BLOCK_END_TYPE);

		$body = $parser->subparse([$this, 'decideEnd']);
		$body->setTemplateName($stream->getSourceContext()->getName());

		$stream->next();
		$stream->expect(Twig_Token::BLOCK_END_TYPE);

		return new Node(
			$body,
			$context ?? new Twig_Node(),
			$token->getLine(),
			$this->getTag()
		);
	}

	public function decideEnd(Twig_Token $token)
	{
		return $token->test(['endnocache']);
	}
}
