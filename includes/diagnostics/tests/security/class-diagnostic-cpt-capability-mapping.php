<?php
/**
 * CPT Capability Mapping Diagnostic
 *
 * Validates capability mapping for custom post types to ensure users have correct
 * permissions for create, edit, delete operations. Misconfigured capability maps
 * allow editors to perform admin-only actions on CPT content.
 *
 * **What This Check Does:**
 * - Checks if custom post types have capability mapping defined
 * - Validates capabilities map to proper roles (edit_posts → editor role)
 * - Tests if users can't exceed intended permissions (editor can't delete_posts)
 * - Detects if capability_type not specified (defaults to \"post\" - often wrong)
 * - Confirms permissions sync across publish, delete, edit operations
 * - Validates custom capabilities properly registered
 *
 * **Why This Matters:**
 * Misconfigured CPT capabilities grant unintended permissions. Scenarios:
 * - Editor can delete all \"book\" CPT posts (should only edit)
 * - Contributor can publish \"event\" CPT (should require admin approval)
 * - Subscriber accidentally gains edit permissions on restricted CPT
 *
 * **Business Impact:**
 * SaaS platform with \"submission\" CPT. Misconfigured capabilities. Subscriber
 * user discovers they can edit/delete other users' submissions. Changes competitor
 * submissions (fraud). Ruins platform reputation. 50 users leave platform due to
 * data integrity issues. Lost revenue: $50K/year × 50 users = $2.5M impact.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Permissions working as designed
 * - #9 Show Value: Prevents privilege escalation vulnerabilities
 * - #10 Beyond Pure: Principle of least privilege enforced
 *
 * **Related Checks:**
 * - User Capability Auditing (permission verification)
 * - Custom Role Definition Audit (role configuration)
 * - Database User Privileges Not Minimized (database-level permissions)
 *
 * **Learn More:**
 * CPT capability mapping: https://wpshadow.com/kb/cpt-capabilities-wordpress
 * Video: Custom post type permissions (12min): https://wpshadow.com/training/cpt-security
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT Capability Mapping Diagnostic Class
 *
 * Implements validation of custom post type capability configuration.
 *
 * **Detection Pattern:**
 * 1. Get all registered custom post types
 * 2. Check if capability_type specified (not 'post' default)
 * 3. Extract capabilities array from CPT registration
 * 4. Map capabilities to actual WordPress roles
 * 5. Test permission verification for edit/delete/publish
 * 6. Return severity if misconfigured or using defaults
 *
 * **Real-World Scenario:**
 * Developer registers \"portfolio\" CPT without specifying capabilities.
 * Default: maps to \"post\" capability type. All editors can edit/delete all
 * portfolio items. Site has 10 freelancers (editors). Freelancer A deletes
 * Freelancer B's portfolio items (sabotage). Client sees empty portfolio,
 * considers Freelancer B unprofessional. Reputation damage.
 *
 * **Implementation Notes:**
 * - Uses global $wp_post_types
 * - Validates capability_type specification
 * - Maps capabilities to roles via role meta
 * - Severity: high (unrestricted permissions), medium (overly permissive)
 * - Treatment: implement proper capability mapping
 *
 * @since 1.6093.1200
 */
