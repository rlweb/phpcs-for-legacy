<?php

declare(strict_types=1);

namespace Rlweb\PHPCSLegacy;

/**
 * Class DiffIssues
 *
 * Diff a list of issues given from PHP-CS with a list of changed lines on files
 */
class DiffIssues
{
	/**
	 * @param int[]   $changedLines
	 * @param mixed[] $fileIssues
	 * @return mixed[]
	 * @throws \Exception
	 */
	public function run(array $changedLines, array $fileIssues): array
	{
		$newFileIssues = [];
		foreach ($fileIssues as $file => $issues) {
			$newIssues = [];
			foreach ($issues as $issue) {
				if (!isset($changedLines[$file])) {
					throw new \Exception('File Folder Mismatch File:' . $file);
				}
				if (!in_array($issue['line'], $changedLines[$file], true)) {
					continue;
				}
				$newIssues[] = $issue;
			}

			if (!$newIssues) {
				continue;
			}
			$newFileIssues[$file] = $newIssues;
		}

		return $newFileIssues;
	}
}
