<?php
declare(strict_types=1);
/**
 * Geoblocking for Admin Dashboard Diagnostic
 *
 * Philosophy: Access control - restrict admin access by location
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if admin dashboard is geoblocked.
 */
class Diagnostic_Geoblocking_Admin_Dashboard extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		$admin_geo_restricted = get_option( 'wpshadow_admin_geo_restricted' );

		if ( empty( $admin_geo_restricted ) ) {
			return array(
				'id'            => 'geoblocking-admin-dashboard',
				'title'         => 'No Geographic Restrictions on Admin Dashboard',
				'description'   => 'Admin dashboard accessible from anywhere globally. Restrict to known office locations to prevent unauthorized access. Enable IP/geo-based admin restrictions.',
				'severity'      => 'medium',
				'category'      => 'security',
				'kb_link'       => 'https://wpshadow.com/kb/georestrict-admin-access/',
				'training_link' => 'https://wpshadow.com/training/admin-access-control/',
				'auto_fixable'  => false,
				'threat_level'  => 60,
			);
		}

		return null;
	}
}
