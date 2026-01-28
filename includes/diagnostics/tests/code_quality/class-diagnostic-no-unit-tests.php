<?php
/**
 * No Unit Tests Diagnostic
 *
 * Checks if theme/plugin lacks unit tests for critical functions.
 * Automated testing prevents regressions and improves code quality.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1810
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_No_Unit_Tests Class
 *
 * Detects absence of unit tests in theme/plugin codebase.
 *
 * @since 1.6028.1810
 */
class Diagnostic_No_Unit_Tests extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-unit-tests';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Unit Tests for Critical Functions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if theme/plugin has automated test coverage';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'code_quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1810
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$analysis = self::analyze_test_coverage();

		// Don't flag simple themes/plugins.
		if ( ! $analysis['should_have_tests'] ) {
			return null;
		}

		// Has adequate test coverage.
		if ( $analysis['has_tests'] ) {
			return null;
		}

		// Determine severity based on complexity.
		if ( $analysis['file_count'] > 50 ) {
			$severity     = 'medium';
			$threat_level = 55;
		} elseif ( $analysis['file_count'] > 20 ) {
			$severity     = 'low';
			$threat_level = 40;
		} else {
			$severity     = 'low';
			$threat_level = 30;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: file count, 2: type (theme/plugin) */
				__( '%1$d PHP files in %2$s but no unit tests found', 'wpshadow' ),
				$analysis['file_count'],
				$analysis['type']
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/unit-tests',
			'family'      => self::$family,
			'meta'        => array(
				'file_count'        => $analysis['file_count'],
				'type'              => $analysis['type'],
				'recommended'       => __( 'Add PHPUnit tests for critical functions', 'wpshadow' ),
				'impact_level'      => 'medium',
				'immediate_actions' => array(
					__( 'Install PHPUnit test framework', 'wpshadow' ),
					__( 'Create tests/ directory', 'wpshadow' ),
					__( 'Write tests for critical functions', 'wpshadow' ),
					__( 'Set up CI/CD to run tests', 'wpshadow' ),
				),
			),
			'details'     => array(
				'why_important' => __( 'Unit tests catch bugs before users do. Automated testing prevents regressions, enables confident refactoring, documents expected behavior, and improves code quality. Without tests, every change risks breaking existing functionality. Professional plugins/themes have >70% code coverage. Tests are essential for maintainability.', 'wpshadow' ),
				'user_impact'   => array(
					__( 'Frequent Bugs: Changes break existing features', 'wpshadow' ),
					__( 'No Confidence: Fear of touching old code', 'wpshadow' ),
					__( 'Slow Development: Manual testing takes time', 'wpshadow' ),
					__( 'Poor Quality: No verification of correctness', 'wpshadow' ),
					__( 'Hard to Refactor: Can\'t verify nothing broke', 'wpshadow' ),
				),
				'analysis'      => $analysis,
				'what_to_test' => array(
					'critical'   => __( 'Data validation, sanitization, security functions', 'wpshadow' ),
					'high'       => __( 'Business logic, calculations, transformations', 'wpshadow' ),
					'medium'     => __( 'Helper functions, utilities, formatting', 'wpshadow' ),
					'low'        => __( 'Simple getters/setters, display logic', 'wpshadow' ),
				),
				'solution_options' => array(
					'free'     => array(
						'label'       => __( 'WordPress PHPUnit Setup', 'wpshadow' ),
						'description' => __( 'Use official WordPress testing framework', 'wpshadow' ),
						'steps'       => array(
							__( 'Install WP Test Suite: composer require --dev phpunit/phpunit wp-phpunit/wp-phpunit', 'wpshadow' ),
							__( 'Create tests/ directory in project root', 'wpshadow' ),
							__( 'Create phpunit.xml configuration file', 'wpshadow' ),
							__( 'Write first test: tests/test-sample.php', 'wpshadow' ),
							__( 'Run tests: vendor/bin/phpunit', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'label'       => __( 'Test Critical Functions', 'wpshadow' ),
						'description' => __( 'Focus on high-impact code first', 'wpshadow' ),
						'steps'       => array(
							__( 'Identify critical functions (data processing, security)', 'wpshadow' ),
							__( 'Write test for each critical function', 'wpshadow' ),
							__( 'Test edge cases and error conditions', 'wpshadow' ),
							__( 'Aim for >80% coverage of critical code', 'wpshadow' ),
							__( 'Run tests before each release', 'wpshadow' ),
						),
					),
					'advanced' => array(
						'label'       => __( 'Full CI/CD Integration', 'wpshadow' ),
						'description' => __( 'Automated testing on every commit', 'wpshadow' ),
						'steps'       => array(
							__( 'Set up GitHub Actions or GitLab CI', 'wpshadow' ),
							__( 'Create .github/workflows/tests.yml', 'wpshadow' ),
							__( 'Run tests on multiple PHP/WordPress versions', 'wpshadow' ),
							__( 'Block merges if tests fail', 'wpshadow' ),
							__( 'Generate code coverage reports', 'wpshadow' ),
						),
					),
				),
				'best_practices' => array(
					__( 'Test one thing per test method', 'wpshadow' ),
					__( 'Use descriptive test names: test_sanitize_rejects_scripts()', 'wpshadow' ),
					__( 'Follow AAA pattern: Arrange, Act, Assert', 'wpshadow' ),
					__( 'Test edge cases and error conditions', 'wpshadow' ),
					__( 'Keep tests fast (<100ms each)', 'wpshadow' ),
				),
				'testing_steps' => array(
					'verification' => array(
						__( 'Check for tests/ directory in project', 'wpshadow' ),
						__( 'Look for phpunit.xml configuration', 'wpshadow' ),
						__( 'Run: composer test or vendor/bin/phpunit', 'wpshadow' ),
						__( 'Verify tests pass', 'wpshadow' ),
					),
					'expected_result' => __( 'Automated test suite with >70% coverage of critical code', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Analyze test coverage setup.
	 *
	 * @since  1.6028.1810
	 * @return array Analysis results.
	 */
	private static function analyze_test_coverage() {
		$result = array(
			'has_tests'         => false,
			'should_have_tests' => false,
			'type'              => 'theme',
			'file_count'        => 0,
			'test_directory'    => '',
			'test_framework'    => '',
		);

		// Check theme.
		$theme_dir = get_template_directory();
		$theme_file_count = self::count_php_files( $theme_dir );
		$theme_has_tests = self::has_test_setup( $theme_dir );

		$result['file_count'] = $theme_file_count;
		$result['type'] = 'theme';

		// Check if active plugin has tests.
		$active_plugins = get_option( 'active_plugins', array() );
		foreach ( array_slice( $active_plugins, 0, 5 ) as $plugin ) {
			$plugin_dir = WP_PLUGIN_DIR . '/' . dirname( $plugin );
			if ( is_dir( $plugin_dir ) ) {
				$plugin_file_count = self::count_php_files( $plugin_dir );
				$plugin_has_tests = self::has_test_setup( $plugin_dir );

				if ( $plugin_file_count > $theme_file_count ) {
					$result['file_count'] = $plugin_file_count;
					$result['type'] = 'plugin';
					$theme_has_tests = $plugin_has_tests; // Use plugin's test status.
				}

				if ( $plugin_has_tests ) {
					$result['has_tests'] = true;
					$result['test_directory'] = $plugin_dir . '/tests';
					break;
				}
			}
		}

		// If theme/plugin has tests.
		if ( $theme_has_tests ) {
			$result['has_tests'] = true;
			$result['test_directory'] = $theme_dir . '/tests';
		}

		// Determine if tests are expected based on complexity.
		if ( $result['file_count'] > 15 ) {
			$result['should_have_tests'] = true;
		}

		// Check for test framework.
		if ( $result['has_tests'] ) {
			$config_file = dirname( $result['test_directory'] ) . '/phpunit.xml';
			if ( file_exists( $config_file ) ) {
				$result['test_framework'] = 'PHPUnit';
			}
		}

		return $result;
	}

	/**
	 * Check if directory has test setup.
	 *
	 * @since  1.6028.1810
	 * @param  string $directory Directory to check.
	 * @return bool True if tests found.
	 */
	private static function has_test_setup( $directory ) {
		if ( ! is_dir( $directory ) ) {
			return false;
		}

		// Check for tests directory.
		$test_dirs = array(
			$directory . '/tests',
			$directory . '/test',
			$directory . '/Tests',
		);

		foreach ( $test_dirs as $test_dir ) {
			if ( is_dir( $test_dir ) ) {
				// Check if it has test files.
				$test_files = glob( $test_dir . '/*test*.php' );
				if ( ! empty( $test_files ) ) {
					return true;
				}
			}
		}

		// Check for phpunit.xml.
		if ( file_exists( $directory . '/phpunit.xml' ) || 
		     file_exists( $directory . '/phpunit.xml.dist' ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Count PHP files in directory.
	 *
	 * @since  1.6028.1810
	 * @param  string $directory Directory path.
	 * @return int File count.
	 */
	private static function count_php_files( $directory ) {
		if ( ! is_dir( $directory ) ) {
			return 0;
		}

		$count = 0;
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $directory, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() && $file->getExtension() === 'php' ) {
				// Skip vendor and tests.
				$path = $file->getPathname();
				if ( strpos( $path, '/vendor/' ) !== false ||
				     strpos( $path, '/tests/' ) !== false ||
				     strpos( $path, '/test/' ) !== false ) {
					continue;
				}
				$count++;
			}

			// Limit scan.
			if ( $count >= 100 ) {
				break;
			}
		}

		return $count;
	}
}
