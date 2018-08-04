<?php

declare(strict_types=1);

namespace Rlweb\PHPCSLegacy;

use Exception;

/**
 * Class DiffFileLoader
 *
 * Take apart the Git Diff Output and return an array of fileName to lines changed
 */
class DiffFileLoader
{
	private const LINE_TYPE_DIFF_HEADER = 'header';
	private const LINE_TYPE_EXTENDED_HEADER = 'extendedHeader';
	private const LINE_TYPE_TO_FROM_HEADER = 'toFromHeader';
	private const LINE_TYPE_CHUNK_HEADER = 'chunkHeader';
	private const LINE_TYPE_ADD_LINE = 'newLine';
	private const LINE_TYPE_REMOVE_LINE = 'removeLine';
	private const LINE_TYPE_DIFF_LINE = 'diffLine;';
	private const LINE_TYPE_NO_NEWLINE = 'noNewline';
	private const LINE_TYPE_MODIFY_PERMS = 'permissions';

	/**
	 * @param $diffOutput
	 * @return int[]
	 * @throws Exception
	 */
	public function load(string $diffOutput): array
	{
		$diffArray = [];
		$currentFile = null;
		$currentLine = null;
		$modifiedLines = [];

		// Separate the diff output
		$lineByLineDiffOutput = array_filter(preg_split('/\\n/', $diffOutput));
		foreach ($lineByLineDiffOutput as $line) {
			[$lineType, $extra] = $this->getLineType($line);

			switch ($lineType) {
				case self::LINE_TYPE_DIFF_HEADER:
					if ($currentFile) {
						$diffArray = $this->addFileWithLines($diffArray, $modifiedLines, $currentFile);
						$currentLine = null;
						$modifiedLines = [];
					}
					$currentFile = $extra['fileName'];
					break;
				case self::LINE_TYPE_CHUNK_HEADER:
					$currentLine = $extra['firstLine'];
					break;
				case self::LINE_TYPE_ADD_LINE:
					$currentLine = $currentLine + 1;
					// Adding the line before and the line after to give a bit more of push!
					$modifiedLines[] = $currentLine - 1;
					$modifiedLines[] = $currentLine;
					$modifiedLines[] = $currentLine + 1;
					break;
				case self::LINE_TYPE_REMOVE_LINE:
					$currentLine = $currentLine - 1;
					break;
				case self::LINE_TYPE_DIFF_LINE:
					$currentLine = $currentLine + 1; // todo really this part seems a little off to me! based on patch
					break;
			}
		}
		if ($currentFile) {
			return $this->addFileWithLines($diffArray, $modifiedLines, $currentFile);
		}
		return [];
	}

	/**
	 * Work out line type and take any useful data from it!
	 *
	 * @param $line
	 * @return string[]
	 * @throws Exception
	 */
	private function getLineType(string $line): array
	{
		$matches = [];
		$extra = [];
		switch (true) {
			case preg_match('/^diff --(?:combined|cc|git) a\/(.*) b\/(.*)$/', $line, $matches) === 1:
				$type = self::LINE_TYPE_DIFF_HEADER;
				$extra['fileName'] = $matches[1];
				break;
			case preg_match('/^(old|new) mode (?:.*)$/', $line, $matches) === 1:
				$type = self::LINE_TYPE_MODIFY_PERMS;
				break;
			case preg_match('/^(index|mode|new file mode|deleted file mode) (?:.*)$/', $line, $matches) === 1:
				$type = self::LINE_TYPE_EXTENDED_HEADER;
				$extra['type'] = $matches[0];
				break;
			case preg_match('/^(---|\+\+\+) (?:.*)$/', $line, $matches) === 1:
				$type = self::LINE_TYPE_TO_FROM_HEADER;
				$extra['type'] = $matches[0];
				break;
			case preg_match('/^@@ (.*) (.*) @@(.*)$/', $line, $matches) === 1:
				// todo Git gives '@@ -8,6 +8,8 @@ use Exception;' why?
				// todo generally for this use case, nah but there could be more @'s
				// "There are (number of parents + 1) @ characters in the chunk header for combined diff format."
				$type = self::LINE_TYPE_CHUNK_HEADER;
				$extra['fromFileRange'] = $matches[2];
				$lineMatches = [];
				preg_match('/^(-|\+)(.*)(,?)(.*)$/', $extra['fromFileRange'], $lineMatches);
				$extra['firstLine'] = (int) $lineMatches[2];
				$extra['toFileRange'] = $matches[0];
				break;
			case preg_match('/^\+(.*)$/', $line, $matches) === 1:
				$type = self::LINE_TYPE_ADD_LINE;
				break;
			case preg_match('/^-(.*)$/', $line, $matches) === 1:
				$type = self::LINE_TYPE_REMOVE_LINE;
				break;
			case preg_match('#No newline at end of file$#', $line, $matches) === 1:
				$type = self::LINE_TYPE_NO_NEWLINE;
				break;
			case preg_match('/^ (.*)$/', $line, $matches) === 1:
				$type = self::LINE_TYPE_DIFF_LINE;
				break;
			default:
				throw new Exception('Unknown Git Diff Output' . $line);
		}

		return [$type, $extra];
	}

	/**
	 * Add new file to list of files which have been changed!
	 *
	 * @param int[]       $diff
	 * @param int[]       $modifiedLines
	 * @param null|string $currentFile
	 * @return int[]
	 */
	private function addFileWithLines(array $diff, array $modifiedLines, ?string $currentFile): array
	{
		if (!$currentFile) {
			return $diff;
		}
		// Only add PHP files
		if (preg_match('/^(.*).php$/', $currentFile, $matches) !== 1) {
			return $diff;
		}

		$modifiedLines = array_unique($modifiedLines, SORT_NUMERIC);
		sort($modifiedLines);
		$diff[$currentFile] = $modifiedLines;

		return $diff;
	}
}
