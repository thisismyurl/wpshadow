<?php
/**
 * Role-Based Content Access Diagnostic
 *
 * Validates that role-based content restriction mechanisms are properly
 * configured and enforced. Checks for content visibility plugins and
 * custom capability implementations.
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
 * Role-Based Content Access Diagnostic Class
 *
 * Checks role-based content restriction configurations.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Role_Based_Content_Access extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'role-based-content-access';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Role-Based Content Access';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates role-based content restrictions';

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

		// Check if membership plugins are active.
		$membership_plugins = array(
			'members/members.php'                       => 'Members',
			'restrict-content-pro/restrict-content-pro.php' => 'Restrict Content Pro',
			'paid-memberships-pro/paid-memberships-pro.php' => 'Paid Memberships Pro',
			'user-role-editor/user-role-editor.php'     => 'User Role Editor',
		);

		$has_membership_plugin = false;
		foreach ( $membership_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$has_membership_plugin = true;
				break;
			}
		}

		// Check for posts with custom role/capability metadata.
		$posts_with_restrictions = get_posts(
			array(
				'post_type'   => 'any',
				'post_status' => 'publish',
				'numberposts' => -1,
				'meta_query'  => array(
					'relation' => 'OR',
					array(
						'key'     => '_restrict_content',
						'compare' => 'EXISTS',
					),
					array(
						'key'     => '_access_level',
						'compare' => 'EXISTS',
					),
					array(
						'key'     => '_required_role',
						'compare' => 'EXISTS',
					),
				),
			)
		);

		// If content restrictions exist but no plugin, warn.
		if ( ! empty( $posts_with_restrictions ) && ! $has_membership_plugin ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with restrictions */
				__( '%d posts have role-based restrictions but no membership plugin is active.', 'wpshadow' ),
				count( $posts_with_restrictions )
			);
		}

		// Check if any posts have public content that should be restricted.
		global $wpdb;
		$public_posts_with_login = $wpdb->get_var(
			"SELECT COUNT(*)
			FROM {$wpdb->posts}
			WHERE post_status = 'publish'
			AND post_content LIKE '%[login]%'
			OR post_content LIKE '%[member]%'"
		);

		if ( $public_posts_with_login > 0 && ! $has_membership_plugin ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts with shortcodes */
				__( '%d posts use membership shortcodes but no membership plugin is active.', 'wpshadow' ),
				$public_posts_with_login
			);
		}

		// Check for proper capability definitions in custom roles.
		$roles = wp_roles()->roles;
		foreach ( $roles as $role_slug => $role_data ) {
			if ( ! in_array( $role_slug, array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' ), true ) ) {
				// Custom role - check if it has read capability.
				if ( empty( $role_data['capabilities']['read'] ) ) {
					$issues[] = sprintf(
						/* translators: %s: role name */
						__( 'Custom role "%s" lacks basic read capability.', 'wpshadow' ),
						$role_slug
					);
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of role-based access issues */
					__( 'Found %d role-based content access issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'details'      => array(
					'issues'         => $issues,
					'recommendation' => __( 'Install a membership plugin like Members or ensure custom implementations are properly configured.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
