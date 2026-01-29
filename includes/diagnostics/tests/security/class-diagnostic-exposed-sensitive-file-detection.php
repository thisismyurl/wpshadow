<?php
/**
 * Exposed Sensitive File Detection Diagnostic
 *
 * Tests for publicly accessible files that should be blocked.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Exposed Sensitive File Detection Class
 *
 * Tests sensitive files.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Exposed_Sensitive_File_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'exposed-sensitive-file-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Exposed Sensitive File Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for publicly accessible files that should be blocked';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$file_check = self::check_sensitive_files();
		
		if ( ! empty( $file_check['exposed_files'] ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of exposed files */
					__( 'Found %d publicly accessible sensitive files (.git, .env, backups, logs)', 'wpshadow' ),
					count( $file_check['exposed_files'] )
				),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/exposed-sensitive-file-detection',
				'meta'         => array(
					'exposed_files' => $file_check['exposed_files'],
				),
			);
		}

		return null;
	}

	/**
	 * Check sensitive files.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_sensitive_files() {
		$check = array(
			'exposed_files' => array(),
		);

		$site_url = get_home_url();

		// Sensitive paths to test.
		$sensitive_paths = array(
			'.git/config',
			'.git/HEAD',
			'.env',
			'wp-config.php.bak',
			'backup.sql',
			'database.sql',
			'error_log',
			'debug.log',
		);

		foreach ( $sensitive_paths as $path ) {
			$test_url = trailingslashit( $site_url ) . $path;
			
			$response = wp_remote_head( $test_url, array( 'timeout' => 5 ) );
			
			if ( ! is_wp_error( $response ) ) {
				$status_code = wp_remote_retrieve_response_code( $response );
				
				// 200 means accessible.
				if ( 200 === $status_code ) {
					$check['exposed_files'][] = $path;
				}
			}
		}

		return $check;
	}
}
