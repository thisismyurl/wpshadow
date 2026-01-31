<?php
/**
 * SQL Injection Vulnerabilities Diagnostic
 *
 * Scans plugin and theme code for unescaped database queries that could
 * allow SQL injection attacks.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_SQL_Injection_Vulnerabilities Class
 *
 * Detects potentially dangerous SQL query patterns in active plugins and themes.
 *
 * @since 1.2601.2148
 */
class Diagnostic_SQL_Injection_Vulnerabilities extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'sql-injection-vulnerabilities';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SQL Injection Vulnerability Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Scans plugin and theme code for unescaped database queries';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Dangerous SQL patterns to detect
	 *
	 * @var array
	 */
	const DANGEROUS_PATTERNS = array(
		'wpdb.*query\s*\(\s*"\s*SELECT.*\$' => 'Unescaped variable in $wpdb->query()',
		'wpdb.*query\s*\(\s*"\s*INSERT.*\$' => 'Unescaped variable in INSERT query',
		'wpdb.*query\s*\(\s*"\s*UPDATE.*\$' => 'Unescaped variable in UPDATE query',
		'wpdb.*query\s*\(\s*"\s*DELETE.*\$' => 'Unescaped variable in DELETE query',
		'\$_GET\[.*\].*wpdb' => 'Direct $_GET usage in database query',
		'\$_POST\[.*\].*wpdb' => 'Direct $_POST usage in database query',
		'\$_REQUEST\[.*\].*wpdb' => 'Direct $_REQUEST usage in database query',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if vulnerabilities detected, null otherwise.
	 */
	public static function check() {
		$vulnerabilities = self::scan_for_vulnerabilities();

		if ( empty( $vulnerabilities ) ) {
			return null;
		}

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: vulnerability count */
				__( 'Found %d potential SQL injection vulnerabilities in active code.', 'wpshadow' ),
				count( $vulnerabilities )
			),
			'severity'      => 'critical',
			'threat_level'  => 95,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/sql-injection-fix',
			'family'        => self::$family,
			'meta'          => array(
				'vulnerabilities_found' => count( $vulnerabilities ),
				'vulnerable_files'      => array_slice( array_unique( array_column( $vulnerabilities, 'file' ) ), 0, 5 ),
				'pattern_types'         => array_unique( array_column( $vulnerabilities, 'pattern' ) ),
				'immediate_actions'     => array(
					__( 'CRITICAL: Contact plugin/theme author about vulnerability' ),
					__( 'Request security patch immediately' ),
					__( 'Consider temporarily disabling affected plugin' ),
					__( 'Monitor for any suspicious database activity' ),
				),
			),
			'details'       => array(
				'threat_level'  => __( 'CRITICAL - SQL injection allows attackers to read/modify/delete database contents' ),
				'attack_scenario' => __( 'Attacker crafts malicious input that executes arbitrary SQL, stealing user data, passwords, or modifying post content.' ),
				'vulnerable_code' => $vulnerabilities,
				'remediation'   => array(
					'Correct Method' => array(
						'$wpdb->prepare()' => __( 'Always use prepare() to safely handle variables in SQL' ),
						'Example' => '$wpdb->query( $wpdb->prepare( "SELECT * FROM posts WHERE ID = %d", $id ) )',
						'Formats' => array(
							'%d' => 'Integer',
							'%f' => 'Float',
							'%s' => 'String (escaped)',
						),
					),
					'For Queries Without Variables' => __( 'Use $wpdb->query( \'SELECT * FROM posts\' ) - but rarely needed in WordPress' ),
				),
				'developer_guide' => array(
					__( 'Never trust user input' ),
					__( 'Always use $wpdb->prepare() with placeholders' ),
					__( 'Never concatenate variables into SQL strings' ),
					__( 'Use WordPress functions (get_posts, WP_Query) instead of raw SQL when possible' ),
					__( 'Escape all output with esc_html(), esc_attr(), etc.' ),
				),
			),
		);
	}

	/**
	 * Scan for SQL injection vulnerabilities.
	 *
	 * @since  1.2601.2148
	 * @return array Array of vulnerabilities found.
	 */
	private static function scan_for_vulnerabilities() {
		$vulnerabilities = array();

		// Scan active plugins
		$plugins_dir = WP_PLUGIN_DIR;
		$active_plugins = get_plugins();

		foreach ( $active_plugins as $plugin_file => $plugin_data ) {
			$plugin_path = $plugins_dir . '/' . dirname( $plugin_file );
			$found = self::scan_directory_for_sql( $plugin_path, 'plugin', $plugin_data['Name'] );
			$vulnerabilities = array_merge( $vulnerabilities, $found );
		}

		// Scan active theme
		$theme = wp_get_theme();
		$theme_path = $theme->get_theme_root() . '/' . $theme->get_stylesheet();
		$found = self::scan_directory_for_sql( $theme_path, 'theme', $theme->get( 'Name' ) );
		$vulnerabilities = array_merge( $vulnerabilities, $found );

		return array_slice( $vulnerabilities, 0, 10 ); // Limit to 10 for display
	}

	/**
	 * Scan directory for SQL injection patterns.
	 *
	 * @since  1.2601.2148
	 * @param  string $dir Directory path.
	 * @param  string $type 'plugin' or 'theme'.
	 * @param  string $name Plugin/theme name.
	 * @return array Found vulnerabilities.
	 */
	private static function scan_directory_for_sql( $dir, $type, $name ) {
		$vulnerabilities = array();
		$files = array();
		$depth = 0;

		// Get PHP files
		$iterator = new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS );
		$filter = new \RecursiveCallbackFilterIterator(
			$iterator,
			function( $file, $key, $iterator ) use ( &$depth ) {
				if ( $depth > 3 ) {
					return false;
				}
				if ( $file->isDir() ) {
					return true;
				}
				return $file->getExtension() === 'php';
			}
		);

		try {
			$files_iter = new \RecursiveIteratorIterator( $filter );
			foreach ( $files_iter as $file ) {
				$files[] = $file->getPathname();
			}
		} catch ( \Exception $e ) {
			return $vulnerabilities;
		}

		foreach ( array_slice( $files, 0, 20 ) as $file ) {
			$content = file_get_contents( $file );
			if ( ! $content ) {
				continue;
			}

			foreach ( self::DANGEROUS_PATTERNS as $pattern => $description ) {
				if ( preg_match( '/' . $pattern . '/i', $content ) ) {
					$vulnerabilities[] = array(
						'file'    => str_replace( WP_CONTENT_DIR, '', $file ),
						'type'    => $type,
						'source'  => $name,
						'pattern' => $description,
					);
					break; // Only report once per file
				}
			}
		}

		return $vulnerabilities;
	}
}
