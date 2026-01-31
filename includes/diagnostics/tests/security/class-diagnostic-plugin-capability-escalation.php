<?php
/**
 * Plugin Capability Escalation Diagnostic
 *
 * Detects plugins that grant excessive capabilities to users.
 *
 * @since   1.4031.1939
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_Capability_Escalation Class
 *
 * Identifies plugins granting excessive capabilities.
 */
class Diagnostic_Plugin_Capability_Escalation extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-capability-escalation';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Capability Escalation';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for plugins granting excessive capabilities to users';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.4031.1939
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$capability_concerns = array();

		// Get active plugins
		$active_plugins = get_option( 'active_plugins', array() );
		$plugins_dir    = WP_PLUGIN_DIR;

		foreach ( $active_plugins as $plugin ) {
			$plugin_file = $plugins_dir . '/' . $plugin;

			if ( ! file_exists( $plugin_file ) ) {
				continue;
			}

			$content = file_get_contents( $plugin_file );

			// Check for grant_super_admin or similar privilege escalation
			if ( preg_match( '/grant_super_admin|add_user_to_blog.*add_role|wp_update_user.*role.*administrator/', $content ) ) {
				$capability_concerns[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Grants super admin or administrator role to users.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for adding custom roles with too many capabilities
			if ( preg_match( '/add_role.*["\']custom["\'].*true/', $content ) ) {
				// Check if it grants manage_options
				if ( preg_match( '/manage_options|activate_plugins|update_core/', $content ) ) {
					$capability_concerns[] = sprintf(
						/* translators: %s: plugin name */
						__( '%s: Creates custom role with admin-level capabilities.', 'wpshadow' ),
						basename( dirname( $plugin_file ) )
					);
				}
			}

			// Check for making subscriber into editor/admin
			if ( preg_match( '/\[\s*["\']subscriber["\'].*\[\s*["\'](?:editor|administrator)["\']/', $content ) ) {
				$capability_concerns[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: May elevate subscriber role to editor or admin.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}

			// Check for giving manage_options to non-admins
			if ( preg_match( '/user->add_cap.*manage_options/', $content ) ) {
				$capability_concerns[] = sprintf(
					/* translators: %s: plugin name */
					__( '%s: Grants manage_options capability to non-admin users.', 'wpshadow' ),
					basename( dirname( $plugin_file ) )
				);
			}
		}

		if ( ! empty( $capability_concerns ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count, %s: details */
					__( '%d capability escalation concerns detected: %s', 'wpshadow' ),
					count( $capability_concerns ),
					implode( ' | ', array_slice( $capability_concerns, 0, 2 ) )
				),
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'details'      => array(
					'concerns' => $capability_concerns,
				),
				'kb_link'      => 'https://wpshadow.com/kb/capability-escalation',
			);
		}

		return null;
	}
}
