<?php

declare(strict_types=1);

namespace Rlweb\PHPCSLegacy;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PHPCSLegacyCommand extends Command
{
	protected function configure()
	{
		$this->setName('run')
			->setDescription('Runs PHPCS on git modified files');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$diffOutput = $this->getGitDiffOutput();
		$changedLines = $this->transformGitDiff($diffOutput);
		$issues = $this->runPHPCSonFiles(array_keys($changedLines));



		var_dump($modifiedIssues);exit();
	}

	/**
	 * @return string
	 */
	private function getGitDiffOutput(): string
	{
		$gitDiffExec = new GitDiffExec();

		return $gitDiffExec->run();
	}

	private function transformGitDiff(string $input): array
	{
		$diffFileLoader = new DiffFileLoader();

		return $diffFileLoader->load($input);
	}

	private function runPHPCSonFiles(array $files): array
	{
		$phpCsRunner = new PHPCSRunner();

		return $phpCsRunner->run($files);
	}

	}
}
