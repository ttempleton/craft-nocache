<?php
namespace Craft;

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
