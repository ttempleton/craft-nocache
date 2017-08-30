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
	public function __construct(\Twig_Node $body, $context, $line, $tag = null)
	{
		parent::__construct([
			'body' => $body,
			'context' => $context,
		], [], $line, $tag);
	}

	public function compile(\Twig_Compiler $compiler)
	{
		$compiler->addDebugInfo($this);

		// Generate a random ID for the `nocache` block
		$id = StringHelper::randomString(24);

		// Create a wrapper node for the internals of the `nocache` block
		// This will serve as the node that'll actually render the contents of that block, whereas this node's purpose
		// is to render the placeholder tags
		$body = new NoCache_Node_Body($this->getNode('body'), $id, $this->lineno, $this->tag);

		// Compile the internals to a separate compiled template file for later use
		craft()->noCache->compile($id, $compiler, $body);

		// Capture the passed context
		$contextNode = $this->getNode('context');
		if($contextNode)
		{
			$compiler->raw('$subContext = ')->subcompile($contextNode)->raw(";\n");
		}
		else
		{
			$compiler->write('$subContext = [];');
		}

		$compiler
			// Only bother tagging the output for post-processing if caching is enabled
			->write('if(\\Craft\\craft()->noCache->isCacheEnabled())')
			->write('{')

				// 1. Saves the captured context at that point in rendering to the cache. This is so when rendering the
				//    internals of the `nocache` block later on, the context can be revived and will work as per usual.
				// 2. Renders the placeholder tag which will later be replaced by the rendered body of the `nocache` tag.
				->write('$contextId = \\Craft\\StringHelper::randomString(8);')
				->write("\\Craft\\craft()->cache->set('nocache_{$id}_' . \$contextId, \$subContext, 0);")
				->write("echo '<no-cache>{$id}-' . \$contextId . '</no-cache>';")

			->write('}')
			->write('else')
			->write('{')

				// Otherwise render the template with the context directly
				->write("echo \\Craft\\craft()->noCache->render('{$id}', \$subContext);")

			->write('}');
	}
}
