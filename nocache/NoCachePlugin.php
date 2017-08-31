<?php
namespace Craft;

/**
 * Class NoCachePlugin
 *
 * Thank you for using Craft No-Cache!
 * @see https://github.com/benjamminf/craft-nocache
 * @package Craft
 */
class NoCachePlugin extends BasePlugin
{
	public function getName()
	{
		return Craft::t("No-Cache");
	}

	public function getDescription()
	{
		return Craft::t("A Twig extension to escape caching inside cache blocks");
	}

	public function getVersion()
	{
		return '1.0.3';
	}

	public function getCraftMinimumVersion()
	{
		return '2.6';
	}

	public function getPHPMinimumVersion()
	{
		return '5.4';
	}

	public function getSchemaVersion()
	{
		return '0.1.0';
	}

	public function getDeveloper()
	{
		return 'Benjamin Fleming';
	}

	public function getDeveloperUrl()
	{
		return 'http://benjamminf.github.io';
	}

	public function getDocumentationUrl()
	{
		return 'https://github.com/benjamminf/craft-nocache';
	}

	public function getReleaseFeedUrl()
	{
		return 'https://raw.githubusercontent.com/benjamminf/craft-nocache/master/releases.json';
	}

	public function isCraftRequiredVersion()
	{
		return version_compare(craft()->getVersion(), $this->getCraftMinimumVersion(), '>=');
	}

	public function isPHPRequiredVersion()
	{
		return version_compare(PHP_VERSION, $this->getPHPMinimumVersion(), '>=');
	}

	public function onBeforeInstall()
	{
		return $this->isCraftRequiredVersion() && $this->isPHPRequiredVersion();
	}

	public function addTwigExtension()
	{
		Craft::import('plugins.nocache.twigextensions.NoCacheTwigExtension');

		return new NoCacheTwigExtension();
	}

	public function init()
	{
		parent::init();

		// 1. Only enable the plugin's functionality if template caching is enabled
		// 2. Watch for `nocache` blocks only if it's a site request
		if(craft()->noCache->isCacheEnabled() && craft()->request->isSiteRequest())
		{
			// Working directory may change during `register_shutdown_function`, so let's deal with that by caching the
			// current working directory to a constant
			define('NOCACHEPLUGIN_CWD', getcwd());

			// Capture the raw request output right before it's sent to the requester
			register_shutdown_function(function()
			{
				$devMode = craft()->config->get('devMode');
				$output = ob_get_clean();

				// Find any `nocache` placeholder tags in the output
				$newOutput = preg_replace_callback('/<no-cache>([a-z0-9]+)-([a-z0-9]+)<\\/no-cache>/i', function($matches) use($devMode)
				{
					$id = $matches[1];
					$type = $matches[2];

					// Change working directory if need be
					if(getcwd() !== NOCACHEPLUGIN_CWD)
					{
						chdir(NOCACHEPLUGIN_CWD);
					}

					// Force-render the internals of the `nocache` tag and put it in place of the placeholder
					$template = craft()->noCache->render($id, $type);

					// If it failed to render, output the original tag in place so it can be used to debug
					$debugOutput = $devMode ? $matches[0] : '';

					return $template === false ? $debugOutput : $template;

				}, $output);

				echo $newOutput;
			});
		}
	}
}
