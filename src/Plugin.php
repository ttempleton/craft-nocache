<?php
namespace ttempleton\nocache;

use yii\base\Event;

use Craft;
use craft\base\Plugin as BasePlugin;
use craft\events\TemplateEvent;
use craft\web\View;

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
	 * @var Plugin The instance of this plugin.
	 */
	public static $plugin;

	/**
	 * Plugin initializer.
	 */
	public function init()
	{
		parent::init();

		self::$plugin = $this;

		$this->setComponents([
			'methods' => Service::class,
		]);

		Craft::$app->getView()->registerTwigExtension(new TwigExtension());

		// 1. Only enable the plugin's functionality if template caching is enabled
		// 2. Watch for `nocache` blocks only if it's a site request
		if ($this->methods->isCacheEnabled() && Craft::$app->getRequest()->getIsSiteRequest())
		{
			// Capture the page template output
			Event::on(View::class, View::EVENT_AFTER_RENDER_PAGE_TEMPLATE, function(TemplateEvent $event)
			{
				$devMode = Craft::$app->getConfig()->getGeneral()->devMode;

				// Find any `nocache` placeholder tags in the output
				$newOutput = preg_replace_callback('/<no-cache>([a-z0-9]+)-([a-z0-9]+)<\\/no-cache>/i', function($matches) use($devMode)
				{
					$id = $matches[1];
					$type = $matches[2];

					// Force-render the internals of the `nocache` tag and put it in place of the placeholder
					$template = $this->methods->render($id, $type);

					// If it failed to render, output the original tag in place so it can be used to debug
					$debugOutput = $devMode ? $matches[0] : '';

					return $template === null ? $debugOutput : $template;

				}, $event->output);

				$event->output = $newOutput;
			});
		}
	}
}
