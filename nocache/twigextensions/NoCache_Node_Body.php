<?php
namespace Craft;

/**
 * Class NoCache_Node_Body
 * This will serve as the node that'll actually render the contents of a `nocache` block
 *
 * @package Craft
 */
class NoCache_Node_Body extends \Twig_Node
{
	protected $id;

	public function __construct(\Twig_Node $body, $id, $line, $tag = null)
	{
		parent::__construct(['body' => $body], [], $line, $tag);

		$this->id = $id;
	}

	public function compile(\Twig_Compiler $compiler)
	{
		$compiler
			->write("\$cachedContext = \\Craft\\craft()->cache->get('nocache_{$this->id}');")

			// Merge the cached context (if it exists) onto the current context before rendering the body
			// Make sure that the original context takes priority over the cached context, so variables that have been
			// updated are used instead (such as the `now` global variable)
			->write('if($cachedContext)')
			->write('{')
				->indent()
				->write('$context += $cachedContext;')
				->outdent()
			->write('}')

			->subcompile($this->getNode('body'));
	}
}
