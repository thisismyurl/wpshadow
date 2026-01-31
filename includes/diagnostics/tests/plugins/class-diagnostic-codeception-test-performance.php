<?php
/**
 * Codeception Test Performance Diagnostic
 *
 * Codeception Test Performance issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1071.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Codeception Test Performance Diagnostic Class
 *
 * @since 1.1071.0000
 */
class Diagnostic_CodeceptionTestPerformance extends Diagnostic_Base {

	protected static $slug = 'codeception-test-performance';
	protected static $title = 'Codeception Test Performance';
	protected static $description = 'Codeception Test Performance issue detected';
	protected static $family = 'performance';

	public static function check() {
		// Check for Codeception in development environment
		if ( wp_get_environment_type() === 'production' ) {
			return null; // Don't run on production
		}
		
		$codeception_yml = ABSPATH . 'codeception.yml';
		if ( ! file_exists( $codeception_yml ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Test output directory size
		$output_dir = ABSPATH . 'tests/_output';
		if ( file_exists( $output_dir ) ) {
			$size = 0;
			$files = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $output_dir ) );
			foreach ( $files as $file ) {
				if ( $file->isFile() ) {
					$size += $file->getSize();
				}
			}
			
			if ( $size > 104857600 ) { // 100MB
				$issues[] = sprintf( __( 'Test output directory: %.2f MB (cleanup needed)', 'wpshadow' ), $size / 1048576 );
			}
		}
		
		// Check 2: Slow test detection
		$slow_tests = get_transient( 'codeception_slow_tests' );
		if ( is_array( $slow_tests ) && count( $slow_tests ) > 5 ) {
			$issues[] = sprintf( __( '%d slow tests detected (>30s each)', 'wpshadow' ), count( $slow_tests ) );
		}
		
		// Check 3: Parallel execution configuration
		$config_content = file_get_contents( $codeception_yml );
		if ( strpos( $config_content, 'parallel' ) === false ) {
			$issues[] = __( 'Parallel test execution not configured', 'wpshadow' );
		}
		
		// Check 4: Memory limit for tests
		$memory_limit = ini_get( 'memory_limit' );
		$memory_bytes = wp_convert_hr_to_bytes( $memory_limit );
		if ( $memory_bytes < 268435456 ) { // 256MB
			$issues[] = sprintf( __( 'PHP memory limit low for tests: %s', 'wpshadow' ), $memory_limit );
		}
		
		// Check 5: Database cleanup between tests
		if ( strpos( $config_content, 'cleanup' ) === false && strpos( $config_content, 'Db' ) !== false ) {
			$issues[] = __( 'Database module without cleanup configuration', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 68;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 62;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of performance issues */
				__( 'Codeception test suite has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/codeception-test-performance',
		);
	}
}
