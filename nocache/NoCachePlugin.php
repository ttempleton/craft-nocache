<?php
namespace Craft;

/**
 * Class NoCachePlugin
 *
 * Thank you for using Craft No-cache!
 * @see https://github.com/benjamminf/craft-nocache
 * @package Craft
 */
class NoCachePlugin extends BasePlugin
{
	public function getName()
	{
		return Craft::t("No-cache");
	}

	public function getDescription()
	{
		return Craft::t("");
	}

	public function getVersion()
	{
		return '0.1.0';
	}

	public function getCraftMinimumVersion()
	{
		return '2.6';
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

	public function onBeforeInstall()
	{
		return $this->isCraftRequiredVersion();
	}

	public function addTwigExtension()
	{
		Craft::import('plugins.nocache.twigextensions.NoCacheTwigExtension');

		return new NoCacheTwigExtension();
	}
}
