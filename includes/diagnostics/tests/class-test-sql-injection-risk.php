<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: SQL Injection Risk
 *
 * Detects potential SQL injection vulnerabilities in database queries.
 * SQL injection allows attackers to execute malicious SQL commands.
 *
 * @since 1.2.0
 */
class Test_Sql_Injection_Risk extends Diagnostic_Base {


	/**
	 * Check for SQL injection risks
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		$risks = self::scan_sql_risks();

		if ( empty( $risks ) ) {
			return null;
		}

		$threat = min( 90, count( $risks ) * 15 );

		return array(
			'threat_level'  => $threat,
			'threat_color'  => 'red',
			'passed'        => false,
			'issue'         => sprintf(
				'Found %d potential SQL injection risks',
				count( $risks )
			),
			'metadata'      => array(
				'risk_count' => count( $risks ),
				'risks'      => array_slice( $risks, 0, 5 ),
			),
			'kb_link'       => 'https://wpshadow.com/kb/sql-injection-prevention/',
			'training_link' => 'https://wpshadow.com/training/wordpress-database-security/',
		);
	}

	/**
	 * Guardian Sub-Test: Direct SQL query scan
	 *
	 * @return array Test result
	 */
	public static function test_direct_sql_queries(): array {
		$risks = self::scan_sql_risks();

		return array(
			'test_name'   => 'Direct SQL Query Scan',
			'risk_count'  => count( $risks ),
			'risks'       => $risks,
			'passed'      => empty( $risks ),
			'description' => empty( $risks ) ? 'No SQL injection risks detected' : sprintf( '%d potential SQL risks found', count( $risks ) ),
		);
	}

	/**
	 * Guardian Sub-Test: WordPress API usage
	 *
	 * @return array Test result
	 */
	public static function test_wordpress_api_usage(): array {
		global $wpdb;

		// Check if wpdb->prepare is being used correctly
		$active_plugins = get_plugins();

		$has_sql_concerns = false;
		foreach ( $active_plugins as $plugin_file => $plugin_data ) {
			$plugin_path = WP_PLUGIN_DIR . '/' . dirname( $plugin_file );
			$php_files   = glob( $plugin_path . '/**/*.php', GLOB_RECURSIVE );

			if ( ! $php_files ) {
				continue;
			}

			foreach ( array_slice( $php_files, 0, 3 ) as $file ) {
				$content = file_get_contents( $file );

				// Look for $wpdb->query without prepare
				if ( preg_match( '/\$wpdb->query\s*\(\s*["\']SELECT/', $content ) ) {
					$has_sql_concerns = true;
					break 2;
				}
			}
		}

		return array(
			'test_name'        => 'WordPress API Usage',
			'has_sql_concerns' => $has_sql_concerns,
			'passed'           => ! $has_sql_concerns,
			'description'      => $has_sql_concerns ? 'Found $wpdb->query() used without wpdb->prepare()' : 'WordPress API appears to be used correctly',
		);
	}

	/**
	 * Guardian Sub-Test: Query escaping
	 *
	 * @return array Test result
	 */
	public static function test_query_escaping(): array {
		global $wpdb;

		$escaping_issues = array();

		$active_plugins = get_plugins();
		foreach ( $active_plugins as $plugin_file => $plugin_data ) {
			$plugin_path = WP_PLUGIN_DIR . '/' . dirname( $plugin_file );
			$php_files   = glob( $plugin_path . '/**/*.php', GLOB_RECURSIVE );

			if ( ! $php_files ) {
				continue;
			}

			foreach ( array_slice( $php_files, 0, 2 ) as $file ) {
				$content = file_get_contents( $file );

				// Look for concatenated queries
				if ( preg_match( '/\$wpdb->(query|get_results|get_var)\s*\(\s*["\'].*\.\s*\$/', $content ) ) {
					$escaping_issues[] = basename( $file );
				}
			}
		}

		return array(
			'test_name'       => 'Query Escaping',
			'escaping_issues' => $escaping_issues,
			'passed'          => empty( $escaping_issues ),
			'description'     => empty( $escaping_issues ) ? 'Queries appear to be properly escaped' : sprintf( '%d files with potential escaping issues', count( $escaping_issues ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Plugin security rating
	 *
	 * @return array Test result
	 */
	public static function test_plugin_security_rating(): array {
		$active_plugins = get_plugins();

		// Count plugins with security concerns in names/descriptions
		$security_concerns = 0;
		foreach ( $active_plugins as $plugin_file => $plugin_data ) {
			$description = strtolower( $plugin_data['Description'] ?? '' );

			if (
				strpos( $description, 'wordpress.com' ) === false &&
				strpos( $description, 'unknown developer' ) !== false
			) {
				++$security_concerns;
			}
		}

		return array(
			'test_name'         => 'Plugin Security Rating',
			'plugin_count'      => count( $active_plugins ),
			'security_concerns' => $security_concerns,
			'passed'            => $security_concerns === 0,
			'description'       => $security_concerns === 0 ? 'All active plugins from known sources' : sprintf( '%d plugins from unknown sources', $security_concerns ),
		);
	}

	/**
	 * Scan for SQL injection risks
	 *
	 * @return array List of risks
	 */
	private static function scan_sql_risks(): array {
		$risks = array();

		$active_plugins = get_plugins();
		foreach ( $active_plugins as $plugin_file => $plugin_data ) {
			$plugin_path = WP_PLUGIN_DIR . '/' . dirname( $plugin_file );
			$php_files   = glob( $plugin_path . '/**/*.php', GLOB_RECURSIVE );

			if ( ! $php_files ) {
				continue;
			}

			foreach ( array_slice( $php_files, 0, 5 ) as $file ) {
				$content = file_get_contents( $file );

				// Check for direct SQL concatenation
				if ( preg_match( '/\$wpdb->(query|get_results|get_var)\s*\(\s*["\']SELECT.*["\]\s*\.\s*\$/', $content ) ) {
					$risks[] = array(
						'type'   => 'Unescaped SQL concatenation',
						'plugin' => $plugin_file,
						'file'   => $file,
					);
				}
			}
		}

		return array_slice( $risks, 0, 10 );
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'SQL Injection Risk';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Detects potential SQL injection vulnerabilities in active plugins';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'Security';
	}
}
