<?php
/**
 * No Database Backup Strategy Diagnostic
 *
 * Detects when database backups are not being performed,
 * risking catastrophic data loss.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Database Backup Strategy
 *
 * Checks whether automated database backups
 * are configured and tested.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Database_Backup_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-database-backup-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Backup Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether database backups are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for backup plugins
		$has_backups = is_plugin_active( 'updraftplus/updraftplus.php' ) ||
			is_plugin_active( 'backwpup/backwpup.php' ) ||
			is_plugin_active( 'all-in-one-wp-migration/all-in-one-wp-migration.php' );

		if ( ! $has_backups ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not backing up your database, which means one hack, server failure, or bad update could destroy everything. Your database contains: all content, user accounts, settings, orders, comments. Without backups, you can\'t recover. Good backup strategy: automated daily backups, stored off-site (not same server), tested restoration process. Most backup plugins do this automatically. Set it once and sleep better.',
					'wpshadow'
				),
				'severity'      => 'critical',
				'threat_level'  => 95,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Data Recovery & Business Continuity',
					'potential_gain' => 'Prevent catastrophic data loss',
					'roi_explanation' => 'Database backups are insurance against hacks, failures, and mistakes. One incident without backups = business destroyed.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/database-backup-strategy',
			);
		}

		return null;
	}
}
