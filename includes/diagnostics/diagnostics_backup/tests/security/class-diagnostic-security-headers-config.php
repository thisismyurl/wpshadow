<?php
/**
 * Security Headers Configuration Diagnostic
 *
 * Verifies critical HTTP security headers are properly configured to
 * protect against XSS, clickjacking, MIME sniffing, and other attacks.
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
 * Diagnostic_Security_Headers_Config Class
 *
 * Detects missing security headers.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Security_Headers_Config extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'security-headers-config';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Security Headers Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies HTTP security headers are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if headers missing, null otherwise.
	 */
	public static function check() {
		$header_check = self::check_security_headers();

		if ( empty( $header_check['missing'] ) ) {
			return null; // All headers present
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of missing headers */
				__( '%d critical security headers missing. Headers protect against XSS, clickjacking, MIME sniffing attacks. Easy to add, massive security improvement.', 'wpshadow' ),
				count( $header_check['missing'] )
			),
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/security-headers',
			'family'       => self::$family,
			'meta'         => array(
				'missing_headers' => $header_check['missing'],
				'present_headers' => $header_check['present'],
				'security_score'  => $header_check['score'],
			),
			'details'      => array(
				'critical_security_headers' => array(
					'X-Frame-Options' => array(
						'Purpose: Prevents clickjacking attacks',
						'Value: SAMEORIGIN or DENY',
						'Protects: Stops site from being embedded in iframe',
					),
					'X-Content-Type-Options' => array(
						'Purpose: Prevents MIME sniffing',
						'Value: nosniff',
						'Protects: Blocks browser from interpreting files as different type',
					),
					'X-XSS-Protection' => array(
						'Purpose: Enables XSS filter in older browsers',
						'Value: 1; mode=block',
						'Protects: Stops reflected XSS attacks',
					),
					'Strict-Transport-Security (HSTS)' => array(
						'Purpose: Forces HTTPS connections',
						'Value: max-age=31536000; includeSubDomains',
						'Protects: Prevents downgrade to HTTP',
					),
					'Content-Security-Policy' => array(
						'Purpose: Controls resource loading',
						'Value: default-src \'self\'',
						'Protects: Prevents XSS via script injection',
					),
					'Referrer-Policy' => array(
						'Purpose: Controls referer information',
						'Value: strict-origin-when-cross-origin',
						'Protects: Limits information leakage',
					),
				),
				'adding_headers_apache'     => array(
					'Via .htaccess' => array(
						'File: /public_html/.htaccess',
						'Add to top:',
						'<IfModule mod_headers.c>',
						'  Header set X-Frame-Options "SAMEORIGIN"',
						'  Header set X-Content-Type-Options "nosniff"',
						'  Header set X-XSS-Protection "1; mode=block"',
						'  Header set Referrer-Policy "strict-origin-when-cross-origin"',
						'</IfModule>',
					),
					'HSTS (HTTPS only)' => array(
						'Header set Strict-Transport-Security "max-age=31536000; includeSubDomains"',
						'Only add if site fully on HTTPS',
					),
				),
				'adding_headers_nginx'      => array(
					'Via nginx.conf' => array(
						'File: /etc/nginx/sites-available/yoursite.conf',
						'Add inside server block:',
						'add_header X-Frame-Options "SAMEORIGIN" always;',
						'add_header X-Content-Type-Options "nosniff" always;',
						'add_header X-XSS-Protection "1; mode=block" always;',
						'add_header Referrer-Policy "strict-origin-when-cross-origin" always;',
					),
					'Reload Nginx' => 'sudo systemctl reload nginx',
				),
				'adding_headers_wordpress'  => array(
					'Via Plugin' => array(
						'Redirection: Free plugin with header support',
						'Security Headers: Dedicated header plugin',
						'Really Simple SSL: Adds HSTS automatically',
					),
					'Via functions.php' => array(
						'add_action(\'send_headers\', function() {',
						'  header(\'X-Frame-Options: SAMEORIGIN\');',
						'  header(\'X-Content-Type-Options: nosniff\');',
						'  header(\'X-XSS-Protection: 1; mode=block\');',
						'});',
					),
				),
				'testing_headers'           => array(
					'securityheaders.com' => 'Enter URL, get security score',
					'Chrome DevTools' => 'F12 → Network → Select request → Headers tab',
					'curl Command' => 'curl -I https://yoursite.com | grep -i "x-"',
				),
			),
		);
	}

	/**
	 * Check security headers.
	 *
	 * @since  1.2601.2148
	 * @return array Header check results.
	 */
	private static function check_security_headers() {
		$response = wp_remote_head( home_url() );

		if ( is_wp_error( $response ) ) {
			return array(
				'missing' => array( 'Unable to check headers' ),
				'present' => array(),
				'score'   => 0,
			);
		}

		$headers = wp_remote_retrieve_headers( $response );

		$required_headers = array(
			'x-frame-options',
			'x-content-type-options',
			'x-xss-protection',
			'referrer-policy',
		);

		$missing = array();
		$present = array();

		foreach ( $required_headers as $header ) {
			if ( ! isset( $headers[ $header ] ) ) {
				$missing[] = $header;
			} else {
				$present[] = $header;
			}
		}

		// Check HSTS only if HTTPS
		if ( is_ssl() && ! isset( $headers['strict-transport-security'] ) ) {
			$missing[] = 'strict-transport-security';
		}

		$score = count( $present ) > 0 ? round( ( count( $present ) / ( count( $present ) + count( $missing ) ) ) * 100 ) : 0;

		return array(
			'missing' => $missing,
			'present' => $present,
			'score'   => $score,
		);
	}
}
