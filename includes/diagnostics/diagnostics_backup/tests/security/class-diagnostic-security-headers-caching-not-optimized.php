<?php
/**
 * Security Headers Caching Not Optimized Diagnostic
 *
 * Checks if security headers are properly cached.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2345
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security Headers Caching Not Optimized Diagnostic Class
 *
 * Detects unoptimized security header caching.
 *
 * @since 1.2601.2345
 */
class Diagnostic_Security_Headers_Caching_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'security-headers-caching-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Security Headers Caching Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if security headers are cached';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2345
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for security header caching plugins
		$security_plugins = array(
			'wordfence/wordfence.php',
			'all-in-one-wp-security-and-firewall/wp-security.php',
		);

		$security_active = false;
		foreach ( $security_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$security_active = true;
				break;
			}
		}

		if ( ! $security_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Security headers are not being cached or optimized. Use security plugins to add and cache security headers.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/security-headers-caching-not-optimized',
			);
		}

		return null;
	}
}
