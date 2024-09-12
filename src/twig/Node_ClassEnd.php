<?php

namespace ttempleton\nocache\twig;

use Twig\Compiler;
use Twig\Node\Node as TwigNode;
use Composer\Semver\VersionParser;
use Composer\InstalledVersions;

/**
 * Class Node_ClassEnd
 * This class is used to output the `loadTemplate()` method for a No-Cache template class, to allow loading of the Twig
 * template the `{% nocache %}` block is on (for example, when trying to import macros), since the No-Cache nodes will
 * share the source context and therefore inherit the original template's name.
 *
 * @package ttempleton\nocache\twig
 * @author Thomas Templeton
 * @since 2.0.8
 */
class Node_ClassEnd extends TwigNode
{
    /**
     * @param TwigNode $node
     */
    public function __construct(TwigNode $node)
    {
        parent::__construct();
        $this->setSourceContext($node->getSourceContext());
    }

    /**
     * @inheritdoc
     */
    public function compile(Compiler $compiler): void
    {
        /**
         * twig/twig 3.13 added return types to Twig\Template::loadTemplate
         * which causes errors when you're on a newer version of twig
         *
         * https://github.com/twigphp/Twig/blob/v3.13.0/src/Template.php#L280
         */
        if (InstalledVersions::satisfies(new VersionParser(), 'twig/twig', "^3.13")) {
            $returnTypes = 'Twig\Template|Twig\TemplateWrapper';
        } else {
            $returnTypes = '';
        }

        $compiler
            ->raw(PHP_EOL)
            ->write('protected function loadTemplate($template, $templateName = null, $line = null, $index = null)' . $returnTypes . PHP_EOL)
            ->write('{' . PHP_EOL)
            ->indent()
                // If $template === $this->getTemplateName(), then Twig will assume the No-Cache template is the correct
                // one to load, when we actually want the Craft project's template with that name.
                ->write('return $template === $this->getTemplateName()' . PHP_EOL)
                ->indent()
                    ->write('? $this->env->loadTemplate($template, $index)' . PHP_EOL)
                    ->write(': parent::loadTemplate($template, $templateName, $line, $index);' . PHP_EOL)
                ->outdent()
            ->outdent()
            ->write('}' . PHP_EOL);
    }
}
