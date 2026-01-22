<?php
declare(strict_types=1);
/**
 * REST API Authentication Diagnostic
 *
 * Philosophy: API security - require authentication
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if REST API requires authentication.
 */
class Diagnostic_REST_API_Authentication extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$rest_api = get_option( 'rest_api_enabled' );
		
		if ( ! empty( $rest_api ) && empty( get_option( 'rest_api_requires_authentication' ) ) ) {
			return array(
				'id'          => 'rest-api-authentication',
				'title'       => 'REST API Endpoints Publicly Accessible',
				'description' => 'REST API endpoints do not require authentication. Unauthenticated users can query/modify data. Require authentication for sensitive REST endpoints.',
				'severity'    => 'high',
				'category'    => 'security',
				'kb_link'     => 'https://wpshadow.com/kb/secure-rest-api/',
				'training_link' => 'https://wpshadow.com/training/rest-api-security/',
				'auto_fixable' => false,
				'threat_level' => 75,
			);
		}
		
		return null;
	}
}
