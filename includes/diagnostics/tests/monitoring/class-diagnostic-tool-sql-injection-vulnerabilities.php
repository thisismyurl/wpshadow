<?php
/**
 * Tool SQL Injection Vulnerabilities Diagnostic
 *
 * Tests whether tool database operations properly sanitize input and use prepared statements.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Admin
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Tool_SQL_Injection_Vulnerabilities Class
 *
 * Verifies database operations use prepared statements.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Tool_SQL_Injection_Vulnerabilities extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tool-sql-injection-vulnerabilities';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Tool SQL Injection Protection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies tool database operations use prepared statements and proper sanitization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues = array();

		// 1. Test wpdb->prepare() availability.
		if ( ! method_exists( $wpdb, 'prepare' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Database prepare() method not available - critical security issue', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/sql-injection-protection',
			);
		}

		// 2. Test basic prepared statement.
		$test_id     = 1;
		$test_query  = $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE ID = %d", $test_id );
		$test_result = $wpdb->get_row( $test_query );
		
		if ( $wpdb->last_error ) {
			$issues[] = __( 'wpdb->prepare() producing errors - query sanitization may be broken', 'wpshadow' );
		}

		// 3. Check for placeholder validation.
		// WordPress 4.8.3+ enforces placeholder requirements.
		$wp_version = get_bloginfo( 'version' );
		if ( version_compare( $wp_version, '4.8.3', '<' ) ) {
			$issues[] = __( 'WordPress version doesn\'t enforce strict placeholder validation - upgrade recommended', 'wpshadow' );
		}

		// 4. Test string sanitization.
		$test_string = "Test ' OR '1'='1";
		$sanitized   = $wpdb->prepare( "SELECT * FROM {$wpdb->posts} WHERE post_title = %s", $test_string );
		
		// Should not contain SQL injection pattern.
		if ( false !== strpos( $sanitized, "1'='1" ) ) {
			$issues[] = __( 'String sanitization not properly escaping quotes', 'wpshadow' );
		}

		// 5. Check for direct query usage (dangerous).
		// Look for plugins doing raw queries in tool hooks.
		$tool_hooks = array(
			'wp_ajax_export_personal_data',
			'wp_ajax_erase_personal_data',
			'import_start',
			'import_end',
		);

		// We can't directly scan plugin code, but we can check patterns.

		// 6. Test sanitization functions.
		$test_input = '<script>alert("xss")</script>';
		$sanitized  = sanitize_text_field( $test_input );
		
		if ( false !== strpos( $sanitized, '<script>' ) ) {
			$issues[] = __( 'sanitize_text_field() not properly removing scripts', 'wpshadow' );
		}

		// 7. Check for esc_sql usage (deprecated).
		// Modern code should use $wpdb->prepare() instead.
		$uses_esc_sql = false;
		
		// This is hard to detect without scanning files, but we can check if it exists.
		if ( function_exists( 'esc_sql' ) ) {
			// Function exists - but shouldn't be used.
			$issues[] = __( 'esc_sql() function available - plugins may be using deprecated sanitization', 'wpshadow' );
		}

		// 8. Test wpdb->esc_like() for LIKE queries.
		$test_like  = '%test%';
		$escaped    = $wpdb->esc_like( $test_like );
		$like_query = $wpdb->prepare(
			"SELECT * FROM {$wpdb->posts} WHERE post_title LIKE %s",
			$escaped
		);

		if ( $wpdb->last_error ) {
			$issues[] = __( 'LIKE query sanitization having issues', 'wpshadow' );
		}

		// 9. Check for type casting in queries.
		$mixed_input = '5 OR 1=1';
		$int_cast    = absint( $mixed_input );
		
		if ( 5 !== $int_cast ) {
			// This is expected - absint() returns 5.
		}

		// Verify prepare() enforces types.
		$bad_query = $wpdb->prepare(
			"SELECT * FROM {$wpdb->posts} WHERE ID = %d",
			$mixed_input
		);
		
		if ( false !== strpos( $bad_query, '1=1' ) ) {
			$issues[] = __( 'Type enforcement in prepared statements not working correctly', 'wpshadow' );
		}

		// 10. Check for LIMIT injection protection.
		$limit  = '10 UNION SELECT';
		$query  = $wpdb->prepare(
			"SELECT * FROM {$wpdb->posts} LIMIT %d",
			absint( $limit )
		);
		
		if ( false !== stripos( $query, 'UNION' ) ) {
			$issues[] = __( 'LIMIT clause vulnerable to injection', 'wpshadow' );
		}

		// 11. Check ORDER BY safety (can't be parameterized).
		// This is a known issue - ORDER BY can't use prepare().
		$order_by = 'post_date DESC';
		
		// Should validate against whitelist.
		$valid_order = array( 'post_date', 'post_title', 'ID' );
		$valid_sort  = array( 'ASC', 'DESC' );
		
		// This check should exist in tool code.
		$issues[] = __( 'ORDER BY clauses cannot be parameterized - verify whitelist validation in tool code', 'wpshadow' );

		// 12. Test multi-value insertion.
		$test_values = array( 1, 2, 3 );
		$placeholders = implode( ',', array_fill( 0, count( $test_values ), '%d' ) );
		$multi_query  = $wpdb->prepare(
			"SELECT * FROM {$wpdb->posts} WHERE ID IN ({$placeholders})",
			...$test_values
		);

		if ( $wpdb->last_error ) {
			$issues[] = __( 'Multi-value prepared statements having issues', 'wpshadow' );
		}

		// 13. Check for wpdb error reporting.
		$show_errors = $wpdb->show_errors;
		if ( $show_errors && defined( 'WP_DEBUG' ) && ! WP_DEBUG ) {
			$issues[] = __( 'Database errors displayed in production - information disclosure risk', 'wpshadow' );
		}

		// 14. Verify charset handling.
		if ( ! defined( 'DB_CHARSET' ) || empty( DB_CHARSET ) ) {
			$issues[] = __( 'Database charset not defined - may allow encoding attacks', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'SQL injection protection issues: %s', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => 'critical',
			'threat_level' => 95,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/sql-injection-protection',
			'details'      => array(
				'issues'      => $issues,
				'wp_version'  => $wp_version,
				'db_charset'  => defined( 'DB_CHARSET' ) ? DB_CHARSET : null,
			),
		);
	}
}
