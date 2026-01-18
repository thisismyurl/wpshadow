<?php
/**
 * Tests for code quality and standards compliance
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Test code quality standards
 */
class CodeQualityTest extends TestCase {

	/**
	 * Test no PHP files have syntax errors
	 */
	public function test_no_syntax_errors(): void {
		$base_dir   = dirname( dirname( dirname( __FILE__ ) ) );
		$php_files  = $this->get_php_files( $base_dir . '/includes' );
		$php_files  = array_merge( $php_files, $this->get_php_files( $base_dir . '/free/includes' ) );
		$php_files  = array_merge( $php_files, $this->get_php_files( $base_dir . '/pro/includes' ) );

		$errors = array();

		foreach ( $php_files as $file ) {
			$output = array();
			$result = 0;
			exec( "php -l " . escapeshellarg( $file ) . " 2>&1", $output, $result );

			if ( 0 !== $result ) {
				$errors[] = $file . ': ' . implode( "\n", $output );
			}
		}

		$this->assertEmpty( $errors, "PHP syntax errors found:\n" . implode( "\n", $errors ) );
	}

	/**
	 * Test namespace usage
	 */
	public function test_namespace_usage(): void {
		$base_dir  = dirname( dirname( dirname( __FILE__ ) ) );
		$php_files = $this->get_php_files( $base_dir . '/includes' );

		foreach ( $php_files as $file ) {
			$content = file_get_contents( $file );

			// Skip test files and WordPress plugins
			if ( strpos( $content, 'Tests' ) !== false || strpos( $content, 'wp-content' ) !== false ) {
				continue;
			}

			// Each file should have either a namespace declaration or be a helper file
			if ( ! preg_match( '/^\s*namespace\s+/m', $content ) ) {
				$this->assertTrue(
					preg_match( '/^<\?php.*?(function\s+|^)?$/im', $content ),
					"File $file should have namespace or be a helper with functions: $file"
				);
			}
		}
	}

	/**
	 * Test class naming convention
	 */
	public function test_class_naming_convention(): void {
		$base_dir  = dirname( dirname( dirname( __FILE__ ) ) );
		$php_files = $this->get_php_files( $base_dir . '/includes' );

		$class_errors = array();

		foreach ( $php_files as $file ) {
			$content = file_get_contents( $file );

			// Check for class declarations
			if ( preg_match( '/class\s+(\w+)/', $content, $matches ) ) {
				$class_name = $matches[1];

				// Class names should start with WPSHADOW_
				if ( strpos( $class_name, 'WPSHADOW_' ) !== 0 && strpos( $class_name, 'Stub' ) === false ) {
					$class_errors[] = $file . ": Class '$class_name' should start with WPSHADOW_";
				}
			}
		}

		$this->assertEmpty( $class_errors, "Class naming violations:\n" . implode( "\n", $class_errors ) );
	}

	/**
	 * Test no use of deprecated functions
	 */
	public function test_no_deprecated_functions(): void {
		$base_dir       = dirname( dirname( dirname( __FILE__ ) ) );
		$php_files      = $this->get_php_files( $base_dir . '/includes' );
		$deprecated_fns = array(
			'extract\s*\(',
			'ereg\s*\(',
			'eregi\s*\(',
		);

		$violations = array();

		foreach ( $php_files as $file ) {
			$content = file_get_contents( $file );

			foreach ( $deprecated_fns as $fn ) {
				if ( preg_match( "/$fn/i", $content ) ) {
					$violations[] = $file . ": Uses deprecated function matching: $fn";
				}
			}
		}

		$this->assertEmpty( $violations, "Deprecated function usage:\n" . implode( "\n", $violations ) );
	}

	/**
	 * Test declare(strict_types=1) usage
	 */
	public function test_strict_types_declaration(): void {
		$base_dir  = dirname( dirname( dirname( __FILE__ ) ) );
		$php_files = $this->get_php_files( $base_dir . '/includes' );

		$missing_strict = array();

		foreach ( $php_files as $file ) {
			$content = file_get_contents( $file );

			// Skip test files
			if ( strpos( $file, '/tests/' ) !== false ) {
				continue;
			}

			// Check if file has declare(strict_types=1) in first 100 chars
			if ( ! preg_match( '/declare\s*\(\s*strict_types\s*=\s*1\s*\)/i', substr( $content, 0, 500 ) ) ) {
				// It's okay if it's a test file or commented out reason
				if ( strpos( $file, 'test' ) === false ) {
					$missing_strict[] = $file;
				}
			}
		}

		// Most files should have strict types (allow some exceptions)
		$strict_count = count( $this->get_php_files( $base_dir . '/includes' ) ) - count( $missing_strict );
		$total_count  = count( $this->get_php_files( $base_dir . '/includes' ) );

		$this->assertGreaterThan(
			$total_count * 0.8, // At least 80% should have strict types
			$strict_count,
			"Low strict_types adoption. Missing in: " . implode( ', ', array_slice( $missing_strict, 0, 5 ) )
		);
	}

	/**
	 * Helper: Get all PHP files in directory
	 *
	 * @param string $dir Directory to scan.
	 * @return array Array of PHP file paths.
	 */
	private function get_php_files( string $dir ): array {
		$files = array();

		if ( ! is_dir( $dir ) ) {
			return $files;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() && $file->getExtension() === 'php' ) {
				$files[] = $file->getRealPath();
			}
		}

		return $files;
	}
}
