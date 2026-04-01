<?php
/**
 * Editor Capability Scope Diagnostic
 *
 * Validates that editor role has appropriate capabilities without
 * administrative permissions that could pose security risks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Editor Capability Scope Diagnostic Class
 *
 * Checks editor role capability configuration.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Editor_Capability_Scope extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'editor-capability-scope';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Editor Capability Scope';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates editor role capability restrictions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$roles  = wp_roles()->roles;

		// Check if editor role exists.
		if ( ! isset( $roles['editor'] ) ) {
			$issues[] = __( 'Editor role is missing (this is unusual)', 'wpshadow' );
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Critical: Editor role is missing.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'details'      => array(
					'issues'         => $issues,
					'recommendation' => __( 'Recreate the editor role with default WordPress capabilities.', 'wpshadow' ),
				),
			);
		}

		$editor_caps = $roles['editor']['capabilities'];

		// Editors should NOT have these administrative capabilities.
		$forbidden_caps = array(
			'manage_options',
			'edit_users',
			'delete_users',
			'create_users',
			'promote_users',
			'install_plugins',
			'activate_plugins',
			'update_plugins',
			'delete_plugins',
			'install_themes',
			'update_themes',
			'delete_themes',
			'edit_themes',
			'edit_plugins',
			'update_core',
			'import',
			'export',
		);

		$has_forbidden = array();
		foreach ( $forbidden_caps as $cap ) {
			if ( ! empty( $editor_caps[ $cap ] ) ) {
				$has_forbidden[] = $cap;
			}
		}

		if ( ! empty( $has_forbidden ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of capabilities */
				__( 'Editors have administrative capabilities: %s', 'wpshadow' ),
				implode( ', ', $has_forbidden )
			);
		}

		// Editors should have these content management capabilities.
		$expected_caps = array(
			'edit_posts',
			'edit_others_posts',
			'publish_posts',
			'delete_posts',
			'delete_others_posts',
			'edit_pages',
			'edit_others_pages',
			'publish_pages',
			'delete_pages',
			'moderate_comments',
			'manage_categories',
			'upload_files',
		);

		$missing_caps = array();
		foreach ( $expected_caps as $cap ) {
			if ( empty( $editor_caps[ $cap ] ) ) {
				$missing_caps[] = $cap;
			}
		}

		if ( ! empty( $missing_caps ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated list of capabilities */
				__( 'Editors lack expected capabilities: %s', 'wpshadow' ),
				implode( ', ', $missing_caps )
			);
		}

		// Get editors.
		$editors = get_users(
			array(
				'role'   => 'editor',
				'fields' => array( 'ID', 'user_login', 'user_email' ),
			)
		);

		if ( empty( $editors ) ) {
			$issues[] = __( 'No editors assigned (content management may be difficult)', 'wpshadow' );
		}

		// Check for editors with multiple roles.
		$multi_role_editors = array();
		foreach ( $editors as $user ) {
			$user_obj = new \WP_User( $user->ID );
			if ( count( $user_obj->roles ) > 1 ) {
				$multi_role_editors[] = array(
					'user_login' => $user->user_login,
					'roles'      => $user_obj->roles,
				);
			}
		}

		if ( ! empty( $multi_role_editors ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of editors with multiple roles */
				__( '%d editors have multiple roles (verify intentional)', 'wpshadow' ),
				count( $multi_role_editors )
			);
		}

		// Check editor activity.
		global $wpdb;
		$inactive_editors = array();

		foreach ( $editors as $user ) {
			// Check last post edit.
			$last_edit = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT MAX(post_modified) FROM {$wpdb->posts} WHERE post_author = %d",
					$user->ID
				)
			);

			if ( $last_edit ) {
				$days_inactive = ( time() - strtotime( $last_edit ) ) / DAY_IN_SECONDS;
				if ( $days_inactive > 180 ) {
					$inactive_editors[] = array(
						'user_login'   => $user->user_login,
						'days_inactive' => absint( $days_inactive ),
					);
				}
			}
		}

		if ( count( $inactive_editors ) > 2 ) {
			$issues[] = sprintf(
				/* translators: %d: number of inactive editors */
				__( '%d editors have not edited content in 180+ days (consider role review)', 'wpshadow' ),
				count( $inactive_editors )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of editor capability issues */
					__( 'Found %d editor role capability issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'details'      => array(
					'issues'             => $issues,
					'editor_count'       => count( $editors ),
					'multi_role_editors' => $multi_role_editors,
					'inactive_editors'   => array_slice( $inactive_editors, 0, 10 ),
					'recommendation'     => __( 'Ensure editors have content management capabilities but not administrative permissions.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
