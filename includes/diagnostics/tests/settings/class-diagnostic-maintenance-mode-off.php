<?php
/**
 * Maintenance Mode Off Diagnostic (Stub)
 *
 * TODO stub mapped to the settings gauge.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
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
 * TODO: Implement full test logic and remediation guidance.
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
	protected static $description = 'TODO: Implement diagnostic logic for Maintenance Mode Off';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Detect maintenance/coming-soon plugins or options still active on public sites.
	 *
	 * TODO Fix Plan:
	 * - Disable maintenance mode when the site is ready for visitors.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
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
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/maintenance-mode-off',
			'details'      => array(
				'note' => __( 'Maintenance mode detected via .maintenance file or an active coming-soon plugin option.', 'wpshadow' ),
			),
		);
	}
}
