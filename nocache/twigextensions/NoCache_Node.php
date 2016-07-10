<?php
namespace Craft;

require_once 'NoCache_Node_Body.php';

/**
 * Class NoCache_Node
 *
 * @package Craft
 */
class NoCache_Node extends \Twig_Node
{
	public function __construct(\Twig_Node $body, $line, $tag = null)
	{
		parent::__construct(['body' => $body], [], $line, $tag);
	}

	public function compile(\Twig_Compiler $compiler)
	{
		$compiler->addDebugInfo($this);

		// Generate a random ID for the `nocache` block
		$id = StringHelper::randomString();

		// Create a wrapper node for the internals of the `nocache` block
		// This will serve as the node that'll actually render the contents of that block, whereas this node's purpose
		// is to render the placeholder tags
		$body = new NoCache_Node_Body($this->getNode('body'), $id, $this->lineno, $this->tag);

		// Only bother tagging the `nocache` block if template caching is enabled
		if(craft()->noCache->isCacheEnabled())
		{
			// Compile the internals to a separate compiled template file for later use
			craft()->noCache->compile($id, $compiler, $body);

			// 1. Saves the entire context (variables, macros, etc.) at that point in rendering to the cache. This is so
			//    when rendering the internals of the `nocache` block later on, the context can be revived and any
			//    variables or macros will work as per usual.
			// 2. Renders the placeholder tag which will later be replaced by the rendered body of the `nocache` tag.
			$compiler
				->write("\\Craft\\craft()->cache->set('nocache_{$id}', \$context);")
				->write("echo '<!--nocache-{$id}-->';");
		}
		else
		{
			// Just directly compile the body if caching is disabled
			$body->compile($compiler);
		}
	}
}
