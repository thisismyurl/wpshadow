<?php
/**
 * Maintenance Mode Off Diagnostic
 *
 * Checks whether the site is currently in maintenance or coming-soon mode that
 * blocks real visitors from accessing it. Flags when the WP_Settings helper
 * detects an active .maintenance file or a known coming-soon plugin option.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_WP_Settings_Helper as WP_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Maintenance_Mode_Off Class
 *
 * Delegates maintenance-mode detection to the WP_Settings helper. Returns a
 * medium-severity finding when maintenance mode is active, or null when the
 * site is publicly accessible.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Maintenance_Mode_Off extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'maintenance-mode-off';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Maintenance Mode Off';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the site is currently in maintenance or coming-soon mode that is blocking real visitors from accessing the site.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Whether this diagnostic is part of the core trusted set.
	 *
	 * @var bool
	 */
	protected static $is_core = true;

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'high';

	/**
	 * Run the diagnostic check.
	 *
	 * Calls the WP_Settings helper to detect maintenance or coming-soon mode.
	 * Returns null when the site is publicly accessible. Returns a medium-severity
	 * finding when maintenance mode is active.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when maintenance mode is active, null when healthy.
	 */
	public static function check() {
		if ( ! WP_Settings::is_maintenance_mode_active() ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your site appears to be in maintenance or coming-soon mode. Real visitors cannot access the site. Disable maintenance mode once your site is ready to go live.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 40,
			'kb_link'      => 'https://wpshadow.com/kb/maintenance-mode-off?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'note' => __( 'Maintenance mode detected via .maintenance file or an active coming-soon plugin option.', 'wpshadow' ),
			),
		);
	}
}