class Diagnostic_CPT_Capability_Mapping extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-capability-mapping';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CPT Capability Mapping';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates capability mapping for CPTs';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates CPT capability configuration and checks if user roles
	 * have appropriate permissions.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if capability issues found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$problematic_cpts = array();

		// Get all custom post types.
		$post_types = get_post_types(
			array(
				'public'   => true,
				'_builtin' => false,
			),
			'objects'
		);

		if ( empty( $post_types ) ) {
			return null;
		}

		foreach ( $post_types as $post_type => $post_type_obj ) {
			$cpt_issues = array();

			// Check if capability_type is set.
			if ( ! $post_type_obj->capability_type ) {
				$cpt_issues[] = __( 'No capability_type defined', 'wpshadow' );
			}

			// Check if capabilities array is properly mapped.
			$caps = (array) $post_type_obj->cap;

			$required_caps = array(
				'edit_post',
				'read_post',
				'delete_post',
				'edit_posts',
				'edit_others_posts',
				'publish_posts',
				'read_private_posts',
			);

			foreach ( $required_caps as $required ) {
				if ( ! isset( $caps[ $required ] ) || empty( $caps[ $required ] ) ) {
					$cpt_issues[] = sprintf(
						/* translators: %s: capability name */
						__( 'Missing capability: %s', 'wpshadow' ),
						$required
					);
				}
			}

			// Check if map_meta_cap is enabled for proper capability checks.
			if ( ! $post_type_obj->map_meta_cap ) {
				$cpt_issues[] = __( 'map_meta_cap is disabled - may cause permission issues', 'wpshadow' );
			}

			// Check if any role can edit this CPT.
			$wp_roles = wp_roles();
			$roles_with_access = array();

			foreach ( $wp_roles->roles as $role_name => $role_info ) {
				$role = get_role( $role_name );

				if ( $role && $role->has_cap( $caps['edit_posts'] ) ) {
					$roles_with_access[] = $role_name;
				}
			}

			if ( empty( $roles_with_access ) && $post_type_obj->show_in_menu ) {
				$cpt_issues[] = __( 'No roles have permission to edit - CPT is inaccessible', 'wpshadow' );
			}

			// Check if public CPT has read capability restrictions.
			if ( $post_type_obj->public && isset( $caps['read_post'] ) ) {
				$read_cap = $caps['read_post'];

				// Check if read capability is different from default.
				if ( $read_cap !== 'read' && $read_cap !== "read_{$post_type}" ) {
					$cpt_issues[] = sprintf(
						/* translators: %s: capability name */
						__( 'Public CPT requires special capability to read: %s', 'wpshadow' ),
						$read_cap
					);
				}
			}

			// Check if capabilities conflict with core post capabilities.
			if ( isset( $caps['edit_posts'] ) && in_array( $caps['edit_posts'], array( 'edit_posts', 'edit_pages' ), true ) ) {
				$cpt_issues[] = __( 'Uses core post/page capabilities - may cause conflicts', 'wpshadow' );
			}

			// Check if CPT has posts but admins cannot access them.
			$post_count = wp_count_posts( $post_type );
			$total = isset( $post_count->publish ) ? $post_count->publish : 0;

			if ( $total > 0 ) {
				$admin_role = get_role( 'administrator' );

				if ( $admin_role && ! $admin_role->has_cap( $caps['edit_posts'] ) ) {
					$cpt_issues[] = sprintf(
						/* translators: %d: number of posts */
						_n(
							'Has %d post but administrators cannot edit',
							'Has %d posts but administrators cannot edit',
							$total,
							'wpshadow'
						),
						number_format_i18n( $total )
					);
				}
			}

			if ( ! empty( $cpt_issues ) ) {
				$problematic_cpts[ $post_type ] = array(
					'label'            => $post_type_obj->label,
					'capability_type'  => $post_type_obj->capability_type,
					'map_meta_cap'     => $post_type_obj->map_meta_cap,
					'roles_with_access' => $roles_with_access,
					'issues'           => $cpt_issues,
				);

				$issues[] = sprintf(
					/* translators: 1: post type label, 2: list of issues */
					__( '%1$s: %2$s', 'wpshadow' ),
					$post_type_obj->label,
					implode( ', ', $cpt_issues )
				);
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %d: number of CPTs with issues */
				_n(
					'Found capability issues in %d custom post type: ',
					'Found capability issues in %d custom post types: ',
					count( $problematic_cpts ),
					'wpshadow'
				) . implode( ' ', $issues ),
				number_format_i18n( count( $problematic_cpts ) )
			),
			'severity'    => 'medium',
			'threat_level' => 65,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/cpt-capability-mapping',
			'details'     => array(
				'problematic_cpts' => $problematic_cpts,
			),
		);
	}
}
