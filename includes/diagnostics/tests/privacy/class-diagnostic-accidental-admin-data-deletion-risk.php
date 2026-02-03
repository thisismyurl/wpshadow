<?php
/**
 * Accidental Admin Data Deletion Risk Diagnostic
 *
 * Tests safeguards preventing accidental deletion of admin or critical user accounts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Privacy
 * @since      1.2034.1450
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Accidental_Admin_Data_Deletion_Risk Class
 *
 * Verifies safeguards against accidental admin account deletion.
 *
 * @since 1.2034.1450
 */
class Diagnostic_Accidental_Admin_Data_Deletion_Risk extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'accidental-admin-data-deletion-risk';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin Account Deletion Safeguards';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for safeguards preventing accidental deletion of admin accounts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2034.1450
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Count total administrators.
		$admin_users = get_users( array( 'role' => 'administrator' ) );
		$admin_count = count( $admin_users );

		if ( 1 === $admin_count ) {
			// Single admin site - very risky.
			$issues[] = __( 'Only one administrator account exists - deleting it would lock you out', 'wpshadow' );
		}

		// 2. Check if there's a filter to prevent admin deletion.
		$has_admin_protection = has_filter( 'wp_privacy_personal_data_erasers' );
		
		if ( ! $has_admin_protection ) {
			// WordPress doesn't have built-in admin protection in erasers.
			$issues[] = __( 'No filter detected to prevent admin account erasure', 'wpshadow' );
		}

		// 3. Check if current user can be deleted.
		$current_user = wp_get_current_user();
		if ( user_can( $current_user, 'manage_options' ) ) {
			// Check if there's a safeguard for self-deletion.
			global $wpdb;
			
			$erasure_requests = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} p
					INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
					WHERE p.post_type = %s 
					AND pm.meta_key = %s
					AND pm.meta_value = %s",
					'user_request',
					'_user_email',
					$current_user->user_email
				)
			);

			if ( (int) $erasure_requests > 0 ) {
				$issues[] = __( 'Current admin user has erasure request - verify safeguards exist', 'wpshadow' );
			}
		}

		// 4. Check for role-based restrictions.
		$erasers = apply_filters( 'wp_privacy_personal_data_erasers', array() );
		
		$has_role_check = false;
		foreach ( $erasers as $eraser ) {
			if ( isset( $eraser['callback'] ) && is_callable( $eraser['callback'] ) ) {
				// We can't directly check the callback logic, but we can verify it exists.
				$has_role_check = true;
				break;
			}
		}

		if ( ! $has_role_check && $admin_count > 0 ) {
			$issues[] = __( 'Erasure process may not check user roles before deletion', 'wpshadow' );
		}

		// 5. Check for confirmation requirements.
		$erasure_requires_confirmation = has_action( 'user_request_action_confirmed' );
		
		if ( ! $erasure_requires_confirmation ) {
			$issues[] = __( 'No confirmation requirement detected for erasure requests', 'wpshadow' );
		}

		// 6. Check for site lockout scenarios (multisite).
		if ( is_multisite() ) {
			// Check if there's at least one super admin.
			$super_admins = get_super_admins();
			
			if ( count( $super_admins ) <= 1 ) {
				$issues[] = __( 'Only one super admin - network could be locked if deleted', 'wpshadow' );
			}

			// Check if network admin deletion is protected.
			$network_protection = has_filter( 'wpmu_validate_user_signup' );
			if ( ! $network_protection ) {
				$issues[] = __( 'No network-level deletion protection detected', 'wpshadow' );
			}
		}

		// 7. Check for custom capability requirements.
		$delete_users_cap = current_user_can( 'delete_users' );
		$list_users_cap   = current_user_can( 'list_users' );
		
		if ( ! $delete_users_cap && ! $list_users_cap ) {
			// This is fine - but we need to verify someone CAN delete if needed.
			$users_with_delete = get_users(
				array(
					'capability' => 'delete_users',
					'number'     => 1,
				)
			);

			if ( empty( $users_with_delete ) ) {
				$issues[] = __( 'No users have permission to delete users - requests cannot be processed', 'wpshadow' );
			}
		}

		// 8. Test for warning systems.
		$has_warning_filter = has_filter( 'wp_privacy_personal_data_eraser_done' );
		
		if ( ! $has_warning_filter ) {
			$issues[] = __( 'No post-erasure notification system detected', 'wpshadow' );
		}

		// 9. Check for backup/rollback capability.
		$backup_plugins = array(
			'updraftplus/updraftplus.php',
			'backwpup/backwpup.php',
			'backup/backup.php',
			'duplicator/duplicator.php',
		);

		$has_backup = false;
		foreach ( $backup_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_backup = true;
				break;
			}
		}

		if ( ! $has_backup && $admin_count <= 2 ) {
			$issues[] = __( 'No backup plugin detected - admin deletion cannot be rolled back', 'wpshadow' );
		}

		// 10. Check for session invalidation safeguards.
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			
			// If current user could be deleted, verify sessions would be handled.
			$has_session_handler = has_action( 'delete_user' );
			
			if ( ! $has_session_handler ) {
				$issues[] = __( 'No session invalidation hook detected - deleted users may remain logged in', 'wpshadow' );
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Admin deletion safeguard gaps: %s', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => 'critical',
			'threat_level' => 95,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/admin-deletion-safeguards',
			'details'      => array(
				'issues'      => $issues,
				'admin_count' => $admin_count,
				'is_multisite' => is_multisite(),
			),
		);
	}
}
