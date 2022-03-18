<?php

namespace ttempleton\nocache;

use Craft;

use craft\base\Plugin as BasePlugin;
use craft\events\TemplateEvent;
use craft\web\View;
use ttempleton\nocache\twig\Extension as TwigExtension;
use yii\base\Event;

/**
 * Class Plugin
 *
 * @package ttempleton\nocache
 * @author Benjamin Fleming
 * @author Thomas Templeton
 * @since 2.0.0
 */
class Plugin extends BasePlugin
{
    /**
     * @var Plugin|null The instance of this plugin.
     */
    public static ?Plugin $plugin;

    /**
     * Plugin initializer.
     */
    public function init(): void
    {
        parent::init();

        self::$plugin = $this;

        $this->setComponents([
            'methods' => Service::class,
        ]);

        if (Craft::$app->getRequest()->getIsSiteRequest()) {
            Craft::$app->getView()->registerTwigExtension(new TwigExtension());
            $this->_addEventListener();
        }
    }

    /**
     * Adds No-Cache's listener for Craft's after render page template event if it's a site request and template caching
     * is enabled.
     */
    private function _addEventListener(): void
    {
        // 1. Only enable the plugin's functionality if template caching is enabled
        // 2. Watch for `nocache` blocks only if it's a site request
        if ($this->methods->isCacheEnabled()) {
            // Capture the page template output
            Event::on(View::class, View::EVENT_AFTER_RENDER_PAGE_TEMPLATE, function(TemplateEvent $event) {
                $devMode = Craft::$app->getConfig()->getGeneral()->devMode;
                $event->output = $this->_renderTagContent($event->output, $devMode);
            });
        }
    }

    /**
     * Renders the internals of No-Cache placeholder tags in a rendered page template.
     *
     * @param string $output - a rendered page template
     * @param bool $debug - whether to output the original tag for debugging purposes if rendering failed
     * @return string - the rendered page template, including any rendered No-Cache content
     */
    private function _renderTagContent(string $output, bool $debug): string
    {
        // Find any `nocache` placeholder tags in the output
        return preg_replace_callback('/<no-cache>([a-z0-9]+)-([a-z0-9]+)<\\/no-cache>/i', function($matches) use ($debug) {
            $id = $matches[1];
            $type = $matches[2];

            // Force-render the internals of the `nocache` tag and put it in place of the placeholder
            $template = $this->methods->render($id, $type);

            // If it failed to render and debugging is enabled, output the original tag in place
            $debugOutput = $debug ? $matches[0] : '';

            return $template !== null ? $template : $debugOutput;
        }, $output);
    }
}
