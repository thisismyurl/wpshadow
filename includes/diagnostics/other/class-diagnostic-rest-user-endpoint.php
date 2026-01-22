<?php
declare(strict_types=1);
/**
 * REST API User Endpoint Diagnostic
 *
 * Philosophy: Information disclosure - prevent user data leakage
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if REST API user endpoint exposes user data.
 */
class Diagnostic_REST_User_Endpoint extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		// Test REST API user endpoint
		$rest_url = rest_url( 'wp/v2/users' );
		$response = wp_remote_get(
			$rest_url,
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$status = wp_remote_retrieve_response_code( $response );

		if ( $status === 200 ) {
			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );

			if ( ! empty( $data ) && is_array( $data ) ) {
				return array(
					'id'            => 'rest-user-endpoint',
					'title'         => 'REST API Exposes User Data',
					'description'   => sprintf(
						'The /wp-json/wp/v2/users endpoint is publicly accessible and exposes %d user(s) information including usernames, emails, and IDs. Restrict access to authenticated requests.',
						count( $data )
					),
					'severity'      => 'medium',
					'category'      => 'security',
					'kb_link'       => 'https://wpshadow.com/kb/secure-rest-api-users/',
					'training_link' => 'https://wpshadow.com/training/rest-api-security/',
					'auto_fixable'  => true,
					'threat_level'  => 65,
				);
			}
		}

		return null;
	}
}
