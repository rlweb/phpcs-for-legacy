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
	 * @throws \Exception
	 */
	public function run(array $fileList): array
	{
		$errorsPerFile = [];
		foreach ($fileList as $file) {
			$cmd = new Process('vendor' . DIRECTORY_SEPARATOR . 'squizlabs' . DIRECTORY_SEPARATOR .
				'php_codesniffer' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR .
				'phpcs --standard=ruleset.xml ' . $file . ' --report=json');
			$cmd->run(function($type, $data) use (&$errorsPerFile, $file) {
				try {
					if (!empty($data)) {
						if ($decodedData = json_decode($data, true)) {
							$files = reset($decodedData['files']);
							$errorsPerFile[$file] = $files['messages'];
						}
					}
				} catch(\Exception $e) {
					// todo
				}
			});
		}

		return $errorsPerFile;
	}
}
