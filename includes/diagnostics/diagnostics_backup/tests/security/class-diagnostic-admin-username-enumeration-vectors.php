<?php
/**
 * Admin Username Enumeration Vectors Diagnostic
 *
 * Tests all methods attackers use to discover admin usernames.
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
 * Admin Username Enumeration Vectors Class
 *
 * Tests username enumeration.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Admin_Username_Enumeration_Vectors extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-username-enumeration-vectors';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin Username Enumeration Vectors';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests all methods attackers use to discover admin usernames';

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
		$enum_check = self::check_enumeration_vectors();
		
		if ( ! empty( $enum_check['exposed_vectors'] ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of exposed vectors */
					__( 'Found %d ways attackers can discover admin usernames (reduces brute force complexity)', 'wpshadow' ),
					count( $enum_check['exposed_vectors'] )
				),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-username-enumeration-vectors',
				'meta'         => array(
					'exposed_vectors' => $enum_check['exposed_vectors'],
				),
			);
		}

		return null;
	}

	/**
	 * Check enumeration vectors.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_enumeration_vectors() {
		$check = array(
			'exposed_vectors' => array(),
		);

		// Test /?author=1 redirect.
		$author_url = add_query_arg( 'author', '1', get_home_url() );
		$response = wp_remote_get( $author_url, array( 'timeout' => 10, 'redirection' => 0 ) );
		
		if ( ! is_wp_error( $response ) ) {
			$status_code = wp_remote_retrieve_response_code( $response );
			
			// 301/302 means redirect happens (username exposed in URL).
			if ( 301 === $status_code || 302 === $status_code ) {
				$check['exposed_vectors'][] = 'author-enumeration';
			}
		}

		// Test REST API user endpoint.
		$rest_url = rest_url( 'wp/v2/users' );
		$rest_response = wp_remote_get( $rest_url, array( 'timeout' => 10 ) );
		
		if ( ! is_wp_error( $rest_response ) ) {
			$status_code = wp_remote_retrieve_response_code( $rest_response );
			
			// 200 means users are exposed.
			if ( 200 === $status_code ) {
				$body = wp_remote_retrieve_body( $rest_response );
				$users = json_decode( $body, true );
				
				if ( ! empty( $users ) && is_array( $users ) ) {
					$check['exposed_vectors'][] = 'rest-api-users';
				}
			}
		}

		return $check;
	}
}
