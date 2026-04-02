<?php
/**
 * Subscriber Role Limitations Diagnostic
 *
 * Validates that subscriber role has appropriately restricted capabilities
 * and cannot perform administrative actions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Subscriber Role Limitations Diagnostic Class
 *
 * Checks subscriber role capability restrictions.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Subscriber_Role_Limitations extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'subscriber-role-limitations';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Subscriber Role Limitations';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates subscriber role restrictions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$roles  = wp_roles()->roles;

		// Check if subscriber role exists.
		if ( ! isset( $roles['subscriber'] ) ) {
			$issues[] = __( 'Subscriber role is missing (this is unusual)', 'wpshadow' );
		} else {
			$subscriber_caps = $roles['subscriber']['capabilities'];

			// Capabilities subscribers should NOT have.
			$forbidden_caps = array(
				'edit_posts',
				'delete_posts',
				'publish_posts',
				'upload_files',
				'edit_pages',
				'delete_pages',
				'publish_pages',
				'edit_others_posts',
				'delete_others_posts',
				'manage_categories',
				'moderate_comments',
				'manage_options',
				'edit_users',
				'delete_users',
			);

			$has_forbidden = array();
			foreach ( $forbidden_caps as $cap ) {
				if ( ! empty( $subscriber_caps[ $cap ] ) ) {
					$has_forbidden[] = $cap;
				}
			}

			if ( ! empty( $has_forbidden ) ) {
				$issues[] = sprintf(
					/* translators: %s: comma-separated list of capabilities */
					__( 'Subscriber role has elevated capabilities: %s', 'wpshadow' ),
					implode( ', ', $has_forbidden )
				);
			}

			// Check that subscribers have 'read' capability.
			if ( empty( $subscriber_caps['read'] ) ) {
				$issues[] = __( 'Subscriber role lacks "read" capability (users cannot access site)', 'wpshadow' );
			}
		}

		// Check for large number of subscribers (not an issue, but worth noting).
		$subscriber_count = count_users();
		$subscribers      = $subscriber_count['avail_roles']['subscriber'] ?? 0;

		// Check for subscribers with admin-bar access.
		if ( isset( $roles['subscriber'] ) ) {
			$subscriber_caps = $roles['subscriber']['capabilities'];
			if ( ! empty( $subscriber_caps['read'] ) ) {
				// Check if admin bar is disabled for subscribers.
				$show_admin_bar = get_user_meta( 1, 'show_admin_bar_front', true );
				// This is user-specific, so we can't check globally easily.
			}
		}

		// Check for subscribers who can access admin.
		// Note: By default, subscribers can access wp-admin (profile page).
		// This is normal WordPress behavior, but some sites may want to restrict it.

		// Check for subscriber accounts with suspicious characteristics.
		$subscribers_list = get_users(
			array(
				'role'   => 'subscriber',
				'fields' => array( 'ID', 'user_login', 'user_email', 'user_registered' ),
			)
		);

		$suspicious_subscribers = array();
		$admin_emails           = array();

		foreach ( $subscribers_list as $user ) {
			// Check for admin-like usernames.
			if ( preg_match( '/(admin|root|superuser|test)/i', $user->user_login ) ) {
				$suspicious_subscribers[] = array(
					'user_login' => $user->user_login,
					'reason'     => __( 'Admin-like username', 'wpshadow' ),
				);
			}

			// Check for admin email patterns.
			if ( preg_match( '/(admin|root|webmaster)@/i', $user->user_email ) ) {
				$admin_emails[] = $user->user_login;
			}

			// Check for very old inactive subscribers.
			$registered_time = strtotime( $user->user_registered );
			$days_old        = ( time() - $registered_time ) / DAY_IN_SECONDS;

			if ( $days_old > 365 * 3 ) {
				$last_login = get_user_meta( $user->ID, 'last_login', true );
				if ( empty( $last_login ) || ( time() - strtotime( $last_login ) ) > 365 * DAY_IN_SECONDS ) {
					// Old and inactive - not necessarily an issue, but worth noting.
				}
			}
		}

		if ( count( $suspicious_subscribers ) > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of suspicious subscribers */
				__( '%d subscriber accounts have suspicious characteristics', 'wpshadow' ),
				count( $suspicious_subscribers )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of subscriber role issues */
					__( 'Found %d subscriber role configuration issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'details'      => array(
					'issues'                 => $issues,
					'subscriber_count'       => $subscribers,
					'suspicious_subscribers' => array_slice( $suspicious_subscribers, 0, 10 ),
					'recommendation'         => __( 'Review subscriber role capabilities and ensure they are limited to read-only access.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
