<?php
/**
 * Header Security Not Implemented Diagnostic
 *
 * Checks if security headers are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Header Security Not Implemented Diagnostic Class
 *
 * Detects missing security headers.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Header_Security_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'header-security-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Header Security Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if security headers are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for security header plugins
		$security_plugins = array(
			'wordfence/wordfence.php',
			'really-simple-ssl/really-simple-ssl.php',
			'sucuri-scanner/sucuri.php',
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
				'description'   => __( 'Security headers (CSP, X-Frame-Options, etc.) are not configured. These protect against common web attacks.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/header-security-not-implemented',
			);
		}

		return null;
	}
}
