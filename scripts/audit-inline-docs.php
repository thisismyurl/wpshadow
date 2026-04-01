<?php
/**
 * Inline Documentation Audit Utility
 *
 * Scans plugin PHP files for missing class-level and public method docblocks.
 *
 * Usage:
 *   php scripts/audit-inline-docs.php
 *   php scripts/audit-inline-docs.php --include-tests
 *
 * @package WPShadow
 * @since 0.6093.1200
 */

declare(strict_types=1);

if ( PHP_SAPI !== 'cli' ) {
	echo "This script must be run from CLI.\n";
	exit(1);
}

$root          = dirname(__DIR__);
$include_tests = in_array('--include-tests', $argv, true);
$limit         = 500;

$issues = array();
$iter   = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

foreach ( $iter as $file ) {
	if ( ! $file->isFile() ) {
		continue;
	}

	$path = $file->getPathname();
	if ( substr($path, -4) !== '.php' ) {
		continue;
	}

	$normalized = str_replace('\\', '/', $path);
	if ( false !== strpos($normalized, '/vendor/') || false !== strpos($normalized, '/node_modules/') || false !== strpos($normalized, '/dev-tools/') ) {
		continue;
	}
	if ( false !== strpos($normalized, '/includes/views/reports/') ) {
		continue;
	}
	if ( ! $include_tests && false !== strpos($normalized, '/tests/') ) {
		continue;
	}

	$code = @file_get_contents($path);
	if ( false === $code ) {
		continue;
	}

	$tokens = token_get_all($code);
	$rel    = ltrim(str_replace(str_replace('\\', '/', $root), '', $normalized), '/');

	$last_docblock = null;
	$line          = 1;

	for ( $i = 0; $i < count($tokens); $i++ ) {
		$token = $tokens[$i];

		if ( is_array($token) ) {
			$line = $token[2];
			if ( T_DOC_COMMENT === $token[0] ) {
				$last_docblock = $token[1];
				continue;
			}

			if ( T_CLASS === $token[0] ) {
				$prev_non_whitespace = null;
				for ( $j = $i - 1; $j >= 0; $j-- ) {
					$prev = $tokens[$j];
					if ( is_array( $prev ) && T_WHITESPACE === $prev[0] ) {
						continue;
					}
					if ( is_string( $prev ) && '' === trim( $prev ) ) {
						continue;
					}
					$prev_non_whitespace = $prev;
					break;
				}

				if ( is_array( $prev_non_whitespace ) && T_DOUBLE_COLON === $prev_non_whitespace[0] ) {
					continue;
				}

				if ( null === $last_docblock ) {
					$issues[] = sprintf('%s:%d Missing class docblock', $rel, $line);
				}
				$last_docblock = null;
				continue;
			}

			if ( T_FUNCTION === $token[0] ) {
				$is_public = false;
				for ( $j = $i - 1; $j >= 0; $j-- ) {
					$prev = $tokens[$j];
					if ( is_string($prev) ) {
						if ( trim($prev) === '' ) {
							continue;
						}
						if ( ';' === $prev || '{' === $prev || '}' === $prev ) {
							break;
						}
						continue;
					}

					if ( in_array($prev[0], array(T_WHITESPACE, T_STATIC, T_ABSTRACT, T_FINAL), true) ) {
						continue;
					}

					if ( T_PUBLIC === $prev[0] ) {
						$is_public = true;
					}
					break;
				}

				if ( $is_public && null === $last_docblock ) {
					$issues[] = sprintf('%s:%d Missing public method docblock', $rel, $line);
				}
				$last_docblock = null;
				continue;
			}
		}
	}

	if ( count($issues) >= $limit ) {
		break;
	}
}

echo sprintf("Inline docs audit (%s tests):\n", $include_tests ? 'with' : 'without');
echo sprintf("Issues found: %d\n\n", count($issues));

foreach ( $issues as $issue ) {
	echo $issue . "\n";
}
