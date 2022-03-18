<?php

namespace ttempleton\nocache\twig;

use Twig\Extension\AbstractExtension;

/**
 * Class Extension
 *
 * @package ttempleton\nocache\twig
 * @author Benjamin Fleming
 * @author Thomas Templeton
 * @since 2.0.0
 */
class Extension extends AbstractExtension
{
    /**
     * @inheritdoc
     */
    public function getTokenParsers(): array
    {
        return [
            new TokenParser(),
        ];
    }
}
