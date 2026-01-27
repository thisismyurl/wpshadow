<?php
/**
 * Diagnostic: Secure Cookie Flag
 *
 * Checks if the secure flag is set on cookies (HTTPS-only transmission).
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Secure_Cookie_Flag
 *
 * Tests if secure cookie flag is enabled.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Secure_Cookie_Flag extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'secure-cookie-flag';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Secure Cookie Flag';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if cookies use the secure flag for HTTPS-only transmission';

	/**
	 * Check secure cookie flag setting.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// If not using HTTPS, recommend enabling secure cookies.
		if ( ! is_ssl() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Site is not using HTTPS. Secure cookie flag cannot be used without HTTPS. Enable HTTPS first to secure cookies.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/secure_cookie_flag',
				'meta'        => array(
					'is_ssl' => false,
				),
			);
		}

		// Check if COOKIE_DOMAIN is set appropriately.
		$cookie_domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '';

		if ( empty( $cookie_domain ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'COOKIE_DOMAIN is not defined. Set it explicitly in wp-config.php for proper secure cookie configuration (with or without leading dot).', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/secure_cookie_flag',
				'meta'        => array(
					'is_ssl'        => true,
					'cookie_domain' => $cookie_domain,
				),
			);
		}

		return null;
	}
}
