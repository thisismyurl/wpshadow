<?php
/**
 * wp-content Upload Path Exposure Analysis Diagnostic
 *
 * Tests if directory listings expose uploaded file structure.
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
 * wp-content Upload Path Exposure Analysis Class
 *
 * Tests upload directory exposure.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Wp_Content_Upload_Path_Exposure_Analysis extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wp-content-upload-path-exposure-analysis';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'wp-content Upload Path Exposure Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if directory listings expose uploaded file structure';

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
		$exposure_test = self::test_directory_exposure();
		
		if ( $exposure_test['directory_listing_enabled'] ) {
			$issues = array();
			
			$issues[] = __( 'Directory listing enabled in uploads directory', 'wpshadow' );

			if ( $exposure_test['sensitive_files_found'] > 0 ) {
				$issues[] = sprintf(
					/* translators: %d: number of sensitive files */
					__( '%d potentially sensitive files exposed (.sql, .zip, .bak)', 'wpshadow' ),
					$exposure_test['sensitive_files_found']
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wp-content-upload-path-exposure-analysis',
				'meta'         => array(
					'directory_listing_enabled' => $exposure_test['directory_listing_enabled'],
					'htaccess_exists'           => $exposure_test['htaccess_exists'],
					'sensitive_files_found'     => $exposure_test['sensitive_files_found'],
					'tested_url'                => $exposure_test['tested_url'],
				),
			);
		}

		return null;
	}

	/**
	 * Test directory listing exposure.
	 *
	 * @since  1.26028.1905
	 * @return array Test results.
	 */
	private static function test_directory_exposure() {
		$result = array(
			'directory_listing_enabled' => false,
			'htaccess_exists'           => false,
			'sensitive_files_found'     => 0,
			'tested_url'                => '',
		);

		$upload_dir = wp_upload_dir();
		$uploads_url = $upload_dir['baseurl'];
		$result['tested_url'] = $uploads_url;

		// Check for .htaccess with Options -Indexes.
		$htaccess_path = $upload_dir['basedir'] . '/.htaccess';
		$result['htaccess_exists'] = file_exists( $htaccess_path );

		if ( $result['htaccess_exists'] ) {
			$htaccess_content = file_get_contents( $htaccess_path );
			if ( false !== strpos( $htaccess_content, 'Options -Indexes' ) ) {
				// Protection exists, likely safe.
				return $result;
			}
		}

		// Test if directory listing is actually enabled.
		$response = wp_remote_get( $uploads_url, array( 'timeout' => 5 ) );
		
		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );
			$status_code = wp_remote_retrieve_response_code( $response );

			// Check for directory listing indicators.
			if ( 200 === $status_code ) {
				if ( false !== stripos( $body, 'Index of' ) ||
				     false !== stripos( $body, 'Parent Directory' ) ||
				     false !== stripos( $body, '<title>Index of' ) ) {
					$result['directory_listing_enabled'] = true;

					// Check for sensitive file types in listing.
					$sensitive_patterns = array( '.sql', '.zip', '.bak', '.tar', '.gz', 'backup' );
					foreach ( $sensitive_patterns as $pattern ) {
						if ( false !== stripos( $body, $pattern ) ) {
							++$result['sensitive_files_found'];
						}
					}
				}
			}
		}

		return $result;
	}
}
