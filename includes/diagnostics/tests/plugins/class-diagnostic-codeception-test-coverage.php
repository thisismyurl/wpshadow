<?php
/**
 * Codeception Test Coverage Diagnostic
 *
 * Codeception Test Coverage issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1072.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Codeception Test Coverage Diagnostic Class
 *
 * @since 1.1072.0000
 */
class Diagnostic_CodeceptionTestCoverage extends Diagnostic_Base {

	protected static $slug = 'codeception-test-coverage';
	protected static $title = 'Codeception Test Coverage';
	protected static $description = 'Codeception Test Coverage issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Codeception in plugin/theme directory
		$codeception_yml = WP_CONTENT_DIR . '/codeception.yml';
		$has_codeception = file_exists( $codeception_yml ) ||
		                   file_exists( ABSPATH . 'codeception.yml' ) ||
		                   class_exists( 'Codeception\Test\Unit' );
		
		if ( ! $has_codeception ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Coverage enabled in config
		$coverage_enabled = get_option( 'codeception_coverage_enabled', false );
		if ( ! $coverage_enabled ) {
			$issues[] = __( 'Code coverage not enabled (no quality metrics)', 'wpshadow' );
		}
		
		// Check 2: Test suites configured
		$test_dir = WP_CONTENT_DIR . '/tests/';
		if ( is_dir( $test_dir ) ) {
			$suites = glob( $test_dir . '*', GLOB_ONLYDIR );
			if ( count( $suites ) === 0 ) {
				$issues[] = __( 'No test suites configured', 'wpshadow' );
			}
		}
		
		// Check 3: Acceptance tests
		$acceptance_dir = $test_dir . 'acceptance/';
		if ( ! is_dir( $acceptance_dir ) || count( glob( $acceptance_dir . '*.php' ) ) === 0 ) {
			$issues[] = __( 'No acceptance tests (no end-to-end coverage)', 'wpshadow' );
		}
		
		// Check 4: Xdebug or PHPDBG available
		$has_debugger = extension_loaded( 'xdebug' ) || PHP_SAPI === 'phpdbg';
		if ( ! $has_debugger ) {
			$issues[] = __( 'No Xdebug/PHPDBG (coverage generation unavailable)', 'wpshadow' );
		}
		
		// Check 5: CI integration
		$ci_files = array(
			ABSPATH . '.github/workflows/',
			ABSPATH . '.gitlab-ci.yml',
			ABSPATH . '.travis.yml',
		);
		
		$has_ci = false;
		foreach ( $ci_files as $file ) {
			if ( file_exists( $file ) || is_dir( $file ) ) {
				$has_ci = true;
				break;
			}
		}
		
		if ( ! $has_ci ) {
			$issues[] = __( 'No CI integration configured (manual testing only)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of Codeception issues */
				__( 'Codeception test setup has %d issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/codeception-test-coverage',
		);
	}
}
