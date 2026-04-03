<?php
/**
 * Admin Account Count Minimized Diagnostic
 *
 * Checks whether the number of administrator accounts exceeds a safe threshold,
 * reducing the attack surface for privilege abuse or compromised credentials.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Account Count Minimized Diagnostic Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Admin_Account_Count_Minimized extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'admin-account-count-minimized';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Admin Account Count Minimized';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the number of administrator accounts exceeds a safe threshold, which increases the attack surface for privilege abuse or compromised credentials.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'security';

/**
 * Confidence level of this diagnostic.
 *
 * @var string
 */
protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * Uses count_users() to count administrator-role accounts and flags when
	 * the total exceeds the recommended maximum.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when admin count is excessive, null when healthy.
	 */
	public static function check() {
		$counts      = count_users();
		$admin_count = isset( $counts['avail_roles']['administrator'] ) ? (int) $counts['avail_roles']['administrator'] : 0;

		// 1–2 admin accounts is a reasonable working number.
		if ( $admin_count <= 2 ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of admin accounts */
				__( 'Your site has %d administrator accounts. Each admin account is a potential entry point for an attacker. Review them and reduce to only the accounts that genuinely require administrator-level access.', 'wpshadow' ),
				$admin_count
			),
			'severity'     => $admin_count > 5 ? 'high' : 'medium',
			'threat_level' => min( 30 + ( ( $admin_count - 2 ) * 10 ), 80 ),
			'kb_link'      => 'https://wpshadow.com/kb/admin-account-count?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'admin_count' => $admin_count,
			),
		);
	}
}
