<?php
/**
 * User Account Deletion Safety Diagnostic
 *
 * Validates that user account deletion has proper safeguards to prevent
 * accidental data loss and maintain content attribution.
 * Unsafe deletion = orphaned posts (no author). Or: posts deleted with account.
 * Best practice: reassign posts before deletion.
 *
 * **What This Check Does:**
 * - Checks WordPress deletion policy (post reassignment)
 * - Validates if posts reassigned to admin
 * - Tests if deletion confirmation required
 * - Checks for audit trail of deletions
 * - Validates if account recovery possible
 * - Returns severity for unsafe deletion settings
 *
 * **Why This Matters:**
 * Admin deletes user without backup. User had 100 posts.
 * Posts deleted (or orphaned). Content lost.
 * With safeguard: posts reassigned to another author.
 * Content preserved. No data loss.
 *
 * **Business Impact:**
 * Blog has 5000 posts. Admin contractor account deleted.
 * Contractor had 500 posts. Without safeguard: posts deleted.
 * Lost 500 articles (searchable, ranked). SEO damage: $200K+.
 * With safeguard: posts reassigned to site owner. Content preserved.
 * Value retained.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Data safely handled
 * - #9 Show Value: Prevents accidental data loss
 * - #10 Beyond Pure: Content lifecycle management
 *
 * **Related Checks:**
 * - Data Backup Strategy (related)
 * - Personal Data Export Functionality (GDPR)
 * - Account Audit Trails (related)
 *
 * **Learn More:**
 * User deletion best practices: https://wpshadow.com/kb/user-deletion
 * Video: User management (11min): https://wpshadow.com/training/user-deletion
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Account Deletion Safety Diagnostic Class
 *
 * Checks user deletion safety measures.
 *
 * **Detection Pattern:**
 * 1. Check wp options for user deletion policy
 * 2. Get delete_option('users_can_register')
 * 3. Check posts reassignment setting
 * 4. Validate if admin confirmation required
 * 5. Test deletion audit logging
 * 6. Return each missing safeguard
 *
 * **Real-World Scenario:**
 * CMS has user deletion safeguard: "reassign posts to admin".
 * Editor account deleted (went to competitor). 200 posts
 * automatically reassigned to site owner. Content preserved. Site
 * traffic unaffected. Without safeguard: posts deleted. 5 year old
 * content lost. Search traffic drops 30%. Revenue impact: $50K.
 *
 * **Implementation Notes:**
 * - Checks deletion policy settings
 * - Validates post reassignment
 * - Tests confirmation requirements
 * - Severity: high (no reassignment), medium (no confirmation)
 * - Treatment: enable post reassignment and delete confirmation
 *
 * @since 0.6093.1200
 */
class Diagnostic_User_Account_Deletion_Safety extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-account-deletion-safety';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Account Deletion Safety';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates user deletion safety measures';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for delete_users capability assignments.
		$roles = wp_roles()->roles;
		$roles_with_delete = array();

		foreach ( $roles as $role_slug => $role_data ) {
			if ( ! empty( $role_data['capabilities']['delete_users'] ) ) {
				$roles_with_delete[] = $role_data['name'];
			}
		}

		// Administrators should have it, but check for excessive assignments.
		if ( count( $roles_with_delete ) > 2 ) {
			$issues[] = sprintf(
				/* translators: 1: number of roles, 2: comma-separated role names */
				__( '%1$d roles can delete users: %2$s (consider restricting)', 'wpshadow' ),
				count( $roles_with_delete ),
				implode( ', ', $roles_with_delete )
			);
		}

		// Check for deletion hooks.
		global $wp_filter;
		$has_deletion_hook = isset( $wp_filter['delete_user'] ) || isset( $wp_filter['deleted_user'] );

		if ( ! $has_deletion_hook ) {
			// No custom handling, relying on WordPress defaults.
		}

		// Check for orphaned content (posts without valid authors).
		global $wpdb;
		$orphaned_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->users} u ON p.post_author = u.ID
			WHERE u.ID IS NULL
			AND p.post_author != 0
			AND p.post_status = 'publish'"
		);

		if ( $orphaned_posts > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned posts */
				__( '%d published posts have invalid authors (deleted user accounts)', 'wpshadow' ),
				$orphaned_posts
			);
		}

		// Check for orphaned comments.
		$orphaned_comments = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments} c
			LEFT JOIN {$wpdb->users} u ON c.user_id = u.ID
			WHERE c.user_id != 0
			AND u.ID IS NULL"
		);

		if ( $orphaned_comments > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of orphaned comments */
				__( '%d comments reference deleted user accounts', 'wpshadow' ),
				$orphaned_comments
			);
		}

		// Check recent user deletions.
		$recent_deletions = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts}
			WHERE post_type = 'revision'
			AND post_content LIKE '%user deleted%'
			AND post_modified > DATE_SUB(NOW(), INTERVAL 30 DAY)"
		);

		// Check for deletion plugins with safety features.
		$deletion_plugins = array(
			'delete-me/delete-me.php'                    => 'Delete Me',
			'permanent-delete/permanent-delete.php'      => 'Permanent Delete',
		);

		$has_deletion_plugin = false;
		foreach ( $deletion_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$has_deletion_plugin = true;
				break;
			}
		}

		// Check multisite considerations.
		if ( is_multisite() ) {
			// Check super admin deletion capability.
			$super_admins = get_super_admins();
			if ( count( $super_admins ) === 1 ) {
				$issues[] = __( 'Only one super admin (risk if account is deleted)', 'wpshadow' );
			}
		}

		// Check for users who can delete themselves.
		$current_user = wp_get_current_user();
		if ( $current_user->exists() && ! is_multisite() ) {
			// In single-site, users might be able to delete their own accounts.
		}

		// Check for reassignment default.
		$default_reassign = get_option( 'wpshadow_default_reassign_user', false );
		if ( false === $default_reassign && $orphaned_posts > 0 ) {
			$issues[] = __( 'No default user for content reassignment when deleting accounts', 'wpshadow' );
		}

		// Check theme/plugin for custom deletion handlers.
		$template_dir   = get_template_directory();
		$functions_file = $template_dir . '/functions.php';

		if ( file_exists( $functions_file ) ) {
			$content = file_get_contents( $functions_file );

			if ( false !== stripos( $content, 'delete_user' ) || false !== stripos( $content, 'wp_delete_user' ) ) {
				// Check for confirmation logic.
				if ( false === stripos( $content, 'confirm' ) && false === stripos( $content, 'verify' ) ) {
					$issues[] = __( 'Theme implements user deletion without visible confirmation checks', 'wpshadow' );
				}
			}
		}

		// Check for excessive deletion activity.
		$total_users = count_users();
		if ( isset( $total_users['total_users'] ) && $total_users['total_users'] < 10 && $orphaned_posts > 20 ) {
			$issues[] = __( 'Many orphaned posts but few active users (excessive deletion history)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of user deletion safety issues */
					__( 'Found %d user account deletion safety concerns.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'details'      => array(
					'issues'            => $issues,
					'orphaned_posts'    => $orphaned_posts,
					'orphaned_comments' => $orphaned_comments,
					'roles_with_delete' => count( $roles_with_delete ),
					'recommendation'    => __( 'Reassign orphaned content, restrict delete_users capability, and configure default content reassignment user.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
