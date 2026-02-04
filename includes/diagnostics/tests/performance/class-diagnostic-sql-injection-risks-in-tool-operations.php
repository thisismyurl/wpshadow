<?php
/**
 * SQL Injection Risks in Tool Operations Diagnostic
 *
 * Tests for SQL injection vulnerability prevention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SQL Injection Risks in Tool Operations Diagnostic Class
 *
 * Tests for SQL injection vulnerability prevention in tool operations.
 *
 * @since 1.6033.0000
 */
class Diagnostic_SQL_Injection_Risks_In_Tool_Operations extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'sql-injection-risks-in-tool-operations';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SQL Injection Risks in Tool Operations';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for SQL injection vulnerability prevention';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// Check if wpdb prepare is available and working.
		$test_query = $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID = %d", 1 );
		if ( empty( $test_query ) ) {
			$issues[] = __( 'wpdb->prepare() not functioning properly', 'wpshadow' );
		}

		// Check for direct query execution patterns (code inspection).
		// This checks common plugin files for potential SQL injection patterns.
		$plugin_files = glob( WP_PLUGIN_DIR . '/*/includes/**.php' );
		$risky_patterns = 0;

		foreach ( $plugin_files as $file ) {
			if ( is_file( $file ) ) {
				$content = file_get_contents( $file );
				if ( preg_match( '/wpdb->query\s*\(\s*"SELECT.*\$/', $content ) ) {
					$risky_patterns++;
				}
			}
		}

		if ( $risky_patterns > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of files with risky patterns */
				__( '%d files have potential SQL injection patterns (direct variable interpolation)', 'wpshadow' ),
				$risky_patterns
			);
		}

		// Check if custom tables use proper escaping.
		$custom_tables = $wpdb->get_results( "SHOW TABLES LIKE '{$wpdb->prefix}%'" );

		if ( ! empty( $custom_tables ) ) {
			foreach ( $custom_tables as $table_obj ) {
				$table_name = current( (array) $table_obj );
				if ( strpos( $table_name, 'wpshadow' ) !== false ) {
					// This is a WPShadow table - verify it has proper structure.
					$issues[] = sprintf(
						/* translators: %s: table name */
						__( 'Custom table %s exists - verify queries use $wpdb->prepare()', 'wpshadow' ),
						$table_name
					);
				}
			}
		}

		// Check WordPress security nonce implementation.
		$has_nonce_check = function_exists( 'wp_verify_nonce' );
		if ( ! $has_nonce_check ) {
			$issues[] = __( 'wp_verify_nonce() not available', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/sql-injection-risks-in-tool-operations',
			);
		}

		return null;
	}
}
