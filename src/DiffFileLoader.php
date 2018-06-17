 *
 * Take apart the Git Diff Output and return an array of fileName to lines changed
	const LINE_TYPE_MODIFY_PERMS = 'permissions';
			if (empty($line)) {
				continue;
			}
			case preg_match('/^(old|new) mode (?:.*)$/', $line, $matches) === 1:
				$type = self::LINE_TYPE_MODIFY_PERMS;
				break;
			case preg_match('/^@@ (.*) (.*) @@(.*)$/', $line, $matches) === 1:
				// todo Git gives '@@ -8,6 +8,8 @@ use Exception;' why?
		// Only add PHP files
		if (preg_match('/^(.*).php$/', $currentFile, $matches) !== 1) {
			return $diff;
		}
