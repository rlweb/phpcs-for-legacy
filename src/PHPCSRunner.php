<?php

declare(strict_types=1);

namespace Rlweb\PHPCSLegacy;

use Symfony\Component\Process\Process;

/**
 * Class PHPCSRunner
 */
class PHPCSRunner
{
	private $command = 'vendor' . DIRECTORY_SEPARATOR . 'squizlabs' . DIRECTORY_SEPARATOR .
	'php_codesniffer' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR .
	'phpcs --standard=ruleset.xml --report=json ';

	/**
	 * @param array $fileList
	 * @return array
	 * @throws \Exception
	 */
	public function run(array $fileList): ?array
	{
		$cmd = new Process($this->command . implode(' ', $fileList));
		$cmd->run();
		if ($cmd->getExitCode() === 2) {
			$cmdOutput = $cmd->getOutput();
			$errorsPerFile = [];
			if ($decodedData = json_decode($cmdOutput, true)) {
				foreach ($decodedData['files'] as $file => $errors) {
					$fileName = ltrim(str_replace(getcwd(), '', $file), '/\\');
					$errorsPerFile[$fileName] = $errors['messages'];
				}
			}
			return $errorsPerFile;
		} else {
			return null;
		}
	}
}
