<?php

declare(strict_types=1);

namespace Rlweb\PHPCSLegacy;

use Symfony\Component\Process\Process;

/**
 * Class PHPCSRunner
 */
class PHPCSRunner
{
	/**
	 * @param array $fileList
	 * @return array
	 */
	public function run(array $fileList): array
	{
		$errorsPerFile = [];
		foreach ($fileList as $file) {
			$cmd = new Process('vendor/bin/phpcs ' . $file . ' --report=json');
			$cmd->run();
			$result = json_decode($cmd->getOutput(), true);
			$errorsPerFile[$file] = reset($result['files'])['messages'];
		}

		return $errorsPerFile;
	}
}
