<?php
/**
 * Diagnostic: WordPress Roles Existence
 *
 * Checks if WordPress standard roles exist and are properly registered.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Configuration
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Wp_Roles_Existence
 *
 * Tests if standard WordPress roles are registered.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Wp_Roles_Existence extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'wp-roles-existence';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Roles Existence';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if WordPress standard roles are properly registered';

	/**
	 * Check WordPress roles.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wp_roles;

		if ( ! isset( $wp_roles ) || ! is_object( $wp_roles ) ) {
			$wp_roles = new \WP_Roles();
		}

		// Standard WordPress roles.
		$standard_roles = array( 'administrator', 'editor', 'author', 'contributor', 'subscriber' );
		$missing_roles  = array();

		foreach ( $standard_roles as $role ) {
			if ( ! isset( $wp_roles->roles[ $role ] ) ) {
				$missing_roles[] = $role;
			}
		}

		if ( ! empty( $missing_roles ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: Comma-separated list of missing roles */
					__( 'WordPress standard roles are missing: %s. Reinitialize roles or restore from a backup.', 'wpshadow' ),
					implode( ', ', $missing_roles )
				),
				'severity'    => 'high',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp_roles_existence',
				'meta'        => array(
					'missing_roles' => $missing_roles,
				),
			);
		}

		return null;
	}
}
