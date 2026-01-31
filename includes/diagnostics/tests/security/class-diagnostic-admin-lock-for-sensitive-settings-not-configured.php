<?php
/**
 * Admin Lock For Sensitive Settings Not Configured Diagnostic
 *
 * Checks if sensitive settings are locked.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Lock For Sensitive Settings Not Configured Diagnostic Class
 *
 * Detects unlocked sensitive settings.
 *
 * @since 1.2601.2352
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
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if sensitive settings are locked from editing
		if ( ! get_option( 'lock_core_file_edit' ) && ! defined( 'DISALLOW_FILE_EDIT' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Admin lock for sensitive settings is not configured. Disable file editing and lock sensitive settings to prevent unauthorized modifications.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/admin-lock-for-sensitive-settings-not-configured',
			);
		}

		return null;
	}
}
