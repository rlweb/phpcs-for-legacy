<?php

declare(strict_types=1);

namespace Rlweb\PHPCSLegacy;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class PHPCSLegacyCommand
 */
class PHPCSLegacyCommand extends Command
{
	protected function configure(): void
	{
		$this->setName('phpcs')
			->setDescription('Runs PHPCS on git modified files');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$diffOutput = $this->getGitDiffOutput();
		$changedLines = $this->transformGitDiff($diffOutput);
		$output->writeln(
			"Changed Lines: " . json_encode($changedLines, JSON_PRETTY_PRINT),
			OutputInterface::VERBOSITY_VERBOSE
		);
		$issues = $this->runPHPCSonFiles(array_keys($changedLines));
		$output->writeln(
			"All Issues: " . json_encode($issues, JSON_PRETTY_PRINT),
			OutputInterface::VERBOSITY_VERY_VERBOSE
		);
		if (!$issues) {
			// No errors returned!
			return 0;
		}
		$issues = $this->diffIssuesWithChangedLines($changedLines, $issues);
		$this->renderIssues($output, $issues);

		// Return valid status code
		return count($issues) ? 1 : 0;
	}

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

	/**
	 * @param array $files
	 * @return null|array
	 * @throws \Exception
	 */
	private function runPHPCSonFiles(array $files): ?array
	{
		$phpCsRunner = new PHPCSRunner();

		return $phpCsRunner->run($files);
	}

	private function diffIssuesWithChangedLines(array $changedLines, array $issues): array
	{
		$diffIssues = new DiffIssues();

		return $diffIssues->run($changedLines, $issues);
	}

	/**
	 * @param OutputInterface $output
	 * @param mixed[]         $issues
	 */
	private function renderIssues(OutputInterface $output, array $issues)
	{
		$issuesTotal = 0;
		foreach ($issues as $fileName => $file) {
			$output->writeln('File: ' . $fileName);
			$messages = array_map(function ($row) {
				return [
					$row['line'],
					$row['message'],
				];
			}, $file);

			$table = new Table($output);
			$table
				->setHeaders(['Line', 'Message'])
				->setRows($messages);
			$table->render();
			$issuesTotal = $issuesTotal + count($file);
		}

		if (!$issuesTotal) {
			return;
		}
		$output->writeln(
			"Diff Issues: " . json_encode($issues, JSON_PRETTY_PRINT),
			OutputInterface::VERBOSITY_VERBOSE
		);
		$output->writeln('Count - Files:' . count($issues) . ' Issues:' . $issuesTotal);
	}
}
