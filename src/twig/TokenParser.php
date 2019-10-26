<?php
namespace ttempleton\nocache\twig;

use Twig\Node\Node as TwigNode;
use Twig\Token as TwigToken;
use Twig\TokenParser\AbstractTokenParser;

/**
 * Class TokenParser
 *
 * @package ttempleton\nocache\twig
 * @author Benjamin Fleming
 * @author Thomas Templeton
 * @since 2.0.0
 */
class TokenParser extends AbstractTokenParser
{
	public function getTag()
	{
		return 'nocache';
	}

	public function parse(TwigToken $token)
	{
		$parser = $this->parser;
		$stream = $parser->getStream();

		$context = null;
		if ($stream->nextIf(TwigToken::NAME_TYPE, 'with'))
		{
			$context = $parser->getExpressionParser()->parseExpression();
		}

		$stream->expect(TwigToken::BLOCK_END_TYPE);

		$body = $parser->subparse([$this, 'decideEnd']);
		$body->setSourceContext($stream->getSourceContext());

		$stream->next();
		$stream->expect(TwigToken::BLOCK_END_TYPE);

		return new Node(
			$body,
			$context ?? new TwigNode(),
			$token->getLine(),
			$this->getTag()
		);
	}

	public function decideEnd(TwigToken $token)
	{
		return $token->test(['endnocache']);
	}
}
