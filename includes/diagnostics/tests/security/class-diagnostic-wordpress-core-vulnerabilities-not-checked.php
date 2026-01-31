<?php
/**
 * WordPress Core Vulnerabilities Not Checked Diagnostic
 *
 * Checks if core vulnerabilities are monitored.
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
 * WordPress Core Vulnerabilities Not Checked Diagnostic Class
 *
 * Detects missing core vulnerability monitoring.
 *
 * @since 1.2601.2352
 */
class Diagnostic_WordPress_Core_Vulnerabilities_Not_Checked extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wordpress-core-vulnerabilities-not-checked';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Core Vulnerabilities Not Checked';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if core vulnerabilities are monitored';

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
		// Check for vulnerability scanner
		if ( ! is_plugin_active( 'wordfence/wordfence.php' ) && ! is_plugin_active( 'sucuri-scanner/sucuri.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'WordPress core vulnerabilities are not being monitored. Enable vulnerability scanning to detect known security issues in WordPress core.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/wordpress-core-vulnerabilities-not-checked',
			);
		}

		return null;
	}
}
