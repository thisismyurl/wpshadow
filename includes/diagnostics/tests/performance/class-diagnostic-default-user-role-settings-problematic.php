<?php
/**
 * Default User Role Settings Problematic Diagnostic
 *
 * Tests for default user role configuration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default User Role Settings Problematic Diagnostic Class
 *
 * Tests for default user role configuration security.
 *
 * @since 1.6033.0000
 */
class Diagnostic_Default_User_Role_Settings_Problematic extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'default-user-role-settings-problematic';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Default User Role Settings Problematic';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for default user role configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check default role setting.
		$default_role = get_option( 'default_role' );

		if ( empty( $default_role ) ) {
			$issues[] = __( 'No default role is set for new users', 'wpshadow' );
		} elseif ( $default_role === 'administrator' ) {
			$issues[] = __( 'Critical: New users are set to Administrator role - major security issue', 'wpshadow' );
		} elseif ( $default_role === 'editor' ) {
			$issues[] = __( 'New users are set to Editor role - consider using Contributor for public registration', 'wpshadow' );
		}

		// Check if role exists.
		if ( ! empty( $default_role ) ) {
			$all_roles = wp_roles()->get_names();

			if ( ! isset( $all_roles[ $default_role ] ) ) {
				$issues[] = sprintf(
					/* translators: %s: role name */
					__( 'Default role (%s) does not exist', 'wpshadow' ),
					$default_role
				);
			}
		}

		// Check for role capabilities.
		if ( ! empty( $default_role ) ) {
			$role = get_role( $default_role );

			if ( $role ) {
				// Check if new user role can publish.
				if ( $role->has_cap( 'publish_posts' ) ) {
					$issues[] = sprintf(
						/* translators: %s: role name */
						__( '%s role can publish posts - consider if this is intended', 'wpshadow' ),
						$default_role
					);
				}

				// Check if new user role can manage settings.
				if ( $role->has_cap( 'manage_options' ) ) {
					$issues[] = __( 'Default role has manage_options capability - security risk', 'wpshadow' );
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/default-user-role-settings-problematic',
			);
		}

		return null;
	}
}
