<?php
/**
 * Security Headers Diagnostic
 *
 * Analyzes security headers configuration and best practices.
 *
 * @since   1.26033.2145
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security Headers Diagnostic
 *
 * Evaluates HTTP security headers implementation.
 *
 * @since 1.26033.2145
 */
class Diagnostic_Security_Headers extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'security-headers';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Security Headers';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes security headers configuration and best practices';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2145
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for critical security headers
		$headers_to_check = array(
			'X-Frame-Options'        => false,
			'X-Content-Type-Options' => false,
			'X-XSS-Protection'       => false,
			'Referrer-Policy'        => false,
			'Permissions-Policy'     => false,
		);

		if ( function_exists( 'apache_response_headers' ) ) {
			$response_headers = apache_response_headers();
			foreach ( $headers_to_check as $header => $present ) {
				if ( isset( $response_headers[ $header ] ) ) {
					$headers_to_check[ $header ] = true;
				}
			}
		}

		// Count missing headers
		$missing_headers = array();
		foreach ( $headers_to_check as $header => $present ) {
			if ( ! $present ) {
				$missing_headers[] = $header;
			}
		}

		$missing_count = count( $missing_headers );

		// Generate findings if critical headers missing
		if ( $missing_count >= 3 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of missing headers */
					__( '%d security headers missing. HTTP security headers protect against common web vulnerabilities.', 'wpshadow' ),
					$missing_count
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/security-headers',
				'meta'         => array(
					'missing_headers'   => $missing_headers,
					'missing_count'     => $missing_count,
					'recommendation'    => 'Add security headers via .htaccess or security plugin',
					'header_purposes'   => array(
						'X-Frame-Options'        => 'Prevents clickjacking attacks',
						'X-Content-Type-Options' => 'Prevents MIME sniffing',
						'X-XSS-Protection'       => 'Enables browser XSS filter',
						'Referrer-Policy'        => 'Controls referrer information',
						'Permissions-Policy'     => 'Restricts browser features',
					),
					'htaccess_example'  => "Header set X-Frame-Options \"SAMEORIGIN\"\nHeader set X-Content-Type-Options \"nosniff\"\nHeader set X-XSS-Protection \"1; mode=block\"",
					'security_score'    => 'Test at securityheaders.com',
				),
			);
		}

		// Warning if some headers missing
		if ( $missing_count > 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: comma-separated list of missing headers */
					__( 'Missing security headers: %s. Consider adding for defense-in-depth.', 'wpshadow' ),
					implode( ', ', $missing_headers )
				),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/security-headers',
				'meta'         => array(
					'missing_headers' => $missing_headers,
					'recommendation'  => 'Add remaining security headers',
				),
			);
		}

		return null;
	}
}
