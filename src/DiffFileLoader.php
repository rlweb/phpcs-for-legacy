	private const LINE_TYPE_DIFF_HEADER = 'header';
	private const LINE_TYPE_EXTENDED_HEADER = 'extendedHeader';
	private const LINE_TYPE_TO_FROM_HEADER = 'toFromHeader';
	private const LINE_TYPE_CHUNK_HEADER = 'chunkHeader';
	private const LINE_TYPE_ADD_LINE = 'newLine';
	private const LINE_TYPE_REMOVE_LINE = 'removeLine';
	private const LINE_TYPE_DIFF_LINE = 'diffLine;';
	private const LINE_TYPE_NO_NEWLINE = 'noNewline';
	private const LINE_TYPE_MODIFY_PERMS = 'permissions';
	 * @return int[]
	public function load(string $diffOutput): array
		$lineByLineDiffOutput = array_filter(preg_split('/\\n/', $diffOutput));
			[$lineType, $extra] = $this->getLineType($line);
				case self::LINE_TYPE_ADD_LINE:
					$currentLine = $currentLine + 1;
					// Adding the line before and the line after to give a bit more of push!
					$modifiedLines[] = $currentLine - 1;
					$modifiedLines[] = $currentLine + 1;
					break;
				case self::LINE_TYPE_REMOVE_LINE:
					$currentLine = $currentLine - 1;
					$currentLine = $currentLine + 1; // todo really this part seems a little off to me! based on patch
		if ($currentFile) {
			return $this->addFileWithLines($diffArray, $modifiedLines, $currentFile);
		}
		return [];
	 * @return string[]
	private function getLineType(string $line): array
				$extra['fromFileRange'] = $matches[2];
				preg_match('/^(-|\+)(.*)(,?)(.*)$/', $extra['fromFileRange'], $lineMatches);
				$extra['firstLine'] = (int) $lineMatches[2];
	 * Add new file to list of files which have been changed!
	 *
	 * @param int[]       $diff
	 * @param int[]       $modifiedLines
	 * @param null|string $currentFile
	 * @return int[]
	private function addFileWithLines(array $diff, array $modifiedLines, ?string $currentFile): array
		if (!$currentFile) {
			return $diff;
		}
		$modifiedLines = array_unique($modifiedLines, SORT_NUMERIC);