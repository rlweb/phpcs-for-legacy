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
			['tests/DiffFileTest.php' => [4, 5, 6, 7, 8, 9, 10, 11, 12]],
			$diffFile->load($diffPatch)
		);
	}

	public function testDiffFileSmallChange()
	{
		$diffPatch = <<<EXAMPLE
diff --git a/www/blu/lib/database/pool.php b/www/blu/lib/database/pool.php
index 2410bba814f..86e08da39c9 100755
--- a/www/blu/lib/database/pool.php
+++ b/www/blu/lib/database/pool.php
@@ -126 +126 @@ class Pool
-		\$this->_poolSettings = \$settings;
+		\$this->_poolSettings =\$settings;
diff --git a/www/blu/plugins/payment/paypal/base.php b/www/blu/plugins/payment/paypal/base.php
index 9bacaf1d19a..61569317934 100755
--- a/www/blu/plugins/payment/paypal/base.php
+++ b/www/blu/plugins/payment/paypal/base.php
@@ -19 +19 @@ abstract class Base extends \Blu\Plugins\Payment\Base
-	protected \$_version = '98.0';
+	protected \$_version = '98.1';
EXAMPLE;
		$diffFile = new DiffFileLoader();
		$this->assertSame(
			[
				'www/blu/lib/database/pool.php'           => [125, 126, 127],
				'www/blu/plugins/payment/paypal/base.php' => [18, 19, 20],
			],
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
			['src/ChangeLog.php' => [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17]],
			$diffFile->load($diffPatch)
		);
	}
}
