<?php
/**
 * Backup Frequency Diagnostic
 *
 * Checks if backups run frequently enough.
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
 * Backup Frequency Diagnostic Class
 *
 * Verifies backups run often enough for site activity level.
 * Like checking how often you save your work.
 *
 * @since 0.6093.1200
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
	protected static $description = 'Checks if backups run frequently enough';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'backup';

	/**
	 * Run the backup frequency diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if backup frequency issues detected, null otherwise.
	 */
	public static function check() {
		// Get site activity level.
		$posts_last_30_days = self::get_posts_last_n_days( 30 );
		$comments_last_30_days = self::get_comments_last_n_days( 30 );

		// Determine activity level.
		$is_high_activity = ( $posts_last_30_days > 30 || $comments_last_30_days > 100 );
		$is_medium_activity = ( $posts_last_30_days > 5 || $comments_last_30_days > 20 );

		// Check backup configuration.
		$backup_frequency = null;
		$last_backup = null;

		// UpdraftPlus.
		if ( class_exists( 'UpdraftPlus' ) ) {
			$schedule = get_option( 'updraft_interval', '' );
			$last_backup_info = get_option( 'updraft_last_backup', array() );
			$last_backup = $last_backup_info['db'] ?? 0;

			$backup_frequency = $schedule;
		}

		// BackWPup.
		if ( class_exists( 'BackWPup' ) && class_exists( 'BackWPup_Option' ) ) {
			$jobs = \BackWPup_Option::get_job_ids();
			if ( ! empty( $jobs ) ) {
				foreach ( $jobs as $job_id ) {
					$schedule = \BackWPup_Option::get( $job_id, 'schedule_type' );
					if ( 'none' !== $schedule ) {
						$backup_frequency = $schedule;
						break;
					}
				}
			}
		}

		// If no backup frequency detected.
		if ( null === $backup_frequency ) {
			return array(
				'id'           => self::$slug . '-unknown',
				'title'        => __( 'Backup Frequency Unknown', 'wpshadow' ),
				'description'  => __( 'We couldn\'t determine your backup schedule (like not knowing how often you save your work). Log into your backup plugin to verify automatic backups are scheduled. For active sites, daily backups are recommended; for low-activity sites, weekly is okay.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/backup-frequency?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'activity_level' => $is_high_activity ? 'high' : ( $is_medium_activity ? 'medium' : 'low' ),
				),
			);
		}

		// Check if last backup is recent.
		if ( $last_backup ) {
			$days_since_backup = ( time() - $last_backup ) / DAY_IN_SECONDS;

			if ( $is_high_activity && $days_since_backup > 1 ) {
				return array(
					'id'           => self::$slug . '-infrequent-high-activity',
					'title'        => __( 'Backup Frequency Too Low for Activity Level', 'wpshadow' ),
					'description'  => sprintf(
						/* translators: 1: days since last backup, 2: posts in last 30 days, 3: comments in last 30 days */
						__( 'Your last backup was %1$d days ago, but your site is very active (%2$d posts and %3$d comments in the last 30 days). Active sites should back up daily (like saving your work frequently when you\'re making lots of changes). If something goes wrong, you could lose days of content. Increase backup frequency in your backup plugin settings.', 'wpshadow' ),
						(int) $days_since_backup,
						$posts_last_30_days,
						$comments_last_30_days
					),
					'severity'     => 'high',
					'threat_level' => 70,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/backup-frequency?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'context'      => array(
						'days_since_backup'  => $days_since_backup,
						'posts_last_30_days' => $posts_last_30_days,
						'comments_last_30_days' => $comments_last_30_days,
						'activity_level'     => 'high',
					),
				);
			}

			if ( $is_medium_activity && $days_since_backup > 7 ) {
				return array(
					'id'           => self::$slug . '-infrequent-medium-activity',
					'title'        => __( 'Backup Frequency Could Be Higher', 'wpshadow' ),
					'description'  => sprintf(
						/* translators: %d: days since last backup */
						__( 'Your last backup was %d days ago. Your site has moderate activity, so weekly backups minimum are recommended (like saving your work at the end of each week). Consider increasing backup frequency to weekly or even daily for better protection.', 'wpshadow' ),
						(int) $days_since_backup
					),
					'severity'     => 'medium',
					'threat_level' => 55,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/backup-frequency?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'context'      => array(
						'days_since_backup' => $days_since_backup,
						'activity_level'    => 'medium',
					),
				);
			}

			if ( $days_since_backup > 30 ) {
				return array(
					'id'           => self::$slug . '-very-old',
					'title'        => __( 'No Recent Backups', 'wpshadow' ),
					'description'  => sprintf(
						/* translators: %d: days since last backup */
						__( 'Your last backup was %d days ago (like having very old photocopies of important documents). Even low-activity sites should back up monthly at minimum. Run a backup now and set up automatic backups in your backup plugin.', 'wpshadow' ),
						(int) $days_since_backup
					),
					'severity'     => 'high',
					'threat_level' => 75,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/backup-frequency?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'context'      => array(
						'days_since_backup' => $days_since_backup,
					),
				);
			}
		}

		return null; // Backup frequency is appropriate.
	}

	/**
	 * Get number of posts published in last N days.
	 *
	 * @since 0.6093.1200
	 * @param  int $days Number of days to look back.
	 * @return int Post count.
	 */
	private static function get_posts_last_n_days( $days ) {
		global $wpdb;

		$cutoff_date = gmdate( 'Y-m-d H:i:s', time() - ( $days * DAY_IN_SECONDS ) );

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts}
				WHERE post_type IN ('post', 'page')
				AND post_status = 'publish'
				AND post_date > %s",
				$cutoff_date
			)
		);

		return (int) $count;
	}

	/**
	 * Get number of comments in last N days.
	 *
	 * @since 0.6093.1200
	 * @param  int $days Number of days to look back.
	 * @return int Comment count.
	 */
	private static function get_comments_last_n_days( $days ) {
		global $wpdb;

		$cutoff_date = gmdate( 'Y-m-d H:i:s', time() - ( $days * DAY_IN_SECONDS ) );

		$count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->comments}
				WHERE comment_approved = '1'
				AND comment_date > %s",
				$cutoff_date
			)
		);

		return (int) $count;
	}
}
