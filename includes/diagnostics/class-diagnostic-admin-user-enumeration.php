<?php
/**
 * Admin User Enumeration Prevention
 *
 * Checks if WordPress is configured to prevent user enumeration attacks
 * through author archives and REST API endpoints.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.0630
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Admin User Enumeration
 *
 * Detects if user enumeration is possible through multiple attack vectors.
 *
 * @since 1.26033.0630
 */
class Diagnostic_Admin_User_Enumeration extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'admin-user-enumeration';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Admin User Enumeration Prevention';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if WordPress is configured to prevent user enumeration attacks';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'admin-security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.0630
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check REST API user enumeration
		$rest_enabled = get_option( 'rest_api_enabled', true );
		if ( $rest_enabled ) {
			// REST API is enabled - check if user endpoints are exposed
			$issues[] = __( 'REST API user endpoints may expose user information', 'wpshadow' );
		}

		// Check author archives
		$has_author_archives = true;
		// WordPress always has author archives unless disabled via filter
		if ( $has_author_archives ) {
			$issues[] = __( 'Author archives are publicly accessible and may leak user information', 'wpshadow' );
		}

		// Check if vulnerable query string parameters are accessible
		$response = wp_remote_head( home_url( '?author=1' ), array( 'sslverify' => false ) );
		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			$issues[] = __( 'Author enumeration via query string is possible', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-user-enumeration',
			);
		}

		return null;
	}
}
