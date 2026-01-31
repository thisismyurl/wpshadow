<?php
/**
 * Update Safety Check Diagnostic
 *
 * Verifies Vault Light backups exist before WordPress updates.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26030.2000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Update Safety Check Diagnostic
 *
 * Ensures Vault Light backups are enabled before allowing updates.
 *
 * @since 1.26030.2000
 */
class Diagnostic_Update_Safety_Check extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'update-safety-check';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Update Safety Backup Check';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Ensures Vault Light backups are enabled before WordPress updates';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'utilities';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26030.2000
	 * @return array|null Finding array if backups not enabled, null otherwise.
	 */
	public static function check() {
		$backup_enabled = get_option( 'wpshadow_backup_enabled', true );

		if ( ! $backup_enabled ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Vault Light pre-treatment snapshots are disabled. Enable them to automatically create a backup before each update, giving you a quick restore point if something breaks.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/update-safety-check',
			);
		}

		return null;
	}
}
