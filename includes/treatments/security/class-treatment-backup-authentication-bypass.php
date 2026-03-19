<?php
/**
 * Backup Authentication Bypass Treatment
 *
 * Detects authentication bypass vulnerabilities in backup/restore
 * functionality and emergency access mechanisms.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backup Authentication Bypass Treatment Class
 *
 * Checks for:
 * - Backup files accessible without authentication
 * - Emergency admin access scripts
 * - Restore functionality without verification
 * - Backup URLs with predictable patterns
 * - Database dumps in web-accessible directories
 * - .sql files in /wp-content/
 *
 * Backup authentication bypass allows attackers to access sensitive
 * data or restore malicious backups without proper authentication.
 *
 * @since 1.6093.1200
 */
class Treatment_Backup_Authentication_Bypass extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'backup-authentication-bypass';

	/**
	 * The treatment title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'Backup Authentication Bypass';

	/**
	 * The treatment description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Detects authentication bypass in backup and restore functionality';

	/**
	 * The family this treatment belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * Scans for backup authentication vulnerabilities.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Backup_Authentication_Bypass' );
	}
}
