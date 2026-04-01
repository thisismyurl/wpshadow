<?php
/**
 * Backup Strategy Not Documented Diagnostic
 *
 * Checks if backup strategy is documented.
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
 * Backup Strategy Not Documented Diagnostic Class
 *
 * Detects undocumented backup strategy.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Backup_Strategy_Not_Documented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-strategy-not-documented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Strategy Not Documented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if backup strategy is documented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if backup documentation exists
		if ( ! get_option( 'backup_strategy_documented' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Backup strategy is not documented. Document your backup procedures, frequency, retention, and recovery process.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/backup-strategy-not-documented?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
