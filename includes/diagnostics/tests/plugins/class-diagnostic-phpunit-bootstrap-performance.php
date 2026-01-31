<?php
/**
 * Phpunit Bootstrap Performance Diagnostic
 *
 * Phpunit Bootstrap Performance issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1076.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Phpunit Bootstrap Performance Diagnostic Class
 *
 * @since 1.1076.0000
 */
class Diagnostic_PhpunitBootstrapPerformance extends Diagnostic_Base {

	protected static $slug = 'phpunit-bootstrap-performance';
	protected static $title = 'Phpunit Bootstrap Performance';
	protected static $description = 'Phpunit Bootstrap Performance issue detected';
	protected static $family = 'performance';

	public static function check() {
		// Check for PHPUnit test suite
		$has_phpunit = file_exists( ABSPATH . 'phpunit.xml' ) ||
		               file_exists( ABSPATH . 'phpunit.xml.dist' ) ||
		               file_exists( ABSPATH . 'tests/bootstrap.php' );

		if ( ! $has_phpunit ) {
			return null;
		}

		$issues = array();
		$bootstrap_file = ABSPATH . 'tests/bootstrap.php';

		if ( ! file_exists( $bootstrap_file ) ) {
			return null;
		}

		// Check 1: Bootstrap file size
		$file_size = filesize( $bootstrap_file );
		if ( $file_size > ( 50 * 1024 ) ) {
			$issues[] = sprintf( __( 'Large bootstrap (%s, slow tests)', 'wpshadow' ), size_format( $file_size ) );
		}

		// Check 2: Database setup
		$bootstrap_content = file_get_contents( $bootstrap_file );
		if ( strpos( $bootstrap_content, 'WP_TESTS_MULTISITE' ) !== false ) {
			$issues[] = __( 'Multisite tests (extra DB overhead)', 'wpshadow' );
		}

		// Check 3: Plugin activation
		$plugin_count = substr_count( $bootstrap_content, 'activate_plugin' );
		if ( $plugin_count > 10 ) {
			$issues[] = sprintf( __( '%d plugins activated (slow bootstrap)', 'wpshadow' ), $plugin_count );
		}

		// Check 4: Custom DB setup
		if ( strpos( $bootstrap_content, 'CREATE TABLE' ) !== false ) {
			$issues[] = __( 'Custom tables in bootstrap (slow setup)', 'wpshadow' );
		}

		// Check 5: External requests
		if ( strpos( $bootstrap_content, 'wp_remote_get' ) !== false || strpos( $bootstrap_content, 'curl_' ) !== false ) {
			$issues[] = __( 'External requests in bootstrap (network delays)', 'wpshadow' );
		}

		// Check 6: Cache setup
		if ( strpos( $bootstrap_content, 'wp_cache_init' ) === false ) {
			$issues[] = __( 'No cache initialization (slower tests)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 55;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 67;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 61;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'PHPUnit bootstrap has %d performance issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/phpunit-bootstrap-performance',
		);
	}
}
