<?php

declare(strict_types=1);

namespace Rlweb\PHPCSLegacy;

/**
 * Class DiffIssues
 */
class DiffIssues
{
	/**
	 * @param array $changedLines
	 * @param array $fileIssues
	 * @return array
	 */
	public function run(array $changedLines, array $fileIssues): array
	{
		$newFileIssues = [];
		foreach ($fileIssues as $file => $issues) {
			$newIssues = [];
			foreach ($issues as $issue) {
				if (in_array($issue['line'], $changedLines[$file], true)) {
					$newIssues[] = $issue;
				}
			}

			if ($newIssues) {
				$newFileIssues[$file] = $newIssues;
			}
		}

		return $newFileIssues;
	}
}