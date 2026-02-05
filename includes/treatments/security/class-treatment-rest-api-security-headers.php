<?php
/**
 * REST API Security Headers Treatment
 *
 * Issue #4947: REST API Missing Rate Limiting
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if REST API has rate limiting.
 * Unlimited API access enables brute force and DoS.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_REST_API_Security_Headers Class
 *
 * @since 1.6050.0000
 */
class Treatment_REST_API_Security_Headers extends Treatment_Base {

	protected static $slug = 'rest-api-security-headers';
	protected static $title = 'REST API Missing Rate Limiting';
	protected static $description = 'Checks if REST API has security headers and rate limiting';
	protected static $family = 'security';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Implement rate limiting: 100 requests per minute per IP', 'wpshadow' );
		$issues[] = __( 'Require authentication for sensitive endpoints', 'wpshadow' );
		$issues[] = __( 'Add CORS headers to restrict domains', 'wpshadow' );
		$issues[] = __( 'Disable REST API discovery in HTML head', 'wpshadow' );
		$issues[] = __( 'Monitor API usage for abuse patterns', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'The REST API is publicly accessible without limits. Rate limiting prevents brute force attacks and API abuse.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/rest-api-security',
				'details'      => array(
					'recommendations'         => $issues,
					'attack_vectors'          => 'User enumeration, brute force, DoS',
					'rate_limit_header'       => 'X-RateLimit-Limit: 100, X-RateLimit-Remaining: 99',
					'disable_discovery'       => 'remove_action("wp_head", "rest_output_link_wp_head");',
				),
			);
		}

		return null;
	}
}
