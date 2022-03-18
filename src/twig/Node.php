<?php

namespace ttempleton\nocache\twig;

use Craft;
use craft\helpers\StringHelper;
use ttempleton\nocache\Plugin as NoCache;
use Twig\Compiler;
use Twig\Node\Node as TwigNode;

/**
 * Class Node
 *
 * @package ttempleton\nocache\twig
 * @author Benjamin Fleming
 * @author Thomas Templeton
 * @since 2.0.0
 */
class Node extends TwigNode
{
    /**
     * @var string|null
     */
    private ?string $_id;

    /**
     * @param TwigNode $body
     * @param TwigNode $context
     * @param int $line
     * @param string|null $tag
     * @param int|null $counter
     */
    public function __construct(TwigNode $body, TwigNode $context, int $line, ?string $tag = null, ?int $counter = null)
    {
        parent::__construct([
            'body' => $body,
            'context' => $context,
        ], [], $line, $tag);

        $this->setSourceContext($body->getSourceContext());
        $this->_id = $counter !== null ? (string)$counter : StringHelper::randomString(24);
    }

    /**
     * @inheritdoc
     */
    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this);

        // Generate an ID for the `nocache` block
        $templateClassName = $compiler->getEnvironment()->getTemplateClass($this->getSourceContext()->getName());
        $id = hash('sha256', $templateClassName . $this->_id);

        // Create a wrapper node for the internals of the `nocache` block
        // This will serve as the node that'll actually render the contents of that block, whereas this node's purpose
        // is to render the placeholder tags
        $bodyNode = $this->getNode('body');
        $bodyNode->setSourceContext($this->getSourceContext());

        $body = new Node_Body($bodyNode, $id, $this->lineno, $this->tag);

        // Compile the internals to a separate compiled template file for later use
        NoCache::$plugin->methods->compile($id, $body);

        // Capture the passed context
        $contextNode = $this->getNode('context');

        if (!empty($contextNode->nodes)) {
            $compiler->write('$subContext = ')->subcompile($contextNode)->raw(';' . PHP_EOL);
        } else {
            $compiler->write('$subContext = [];' . PHP_EOL);
        }

        $compiler
            // Only bother tagging the output for post-processing if caching is enabled
            ->write('if (' . NoCache::class . '::$plugin->methods->isCacheEnabled()) {' . PHP_EOL)
            ->indent()

                // 1. Saves the captured context at that point in rendering to the cache. This is so when rendering the
                //    internals of the `nocache` block later on, the context can be revived and will work as per usual.
                // 2. Renders the placeholder tag which will later be replaced by the rendered body of the `nocache` tag.
                ->write('$contextId = ' . StringHelper::class . '::randomString(8);' . PHP_EOL)
                ->write(Craft::class . "::\$app->getCache()->set('nocache_{$id}_' . \$contextId, \$subContext, 0);" . PHP_EOL)
                ->write("echo '<no-cache>{$id}-' . \$contextId . '</no-cache>';" . PHP_EOL)

            ->outdent()
            ->write('} else {' . PHP_EOL)
            ->indent()

                // Otherwise render the template with the context directly
                ->write("echo " . NoCache::class . "::\$plugin->methods->render('{$id}', \$subContext);" . PHP_EOL)

            ->outdent()
            ->write('}' . PHP_EOL);
    }
}
