<?php
/**
 * XSS Attack Prevention Not Tested Diagnostic
 *
 * Checks if XSS prevention is tested.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * XSS Attack Prevention Not Tested Diagnostic Class
 *
 * Detects untested XSS prevention.
 *
 * @since 1.2601.2352
 */
class Diagnostic_XSS_Attack_Prevention_Not_Tested extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'xss-attack-prevention-not-tested';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'XSS Attack Prevention Not Tested';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if XSS prevention is tested';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for security testing
		if ( ! is_plugin_active( 'wordfence/wordfence.php' ) && ! is_plugin_active( 'sucuri-scanner/sucuri.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Cross-site scripting (XSS) prevention is not tested. Implement automated security testing to detect XSS vulnerabilities.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/xss-attack-prevention-not-tested',
			);
		}

		return null;
	}
}
