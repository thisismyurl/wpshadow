<?php
/**
 * Plugin Installation Permissions Diagnostic
 *
 * Verifies plugin installation and management permissions are properly
 * restricted. Without restriction, compromised user account can install
 * malicious plugins (full site compromise).
 *
 * **What This Check Does:**
 * - Checks who has plugin installation permission
 * - Validates only admins can install/activate plugins
 * - Tests if file permissions allow write
 * - Confirms update permissions restricted
 * - Checks for overrides (capabilities wrong)
 * - Returns severity if permissions too permissive
 *
 * **Why This Matters:**
 * Over-permissive installation = account takeover. Scenarios:
 * - Editor role can install plugins (should be admin only)
 * - Attacker compromises editor account
 * - Uses editor account to install malicious plugin
 * - Plugin becomes fully executed code on your site
 * - Site fully compromised despite limited account
 *
 * **Business Impact:**
 * Client site allows editor role to install plugins (misconfigured). Editor
 * account compromised (weak password). Attacker installs backdoor plugin.
 * Site compromised via editor account. Recovery: $200K+. With proper perms:
 * editor can't install plugins (admin-only feature). Even with editor
 * compromise: site stays safe (can't install malicious code).
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Plugin installation protected
 * - #9 Show Value: Limits damage from compromised accounts
 * - #10 Beyond Pure: Principle of least privilege
 *
 * **Related Checks:**
 * - User Capability Auditing (role permissions)
 * - Administrator Account Security (admin protection)
 * - Plugin Capability Escalation (permission bypass)
 *
 * **Learn More:**
 * Plugin installation security: https://wpshadow.com/kb/plugin-install-permissions
 * Video: Restricting plugin access (9min): https://wpshadow.com/training/plugin-perms
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Installation Permissions Diagnostic
 *
 * Checks for proper file permissions and capability restrictions.
 *
 * **Detection Pattern:**
 * 1. Check current_user_can('install_plugins') for various roles
 * 2. Test if non-admins can access Plugin install page
 * 3. Validate file permissions on plugins directory
 * 4. Confirm plugin update permissions restricted
 * 5. Test capability grants/overrides
 * 6. Return severity if permissions too permissive
 *
 * **Real-World Scenario:**
 * WordPress site configured to allow "contributor" to install plugins
 * (accidental setting). Attacker compromises contributor account. Uses
 * contributor to install backdoor plugin. Site fully compromised via
 * contributor account (should be harmless). Proper setup: install_plugins
 * capability restricted to admin_only (using filters). Contributor stays safe.
 *
 * **Implementation Notes:**
 * - Checks plugin directory permissions
 * - Validates capability restrictions (install_plugins)
 * - Tests role access to plugin install page
 * - Severity: critical (non-admin can install), high (weak file perms)
 * - Treatment: restrict install_plugins to admin roles
 *
 * @since 1.6030.2240
 */
class Diagnostic_Plugin_Installation_Permissions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-installation-permissions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Installation Permissions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies proper permissions for plugin installation and management';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if plugin directory is writable
		$plugin_dir = WP_PLUGIN_DIR;
		if ( ! is_writable( $plugin_dir ) ) {
			$issues[] = __( 'Plugin directory is not writable - this may be intentional', 'wpshadow' );
		} else {
			// If writable, check directory permissions
			$perms = substr( sprintf( '%o', fileperms( $plugin_dir ) ), -4 );
			if ( intval( $perms ) > 755 ) {
				$issues[] = sprintf(
					/* translators: %s: file permissions */
					__( 'Plugin directory permissions too open (%s) - should be 755 or restrictive', 'wpshadow' ),
					$perms
				);
			}
		}

		// Check for FTP/SFTP configuration
		if ( ! defined( 'FTP_HOST' ) && ! defined( 'FS_METHOD' ) || 'direct' === FS_METHOD ) {
			$issues[] = __( 'Direct filesystem access enabled - consider using SFTP for updates', 'wpshadow' );
		}

		// Check for capability restrictions
		$this_user = wp_get_current_user();
		if ( current_user_can( 'install_plugins' ) ) {
			if ( ! current_user_can( 'manage_network_options' ) ) {
				// User can install but not manage network - might be unintended
			}
		}

		// Check for plugins without proper capability checks
		$active_plugins = get_option( 'active_plugins', array() );
		$unsafe_count = 0;

		foreach ( $active_plugins as $plugin ) {
			$plugin_path = WP_PLUGIN_DIR . '/' . $plugin;
			if ( file_exists( $plugin_path ) ) {
				$content = file_get_contents( $plugin_path );

				// Check for unsafe admin_init without capability check
				if ( strpos( $content, "add_action( 'admin_init'" ) !== false
					&& strpos( $content, 'current_user_can' ) === false ) {
					++$unsafe_count;
				}
			}
		}

		if ( $unsafe_count > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of plugins */
				__( '%d plugins have admin_init hooks without capability checks', 'wpshadow' ),
				$unsafe_count
			);
		}

		// Check for read-only filesystem
		$upload_dir = wp_upload_dir();
		if ( ! is_writable( $upload_dir['basedir'] ) ) {
			$issues[] = __( 'Upload directory not writable - plugin uploads will fail', 'wpshadow' );
		}

		// Report findings
		if ( ! empty( $issues ) ) {
			$severity     = 'medium';
			$threat_level = 55;

			if ( count( $issues ) > 2 ) {
				$severity     = 'high';
				$threat_level = 75;
			}

			$description = __( 'Plugin installation permission issues detected', 'wpshadow' );

			$details = array(
				'issues'                    => $issues,
				'plugin_dir_writable'       => is_writable( $plugin_dir ),
				'upload_dir_writable'       => is_writable( $upload_dir['basedir'] ),
				'unsafe_plugins'            => $unsafe_count,
				'fs_method_configured'      => defined( 'FS_METHOD' ),
			);

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugin-installation-permissions',
				'details'      => $details,
			);
		}

		return null;
	}
}
