<?php
/**
 * Core WordPress Updates Not Automatically Applied Diagnostic
 *
 * Checks if core updates are automated.
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
 * Core WordPress Updates Not Automatically Applied Diagnostic Class
 *
 * Detects non-automated core updates.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Core_WordPress_Updates_Not_Automatically_Applied extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'core-wordpress-updates-not-automatically-applied';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Core WordPress Updates Not Automatically Applied';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if core updates are automated';

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
		// Check if automatic updates are enabled
		if ( defined( 'AUTOMATIC_UPDATER_DISABLED' ) && AUTOMATIC_UPDATER_DISABLED ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Core WordPress updates are not automatically applied. Enable automatic updates for WordPress core to get security patches automatically.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => true,
				'kb_link'       => 'https://wpshadow.com/kb/core-wordpress-updates-not-automatically-applied',
			);
		}

		return null;
	}
}
