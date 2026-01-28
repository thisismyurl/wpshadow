<?php
/**
 * Directory Listing Enabled Diagnostic
 *
 * Detects if directory listing is enabled, allowing attackers to browse
 * file structure and discover sensitive files.
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
 * Diagnostic_Directory_Listing_Enabled Class
 *
 * Tests for directory listing vulnerability on web-accessible directories.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Directory_Listing_Enabled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'directory-listing-enabled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Directory Listing Vulnerability Check';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects if directories are browsable, exposing file structure';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Directories to test for listing
	 *
	 * @var array
	 */
	const TEST_DIRECTORIES = array(
		'/wp-content/',
		'/wp-content/plugins/',
		'/wp-content/themes/',
		'/wp-content/uploads/',
		'/wp-admin/',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if directory listing detected, null otherwise.
	 */
	public static function check() {
		$exposed_dirs = self::test_directory_listing();

		if ( empty( $exposed_dirs ) ) {
			return null;
		}

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: directory count */
				__( 'Found %d directories with listing enabled, exposing file structure to attackers.', 'wpshadow' ),
				count( $exposed_dirs )
			),
			'severity'      => 'high',
			'threat_level'  => 70,
			'auto_fixable'  => true,
			'kb_link'       => 'https://wpshadow.com/kb/disable-directory-listing',
			'family'        => self::$family,
			'meta'          => array(
				'exposed_directories' => $exposed_dirs,
				'exposure_level'      => 'HIGH - File structure is visible to attackers',
				'immediate_action'    => __( 'Add .htaccess rule to disable directory listing' ),
				'affected_count'      => count( $exposed_dirs ),
			),
			'details'       => array(
				'issue'       => __( 'Directory listing allows browsers to see all files and folders, revealing plugin/theme versions, backup files, and potential vulnerabilities.' ),
				'attack_vector' => array(
					__( 'Attacker visits /wp-content/plugins/ and sees all plugins with versions' ),
					__( 'Attacker discovers backup files like backup.sql or database.dump' ),
					__( 'Attacker finds old file versions in /wp-content/uploads/old/' ),
					__( 'Attacker researches vulnerabilities for discovered plugins' ),
				),
				'quick_fix'   => array(
					'Option 1: Via .htaccess' => array(
						__( 'Add to /wp-content/.htaccess: Options -Indexes' ),
						__( 'Add to /wp-content/plugins/.htaccess: Options -Indexes' ),
						__( 'Add to /wp-content/themes/.htaccess: Options -Indexes' ),
					),
					'Option 2: Via PHP' => array(
						__( 'Add index.php to each directory (empty file)' ),
						__( 'Or configure web server (nginx: disable directory listing)' ),
					),
					'Option 3: Plugin' => array(
						__( 'Use Security plugin (Wordfence, Sucuri) to automate' ),
					),
				),
				'htaccess_rules' => array(
					'For All WordPress' => '<Options -Indexes>',
					'For Specific Dir' => 'Add <Options -Indexes> to .htaccess in that directory',
					'Verify' => 'Visit directory URL - should see 403 Forbidden or blank page, not file listing',
				),
			),
		);
	}

	/**
	 * Test directories for listing vulnerability.
	 *
	 * @since  1.2601.2148
	 * @return array Array of exposed directories.
	 */
	private static function test_directory_listing() {
		$exposed = array();

		foreach ( self::TEST_DIRECTORIES as $dir ) {
			$url = home_url() . $dir;
			$response = wp_remote_get( $url, array( 'sslverify' => false ) );

			if ( is_wp_error( $response ) ) {
				continue;
			}

			$code = wp_remote_retrieve_response_code( $response );
			$body = wp_remote_retrieve_body( $response );

			// 200 + HTML with hrefs/file listing = directory listing enabled
			if ( $code === 200 && self::is_directory_listing( $body ) ) {
				$exposed[] = $dir;
			}
		}

		return $exposed;
	}

	/**
	 * Check if response is a directory listing page.
	 *
	 * @since  1.2601.2148
	 * @param  string $html HTML content.
	 * @return bool True if appears to be directory listing.
	 */
	private static function is_directory_listing( $html ) {
		$html_lower = strtolower( $html );

		// Directory listings contain parent directory link and file links
		$indicators = array(
			'<a href="' => 1,
			'[to parent directory]' => 2,
			'index of ' => 2,
		);

		$score = 0;
		foreach ( $indicators as $indicator => $weight ) {
			if ( strpos( $html_lower, $indicator ) !== false ) {
				$score += $weight;
			}
		}

		return $score >= 2;
	}
}
