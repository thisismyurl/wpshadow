<?php
/**
 * Ticketing System Diagnostic
 *
 * Checks if a customer support ticketing system is implemented.
 *
 * @package WPShadow\Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Support Ticketing System
 *
 * Detects whether the site has a ticket management system for support requests.
 */
class Diagnostic_Support_Ticketing_System extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'support-ticketing-system';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Support Ticketing System';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for customer support ticket management';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'customer-support';

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Finding array if issues detected, null otherwise
	 */
	public static function check() {
		$issues  = array();
		$stats   = array();
		$plugins = array(
			'wp-support-plus-responsive-ticket-system/wp-support-plus.php' => 'WP Support Plus',
			'wplivechat-pro/wplivechat.php'                                => 'WP Live Chat Pro',
			'awesome-support/awesome-support.php'                          => 'Awesome Support',
			'freesoul-helpdesk/freesoul-helpdesk.php'                      => 'Freesoul Help Desk',
			'userfeedback/userfeedback.php'                                => 'UserFeedback',
			'zendesk-support-plugin/zendesk-support.php'                   => 'Zendesk Support',
		);

		$active = array();
		foreach ( $plugins as $file => $name ) {
			if ( is_plugin_active( $file ) ) {
				$active[] = $name;
			}
		}

		$stats['active_ticketing_tools']  = count( $active );
		$stats['ticketing_plugins_found'] = $active;

		if ( empty( $active ) ) {
			$issues[] = __( 'No support ticketing system detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'A support ticketing system organizes customer inquiries, tracks issues, and ensures no request falls through the cracks. This improves response times, customer satisfaction, and helps your team provide better support.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/support-ticketing',
				'context'       => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
