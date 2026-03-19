<?php
/**
 * Admin Lock For Sensitive Settings Not Configured Diagnostic
 *
 * Checks if sensitive settings are locked.
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
 * Admin Lock For Sensitive Settings Not Configured Diagnostic Class
 *
 * Detects unlocked sensitive settings.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Admin_Lock_For_Sensitive_Settings_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-lock-for-sensitive-settings-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin Lock For Sensitive Settings Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if sensitive settings are locked';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if sensitive settings are locked from editing
		if ( ! get_option( 'lock_core_file_edit' ) && ! defined( 'DISALLOW_FILE_EDIT' ) ) {
			$finding = array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Admin lock for sensitive settings is not configured. Disable file editing and lock sensitive settings to prevent unauthorized modifications.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/admin-lock-for-sensitive-settings-not-configured',
				'context'       => array(
					'why'            => __( 'If admins can edit core files via WordPress dashboard, attackers with compromised admin credentials can inject malware directly into your codebase without FTP access. Verizon DBIR: 60% of breaches involved privileged access. PCI-DSS requires restricting file editing. Attackers leverage file editor to: Add backdoors, inject redirects, steal data, disable security plugins. A compromised editor means malware instantly.', 'wpshadow' ),
					'recommendation' => __( '1. Add to wp-config.php: define("DISALLOW_FILE_EDIT", true); - This removes file editor from dashboard completely\n2. Add to wp-config.php: define("DISALLOW_FILE_MODS", true); - Disables plugin/theme updates via dashboard (use FTP instead)\n3. Remove file editor: Settings > Plugins > All, search "Code Editor", deactivate if present\n4. Lock settings: Use define("DISALLOW_USER_PLUGIN_INSTALL", true); to prevent plugin installations\n5. Disable theme switching: define("DISALLOW_THEME_SELECTION", true);\n6. Verify plugins don\'t provide alternative editors (check Plugins > Installed for "Code" or "Editor")\n7. Set file permissions: chmod 755 /wp-content/plugins, chmod 755 /wp-content/themes\n8. Log all attempts: Monitor wp_admin_notice hooks for file edit attempts\n9. Use Content Security Policy: Prevent inline script injection attempts\n10. Regular audits: Monthly check that core files haven\'t been modified (use SFTP diff)', 'wpshadow' ),
				),
			);
			$finding = Upgrade_Path_Helper::add_upgrade_path( $finding, 'security', 'admin-security', 'file-edit-locking' );
			return $finding;
		}

		return null;
	}
}
