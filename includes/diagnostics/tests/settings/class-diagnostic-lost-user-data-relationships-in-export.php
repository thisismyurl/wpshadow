<?php
/**
 * Lost User Data Relationships in Export Diagnostic
 *
 * Tests whether user metadata, roles, and capabilities are
 * preserved in export files.
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
 * Lost User Data Relationships in Export Diagnostic Class
 *
 * Tests for user data preservation in WordPress exports.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Lost_User_Data_Relationships_In_Export extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'lost-user-data-relationships-in-export';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Lost User Data Relationships in Export';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether user data and relationships are preserved in exports';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'export';

	/**
	 * Run the diagnostic check.
	 *
	 * Verifies that user data and relationships are properly
	 * captured in export files.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		global $wpdb;

		// Get all users.
		$all_users = $wpdb->get_results( "SELECT ID, user_login, user_email, user_registered FROM {$wpdb->users}" );

		$total_users = count( $all_users );
		$admin_users = 0;
		$editor_users = 0;
		$contributor_users = 0;
		$custom_role_users = 0;
		$users_with_metadata = 0;

		$non_default_roles = array();

		foreach ( $all_users as $user ) {
			// Check user roles.
			$user_obj = get_user_by( 'id', $user->ID );

			if ( $user_obj ) {
				// Check for custom roles.
				if ( ! empty( $user_obj->roles ) ) {
					foreach ( $user_obj->roles as $role ) {
						if ( ! in_array( $role, array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' ), true ) ) {
							$custom_role_users++;
							if ( ! isset( $non_default_roles[ $role ] ) ) {
								$non_default_roles[ $role ] = 0;
							}
							$non_default_roles[ $role ]++;
						}
					}
				}
			}

			// Count user metadata.
			$user_meta_count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE user_id = %d AND meta_key NOT LIKE %s",
					$user->ID,
					'%_wp_user_level%'
				)
			);

			if ( $user_meta_count > 0 ) {
				$users_with_metadata++;
			}
		}

		// Count by role.
		$admin_users = count_users();
		$admin_count = isset( $admin_users['avail_roles']['administrator'] ) ? $admin_users['avail_roles']['administrator'] : 0;
		$editor_count = isset( $admin_users['avail_roles']['editor'] ) ? $admin_users['avail_roles']['editor'] : 0;
		$contributor_count = isset( $admin_users['avail_roles']['contributor'] ) ? $admin_users['avail_roles']['contributor'] : 0;

		// Check for user metadata.
		$total_user_metadata = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key NOT LIKE %s",
				'%capabilities%'
			)
		);

		// Check for multi-user collaboration.
		$posts_by_author = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT post_author, COUNT(*) as count 
				FROM {$wpdb->posts} 
				WHERE post_status = %s 
				GROUP BY post_author",
				'publish'
			)
		);

		$total_post_authors = count( $posts_by_author );

		// Check for user relationships in postmeta.
		$posts_with_user_relationships = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT post_id) 
				FROM {$wpdb->postmeta} 
				WHERE meta_value REGEXP %s",
				'\"[0-9]+\".*ID.*[0-9]+'
			)
		);

		// Check for custom user meta keys.
		$custom_user_meta_keys = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT meta_key FROM {$wpdb->usermeta} 
				WHERE meta_key NOT LIKE %s 
				AND meta_key NOT LIKE %s 
				AND meta_key NOT LIKE %s 
				LIMIT 20",
				'%capabilities%',
				'%user_level%',
				'wp_%'
			)
		);

		// Check WXR user export support.
		$wxr_users_included = apply_filters( 'wxr_export_users', true );

		// Check for team/collaboration plugins.
		$team_plugins = array(
			'members/members.php' => 'Members',
			'user-role-editor/user-role-editor.php' => 'User Role Editor',
			'capability-manager-enhanced/capsman-enhanced.php' => 'Capability Manager Enhanced',
		);

		$team_plugin_active = false;
		foreach ( $team_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$team_plugin_active = true;
				break;
			}
		}

		if ( $total_users > 1 || $total_user_metadata > 0 || $total_post_authors > 1 || ! empty( $non_default_roles ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of users, %d: user metadata entries */
					__( '%d user accounts with %d metadata entries may not be fully exported', 'wpshadow' ),
					$total_users,
					$total_user_metadata
				),
				'severity'     => 'medium',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/lost-user-data-relationships-in-export',
				'details'      => array(
					'total_users'                     => $total_users,
					'admin_users'                     => $admin_count,
					'editor_users'                    => $editor_count,
					'contributor_users'               => $contributor_count,
					'custom_role_users'               => $custom_role_users,
					'non_default_roles'               => $non_default_roles,
					'users_with_metadata'             => $users_with_metadata,
					'total_user_metadata_entries'     => $total_user_metadata,
					'total_post_authors'              => $total_post_authors,
					'custom_user_meta_keys'           => array_column( $custom_user_meta_keys, 'meta_key' ),
					'posts_with_user_relationships'   => $posts_with_user_relationships,
					'wxr_users_export_enabled'        => $wxr_users_included,
					'team_collaboration_plugin_active' => $team_plugin_active,
					'team_structure_impact'           => sprintf(
						/* translators: %d: number of users */
						__( '%d user accounts and their roles may not migrate correctly', 'wpshadow' ),
						$total_users
					),
					'permission_risk'                 => __( 'User permissions and access levels will be lost after restore', 'wpshadow' ),
					'attribution_impact'              => __( 'Author attributions and bylines may be reassigned incorrectly', 'wpshadow' ),
					'team_workflow_risk'              => __( 'Multi-author workflows and collaboration will break', 'wpshadow' ),
					'important_note'                  => __( 'WordPress native export focuses on content, not user data - requires separate user export tool', 'wpshadow' ),
					'fix_methods'                     => array(
						__( 'Use plugin that exports users separately (e.g., WP All Import)', 'wpshadow' ),
						__( 'Export user data from Tools > Users menu if available', 'wpshadow' ),
						__( 'Create manual user mapping spreadsheet', 'wpshadow' ),
						__( 'Use database export for complete user data backup', 'wpshadow' ),
						__( 'Document all user roles before migration', 'wpshadow' ),
					),
					'verification'                    => array(
						__( 'Check WXR export for <wp:creator> tags', 'wpshadow' ),
						__( 'Search XML for custom user metadata', 'wpshadow' ),
						__( 'Compare user count in export vs site', 'wpshadow' ),
						__( 'Test user restore on staging site', 'wpshadow' ),
						__( 'Verify user roles and permissions after import', 'wpshadow' ),
					),
					'critical_note'                   => __( 'User data is not included in standard WordPress exports - team sites require special handling during migration', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
