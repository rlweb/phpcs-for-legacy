<?php

declare(strict_types=1);

namespace Rlweb\PHPCSLegacy;

use Symfony\Component\Process\Process;

/**
 * Class GitDiffExec
 *
 * Grab the Git Diff output
 */
class GitDiffExec
{
	public function run($branch = null): string
	{
		$process = new Process('git diff head');
		$process->run();
		// todo handle errors
		return $process->getOutput();
	}
}
