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
	const LINE_TYPE_DIFF_HEADER = 'header';
	const LINE_TYPE_EXTENDED_HEADER = 'extendedHeader';
	const LINE_TYPE_TO_FROM_HEADER = 'toFromHeader';
	const LINE_TYPE_CHUNK_HEADER = 'chunkHeader';
	const LINE_TYPE_ADD_LINE = 'newLine';
	const LINE_TYPE_REMOVE_LINE = 'removeLine';
	const LINE_TYPE_DIFF_LINE = 'diffLine;';
	const LINE_TYPE_NO_NEWLINE = 'noNewline';
	const LINE_TYPE_MODIFY_PERMS = 'permissions';

	/**
	 * @param $diffOutput
	 * @return array
	 * @throws Exception
	 */
	public function load($diffOutput): array
	{
		$lineByLineDiffOutput = explode(PHP_EOL, $diffOutput);

		$diffArray = [];
		$currentFile = null;
		$currentLine = null;
		$modifiedLines = [];

		// Separate the diff output
		$lineByLineDiffOutput = preg_split('/\\n/', reset($lineByLineDiffOutput));

		foreach ($lineByLineDiffOutput as $line) {
			if (empty($line)) {
				continue;
			}
			list($lineType, $extra) = $this->getLineType($line);

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
					$currentLine--;
					break;
				case  self::LINE_TYPE_ADD_LINE:
					$currentLine++;
					$modifiedLines[] = $currentLine;
					break;
				case self::LINE_TYPE_DIFF_LINE:
					$currentLine++;
					break;
			}
		}
		return $this->addFileWithLines($diffArray, $modifiedLines, $currentFile);
	}

	/**
	 * Work out line type and take any useful data from it!
	 *
	 * @param $line
	 * @return array
	 * @throws Exception
	 */
	private function getLineType($line): array
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
				$extra['fromFileRange'] = $matches[1];
				$lineMatches = [];
				preg_match('/^(-|\+)(.*),(.*)$/', $extra['fromFileRange'], $lineMatches);
				$extra['firstLine'] = $lineMatches[2];
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
	 * @param $diff
	 * @param $modifiedLines
	 * @param $currentFile
	 * @return array
	 */
	private function addFileWithLines($diff, $modifiedLines, $currentFile): array
	{
		// Only add PHP files
		if (preg_match('/^(.*).php$/', $currentFile, $matches) !== 1) {
			return $diff;
		}

		$modifiedLines = array_unique($modifiedLines);
		sort($modifiedLines);
		$diff[$currentFile] = $modifiedLines;

		return $diff;
	}
}
