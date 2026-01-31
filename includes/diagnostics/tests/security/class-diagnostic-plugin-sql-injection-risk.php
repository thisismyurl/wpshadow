<?php
/**
 * Plugin SQL Injection Risk Diagnostic
 *
 * Detects plugins with SQL injection vulnerabilities.
 *
 * @since   1.4031.1939
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_SQL_Injection_Risk Class
 *
 * Identifies plugins vulnerable to SQL injection.
 */
class Diagnostic_Plugin_SQL_Injection_Risk extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-sql-injection-risk';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin SQL Injection Risk';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins vulnerable to SQL injection attacks';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.4031.1939
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$sql_risks = array();

		// Get active plugins
		$active_plugins = get_option( 'active_plugins', array() );
		$plugins_dir    = WP_PLUGIN_DIR;

		foreach ( $active_plugins as $plugin ) {
			$plugin_file = $plugins_dir . '/' . $plugin;

			if ( ! file_exists( $plugin_file ) ) {
				continue;
			}

			$content = file_get_contents( $plugin_file );

			// Check for direct query construction with variables
			if ( preg_match( '/\$wpdb\->(?:query|get_results?)\s*\(\s*["\'].*\$(?:_GET|_POST|_REQUEST)/', $content ) ) {
				$sql_risks[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Constructs SQL queries with $_GET/$_POST without $wpdb->prepare().', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for sprintf with database variables
			if ( preg_match( '/sprintf\s*\(\s*["\'].*SELECT.*["\'].*\$(?:_GET|_POST|_REQUEST)/', $content ) ) {
				$sql_risks[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Uses sprintf() for SQL queries with user input.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for string concatenation in queries
			if ( preg_match( '/\$wpdb\->query\s*\(\s*["\'].*\s*\.\s*\$[a-zA-Z_]/', $content ) ) {
				$sql_risks[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Concatenates variables into SQL queries.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for WHERE IN with variables
			if ( preg_match( '/WHERE\s+.+\s+IN\s*\(\s*["\'].*\$[a-zA-Z_]/', $content ) ) {
				$sql_risks[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Uses variables in WHERE IN clauses without prepare().', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}
		}

		if ( ! empty( $sql_risks ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: risk count, %s: details */
					__( '%d SQL injection risks detected: %s', 'wpshadow' ),
					count( $sql_risks ),
					implode( ' | ', array_slice( $sql_risks, 0, 3 ) )
				),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'details'      => array(
					'sql_risks' => $sql_risks,
				),
				'kb_link'      => 'https://wpshadow.com/kb/sql-injection-prevention',
			);
		}

		return null;
	}
}
