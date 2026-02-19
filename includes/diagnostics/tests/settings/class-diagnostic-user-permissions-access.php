<?php
/**
 * User Permissions and Content Access Control
 *
 * Validates user permissions and content access control.
 *
 * @since   1.2034.1615
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_User_Permissions_Access Class
 *
 * Checks user permissions and content access control.
 *
 * @since 1.2034.1615
 */
class Diagnostic_User_Permissions_Access extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-permissions-access';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Permissions and Access';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates user permissions and content access control';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-management';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2034.1615
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Pattern 1: Contributors can edit published content
		$contributor_role = get_role( 'contributor' );

		if ( $contributor_role && ( $contributor_role->has_cap( 'edit_published_posts' ) || $contributor_role->has_cap( 'delete_published_posts' ) ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Contributors have access to published content', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/user-permissions-access',
				'details'      => array(
					'issue' => 'contributors_can_edit_published',
					'message' => __( 'Contributors role has editing capability on published posts', 'wpshadow' ),
					'wordpress_intent' => __( 'Contributors should only create posts, not publish or edit others\' content', 'wpshadow' ),
					'default_contributor' => array(
						'Can create posts' => 'draft_posts capability',
						'Cannot publish' => 'No publish_posts',
						'Cannot edit others' => 'No edit_others_posts',
						'Cannot delete' => 'No delete capability',
					),
					'risky_capabilities' => array(
						'edit_published_posts' => 'Can change published content',
						'delete_published_posts' => 'Can remove published content',
						'edit_others_posts' => 'Can modify other authors\' work',
					),
					'consequences' => array(
						'Unauthorized content changes',
						'Accidental or malicious edits',
						'Loss of original content',
						'Compliance violations',
					),
					'fixing_contributor_role' => "// Restore proper contributor permissions
\$contributor = get_role('contributor');

// Remove inappropriate capabilities
\$contributor->remove_cap('edit_published_posts');
\$contributor->remove_cap('delete_published_posts');
\$contributor->remove_cap('edit_others_posts');
\$contributor->remove_cap('delete_others_posts');

// Keep only appropriate capabilities
// They should have:
// - read
// - edit_posts (own drafts only)
// - delete_posts (own drafts only)",
					'role_hierarchy' => array(
						'Subscriber' => 'Read only',
						'Contributor' => 'Create drafts, need approval',
						'Author' => 'Publish own posts',
						'Editor' => 'Publish and edit all',
						'Admin' => 'Full access',
					),
					'per_post_permissions' => "// Check specific post permissions
\$post_id = 1;
\$user_id = 2;

\$can_edit = current_user_can('edit_post', \$post_id);
\$can_delete = current_user_can('delete_post', \$post_id);

if (\$can_delete && get_post(\$post_id)->post_author != \$user_id) {
	// User shouldn't be able to delete
}",
					'auditing_permissions' => array(
						'1. List all users with contributor role',
						'2. Check their actual capabilities',
						'3. Find inappropriate caps',
						'4. Reset role to defaults',
						'5. Test functionality',
					),
					'restoring_defaults' => "// Restore ALL role defaults
require_once(ABSPATH . 'wp-admin/includes/user.php');

// Remove custom role
remove_role('contributor');

// Recreate with defaults
add_role(
	'contributor',
	'Contributor',
	get_role('author')->capabilities // Copy author capabilities as base
);

// Remove inappropriate capabilities
\$contributor = get_role('contributor');
\$contributor->remove_cap('publish_posts');",
					'testing_permissions' => __( 'Test with contributor account to verify they cannot edit published posts', 'wpshadow' ),
					'recommendation' => __( 'Remove inappropriate capabilities from contributor role', 'wpshadow' ),
				),
			);
		}

		// Pattern 2: Private content accessible to low-privilege users
		$private_posts = $wpdb->get_results(
			"SELECT ID, post_title, post_status FROM {$wpdb->posts} WHERE post_status = 'private' LIMIT 5"
		);

		if ( ! empty( $private_posts ) ) {
			$subscriber = get_role( 'subscriber' );

			if ( $subscriber && $subscriber->has_cap( 'read_private_posts' ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Subscribers can access private content', 'wpshadow' ),
					'severity'     => 'high',
					'threat_level' => 75,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/user-permissions-access',
					'details'      => array(
						'issue' => 'private_content_accessible',
						'private_post_count' => count( $private_posts ),
						'sample_posts' => array_slice( $private_posts, 0, 5 ),
						'message' => __( 'Subscribers role has access to private content', 'wpshadow' ),
						'post_visibility' => array(
							'Public' => 'Anyone can see',
							'Private' => 'Author + admins only',
							'Protected' => 'Password required',
							'Draft' => 'Author only (not published)',
						),
						'who_should_see_private' => array(
							'Author of the post',
							'Administrator',
							'Possibly editors',
							'NOT subscribers/public',
						),
						'privacy_implications' => array(
							'Unauthorized information exposure',
							'Business confidential leaks',
							'Privacy violations',
							'Compliance issues (GDPR, etc)',
						),
						'removing_capability' => "// Remove private reading from subscribers
\$subscriber = get_role('subscriber');

if (\$subscriber->has_cap('read_private_posts')) {
	\$subscriber->remove_cap('read_private_posts');
}

// Only admins and editors should read private
\$editor = get_role('editor');
if (!\$editor->has_cap('read_private_posts')) {
	\$editor->add_cap('read_private_posts');
}",
					'checking_private_access' => "// Check what private posts user can see
\$private_posts = get_posts(array(
	'post_status' => 'private',
	'posts_per_page' => -1,
));

// For each post, check if current user can read
foreach (\$private_posts as \$post) {
	if (current_user_can('read_post', \$post->ID)) {
		echo \$post->post_title . ' is readable\\n';
	}
}",
					'metadata_exposure' => __( 'Check if private content metadata is exposed in APIs', 'wpshadow' ),
					'search_inclusion' => __( 'Ensure private posts excluded from search results for non-admin', 'wpshadow' ),
					'recommendation' => __( 'Remove read_private_posts capability from low-privilege roles', 'wpshadow' ),
				),
			);
			}
		}

		// Pattern 3: Privilege escalation vulnerabilities
		$all_users = get_users( array( 'number' => -1 ) );
		$privilege_issues = array();

		foreach ( $all_users as $user ) {
			$caps = $user->get_role() ? get_role( $user->get_role() )->capabilities : array();

			// Check for suspicious capabilities
			if ( isset( $caps['edit_users'] ) && $user->get_role() !== 'administrator' ) {
				$privilege_issues[] = array(
					'user_id' => $user->ID,
					'user_login' => $user->user_login,
					'role' => $user->get_role(),
					'issue' => 'non_admin_can_edit_users',
				);
			}
		}

		if ( ! empty( $privilege_issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Non-admin users have privilege escalation capabilities', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/user-permissions-access',
				'details'      => array(
					'issue' => 'privilege_escalation',
					'affected_users' => count( $privilege_issues ),
					'users' => array_slice( $privilege_issues, 0, 10 ),
					'message' => sprintf(
						/* translators: %d: number of users */
						__( '%d non-admin users have admin-level capabilities', 'wpshadow' ),
						count( $privilege_issues )
					),
					'privilege_escalation' => __( 'Ability for lower-role user to gain admin access', 'wpshadow' ),
					'critical_capabilities' => array(
						'edit_users' => 'Can modify user accounts',
						'create_users' => 'Can create new admin accounts',
						'delete_users' => 'Can remove users',
						'manage_options' => 'Can access all settings',
						'activate_plugins' => 'Can install malware',
					),
					'exploitation_scenario' => array(
						'1. Attacker gains editor account',
						'2. Uses edit_users capability',
						'3. Modifies own account to admin',
						'4. Complete site compromise',
					),
					'finding_problem_users' => "// Find non-admins with admin capabilities
\$users = get_users(array('number' => -1));

foreach (\$users as \$user) {
	\$role = \$user->get_role();
	
	if (\$role !== 'administrator') {
		\$user_obj = new WP_User(\$user->ID);
		
		if (\$user_obj->has_cap('edit_users') || 
		    \$user_obj->has_cap('manage_options')) {
			echo \"ALERT: {$user->user_login} ({$role}) has admin caps\\n\";
		}
	}
}",
					'fixing_escalation' => "// Remove inappropriate capabilities from user
\$user = new WP_User(123);
\$user->remove_cap('edit_users');
\$user->remove_cap('manage_options');
\$user->remove_cap('activate_plugins');

// Or reset to pure role
\$user->set_role('editor'); // Only editor perms",
					'capability_audit' => array(
						'1. List all users',
						'2. Check their role',
						'3. Verify role matches job',
						'4. Check for extra caps',
						'5. Remove inappropriate caps',
					),
					'preventing_escalation' => array(
						'Never manually add edit_users to non-admin',
						'Use roles properly',
						'Audit capabilities quarterly',
						'Monitor for changes',
					),
					'recommendation' => __( 'CRITICAL: Remove admin capabilities from non-admin users immediately', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: Anonymous users accessing admin pages
		return null;
	}
}
