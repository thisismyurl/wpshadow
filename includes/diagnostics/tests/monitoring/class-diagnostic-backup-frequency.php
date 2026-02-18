<?php
/**
 * Backup Frequency Diagnostic
 *
 * Analyzes backup schedule and last backup status.
 *
 * @since   1.6033.2150
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Backup Frequency Diagnostic
 *
 * Evaluates backup configuration and schedule adequacy.
 *
 * @since 1.6033.2150
 */
class Diagnostic_Backup_Frequency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'backup-frequency';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Backup Frequency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes backup schedule and last backup status';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'backup';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2150
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for backup plugins
		$backup_plugins = array(
			'updraftplus/updraftplus.php'           => 'UpdraftPlus',
			'backwpup/backwpup.php'                 => 'BackWPup',
			'duplicator/duplicator.php'             => 'Duplicator',
			'all-in-one-wp-migration/all-in-one-wp-migration.php' => 'All-in-One WP Migration',
			'jetpack/jetpack.php'                   => 'Jetpack Backup',
		);

		$active_backup = null;
		foreach ( $backup_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_backup = $name;
				break;
			}
		}

		// Check last backup time (UpdraftPlus)
		$last_backup_time = get_option( 'updraft_last_backup' );
		$days_since_backup = $last_backup_time ? floor( ( time() - $last_backup_time ) / DAY_IN_SECONDS ) : 999;

		// Check scheduled backups
		$has_scheduled_backup = false;
		if ( $active_backup === 'UpdraftPlus' ) {
			$has_scheduled_backup = get_option( 'updraft_interval' ) !== 'manual';
		}

		// Estimate content update frequency
		$post_count = wp_count_posts()->publish ?? 0;
		$recent_posts = get_posts( array(
			'posts_per_page' => -1,
			'date_query'     => array(
				array( 'after' => '30 days ago' ),
			),
			'fields'         => 'ids',
		) );
		$monthly_post_rate = count( $recent_posts );
		$is_active_site = $monthly_post_rate > 4; // More than 1 post per week

		// Generate findings if no backup configured
		if ( ! $active_backup ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No backup plugin detected. Automated backups essential for disaster recovery.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-frequency',
				'meta'         => array(
					'active_backup'     => $active_backup,
					'recommendation'    => 'Install UpdraftPlus or BackWPup',
					'backup_importance' => array(
						'Protects against data loss',
						'Enables quick site restoration',
						'Guards against security breaches',
						'Prevents accidental deletion',
						'Facilitates site migrations',
					),
					'backup_options'    => array(
						'UpdraftPlus (free + premium)',
						'BackWPup (free)',
						'Jetpack Backup (paid)',
						'ManageWP (centralized backups)',
					),
					'backup_targets'    => 'Files + database both required',
				),
			);
		}

		// Alert if backup overdue
		if ( $days_since_backup > 7 && $is_active_site ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: days since last backup */
					__( 'Last backup was %d days ago. Active sites should backup weekly minimum.', 'wpshadow' ),
					$days_since_backup
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-frequency',
				'meta'         => array(
					'days_since_backup'   => $days_since_backup,
					'active_backup'       => $active_backup,
					'is_active_site'      => $is_active_site,
					'monthly_post_rate'   => $monthly_post_rate,
					'recommendation'      => 'Configure automated daily or weekly backups',
					'frequency_guideline' => array(
						'E-commerce sites: Daily',
						'Active blogs: Weekly',
						'Static sites: Monthly',
						'Before updates: Always',
					),
				),
			);
		}

		// Warning if no scheduled backups
		if ( ! $has_scheduled_backup && $is_active_site ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No scheduled backups configured. Manual backups often forgotten - automate for reliability.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-frequency',
				'meta'         => array(
					'has_scheduled_backup' => $has_scheduled_backup,
					'active_backup'        => $active_backup,
					'recommendation'       => 'Enable scheduled backups in plugin settings',
				),
			);
		}

		return null;
	}
}
