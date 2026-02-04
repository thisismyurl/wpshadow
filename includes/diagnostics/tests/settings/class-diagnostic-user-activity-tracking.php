<?php
/**
 * User Activity Tracking Diagnostic
 *
 * Checks whether user activity tracking is enabled.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Activity Tracking Diagnostic
 *
 * Validates that activity logging is configured for audit trails.
 *
 * @since 1.6030.2240
 */
class Diagnostic_User_Activity_Tracking extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-activity-tracking';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Activity Tracking';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether user activity tracking is enabled';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$active_plugins = get_option( 'active_plugins', array() );
		$log_plugins = array(
			'wp-security-audit-log/wp-security-audit-log.php' => 'WP Security Audit Log',
			'simple-history/index.php' => 'Simple History',
			'activity-log/activity-log.php' => 'Activity Log',
		);

		$enabled = array();
		foreach ( $log_plugins as $plugin => $name ) {
			if ( in_array( $plugin, $active_plugins, true ) ) {
				$enabled[] = $name;
			}
		}

		if ( empty( $enabled ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No user activity tracking detected', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/user-activity-tracking',
				'details'      => array(
					'issues' => array(
						__( 'Install an activity log plugin to maintain audit trails', 'wpshadow' ),
					),
				),
			);
		}

		return null;
	}
}
