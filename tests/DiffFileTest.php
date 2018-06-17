<?php

namespace Rlweb\PHPCSLegacyTests;

use PHPUnit\Framework\TestCase;
use Rlweb\PHPCSLegacy\DiffFileLoader;

class DiffFileTest extends TestCase
{
	public function testDiffFile()
	{
		$diffPatch = <<<EXAMPLE
diff --git a/tests/DiffFileTest.php b/tests/DiffFileTest.php
index 91bf0d3..37f3aa3 100644
--- a/tests/DiffFileTest.php
+++ b/tests/DiffFileTest.php
@@ -2,9 +2,12 @@
 
 namespace Rlweb\PHPCSLegacyTests;
 
-use Rlweb\PHPCSLegacy\GitDiffTool;
+use PHPUnit\Framework\TestCase;
 
-class GitDiffToolTest extends \PHPUnit_Framework_TestCase
+class DiffFileTest extends TestCase
 {
+       public function testDiffFile()
+       {
 
+       }
 }
EXAMPLE;
		$diffFile = new DiffFileLoader();
		$this->assertSame(
			['tests/DiffFileTest.php' => [5, 7, 9, 10, 12]],
			$diffFile->load($diffPatch)
		);
	}

	public function testDiffNewFile()
	{
		$diffPatch = <<<EXAMPLE
diff --git a/src/ChangeLog.php b/src/ChangeLog.php
new file mode 100644
index 0000000..199589a
--- /dev/null
+++ b/src/ChangeLog.php
@@ -0,0 +1,15 @@
+<?php
+
+declare(strict_types=1);
+
+namespace Rlweb\PHPCSLegacy;
+
+/**
+ * Class ChangeLog
+ */
+class ChangeLog
+{
+       private \$files = [];
+
+
+}
\ No newline at end of file
EXAMPLE;
		$diffFile = new DiffFileLoader();
		$this->assertSame(
			['src/ChangeLog.php' => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14]],
			$diffFile->load($diffPatch)
		);
	}
	public function testDiffChmodFile()
	{
		$diffPatch = <<<EXAMPLE
diff --git a/bin/phpcslegacy b/bin/phpcslegacy
old mode 100644
new mode 100755
index 9b14859..7d92dc5
--- a/bin/phpcslegacy
+++ b/bin/phpcslegacy
@@ -1,11 +1,11 @@
 #!/usr/bin/env php
 <?php
-require __DIR__.'/vendor/autoload.php';
+require __DIR__ . '/../vendor/autoload.php';
 
 use Symfony\Component\Console\Application;
 
 \$application = new Application();
 
-\$application->add(new \Rlweb\PHPCSLegacy\PHPCSLegacyCommand());
+\$application->add(new \Rlweb\PHPCSLegacy\PHPCSLegacyCommand('run'));
 
 \$application->run();
\ No newline at end of file
EXAMPLE;
		$diffFile = new DiffFileLoader();
		$this->assertSame(
			['bin/phpcslegacy' => [3,9]],
			$diffFile->load($diffPatch)
		);
	}
}
