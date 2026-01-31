<?php
/**
 * Cookie Domain Configuration Analysis Diagnostic
 *
 * Validates cookie domain constants for proper session handling.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26030.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cookie Domain Configuration Analysis Class
 *
 * Tests cookie configuration.
 *
 * @since 1.26030.0000
 */
class Diagnostic_Cookie_Domain_Configuration_Analysis extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cookie-domain-configuration-analysis';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cookie Domain Configuration Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates cookie domain constants for proper session handling';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26030.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cookie_check = self::check_cookie_configuration();
		
		if ( $cookie_check['has_issues'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $cookie_check['issues'] ),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/cookie-domain-configuration-analysis',
				'meta'         => array(
					'cookie_domain_set'      => $cookie_check['cookie_domain_set'],
					'cookie_domain_value'    => $cookie_check['cookie_domain_value'],
					'site_url'               => $cookie_check['site_url'],
					'recommendations'        => $cookie_check['recommendations'],
				),
			);
		}

		return null;
	}

	/**
	 * Check cookie configuration.
	 *
	 * @since  1.26030.0000
	 * @return array Check results.
	 */
	private static function check_cookie_configuration() {
		$check = array(
			'has_issues'         => false,
			'issues'             => array(),
			'cookie_domain_set'  => defined( 'COOKIE_DOMAIN' ),
			'cookie_domain_value' => defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '',
			'site_url'           => home_url(),
			'recommendations'    => array(),
		);

		// Parse site URL.
		$parsed_url = wp_parse_url( home_url() );
		$site_host = isset( $parsed_url['host'] ) ? $parsed_url['host'] : '';

		// Check if COOKIE_DOMAIN is set.
		if ( $check['cookie_domain_set'] && ! empty( $check['cookie_domain_value'] ) ) {
			// Check for www subdomain issues.
			if ( false !== strpos( $check['cookie_domain_value'], 'www.' ) && false === strpos( $site_host, 'www.' ) ) {
				$check['has_issues'] = true;
				$check['issues'][] = __( 'COOKIE_DOMAIN set to www subdomain but site URL has no www (login issues on non-www)', 'wpshadow' );
				$check['recommendations'][] = __( 'Remove COOKIE_DOMAIN constant or set to naked domain', 'wpshadow' );
			}

			// Check for hardcoded domain mismatches.
			if ( $check['cookie_domain_value'] !== $site_host && '.' . $site_host !== $check['cookie_domain_value'] ) {
				$check['has_issues'] = true;
				$check['issues'][] = sprintf(
					/* translators: 1: COOKIE_DOMAIN value, 2: site URL */
					__( 'COOKIE_DOMAIN (%1$s) does not match site URL (%2$s)', 'wpshadow' ),
					$check['cookie_domain_value'],
					$site_host
				);
				$check['recommendations'][] = __( 'Update COOKIE_DOMAIN to match current site URL or remove it', 'wpshadow' );
			}
		}

		// Check COOKIEPATH.
		if ( defined( 'COOKIEPATH' ) ) {
			$cookie_path = COOKIEPATH;
			$site_path = isset( $parsed_url['path'] ) ? $parsed_url['path'] : '/';

			if ( $cookie_path !== $site_path ) {
				$check['has_issues'] = true;
				$check['issues'][] = __( 'COOKIEPATH does not match site path (potential login issues)', 'wpshadow' );
				$check['recommendations'][] = __( 'Update COOKIEPATH to match site installation path', 'wpshadow' );
			}
		}

		return $check;
	}
}
