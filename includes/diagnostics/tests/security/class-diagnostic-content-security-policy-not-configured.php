<?php
/**
 * Content Security Policy Not Configured Diagnostic
 *
 * Checks if Content Security Policy is set.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2340
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content Security Policy Not Configured Diagnostic Class
 *
 * Detects missing CSP headers.
 *
 * @since 1.2601.2340
 */
class Diagnostic_Content_Security_Policy_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-security-policy-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Security Policy Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Content Security Policy is set';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2340
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for security plugins that set CSP
		$csp_plugins = array(
			'wordfence/wordfence.php',
			'all-in-one-wp-security-and-firewall/wp-security.php',
		);

		$csp_active = false;
		foreach ( $csp_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$csp_active = true;
				break;
			}
		}

		if ( ! $csp_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Content Security Policy (CSP) is not configured. Implement CSP headers to prevent XSS and injection attacks.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/content-security-policy-not-configured',
			);
		}

		return null;
	}
}
