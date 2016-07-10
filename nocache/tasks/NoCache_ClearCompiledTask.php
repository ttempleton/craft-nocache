<?php
namespace Craft;

/**
 * Class NoCache_ClearCompiledTask
 * This task is not currently being used as there are weird issues with the `clearCompiled()` method when running it
 * in a task. Might as well leave this class in here though until I figure it out.
 *
 * @package Craft
 */
class NoCache_ClearCompiledTask extends BaseTask
{
	public function getDescription()
	{
		return Craft::t("Clearing compiled nodes");
	}

	public function getTotalSteps()
	{
		return 1;
	}

	public function runStep($step)
	{
		craft()->noCache->clearCompiled();

		return true;
	}
}
