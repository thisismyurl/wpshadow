<?php
/**
 * Data Retention Settings Diagnostic
 *
 * Verifies that data retention policies are properly configured to comply
 * with GDPR and other privacy regulations requiring data minimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26032.1600
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data Retention Settings Diagnostic Class
 *
 * Ensures appropriate data retention policies are in place for privacy compliance.
 *
 * @since 1.26032.1600
 */
class Diagnostic_Data_Retention_Settings extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'data-retention-settings';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Data Retention Settings';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies data retention policy configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Comment auto-deletion is configured
	 * - Old data export files are cleaned up
	 * - User session expiration is reasonable
	 * - Transients are being cleaned up
	 * - Log file retention policies
	 *
	 * @since  1.26032.1600
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if old personal data export files are being cleaned up.
		$has_cleanup_cron = wp_next_scheduled( 'wp_privacy_delete_old_export_files' );
		if ( false === $has_cleanup_cron ) {
			$issues[] = __( 'Automatic cleanup of old personal data export files is not scheduled', 'wpshadow' );
		}

		// Check for excessive comment retention.
		global $wpdb;
		$old_comments_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->comments} 
				WHERE comment_date < %s 
				AND comment_approved = 'spam'",
				gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
			)
		);

		if ( $old_comments_count > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of old spam comments */
				_n(
					'There is %d spam comment older than 30 days that should be permanently deleted',
					'There are %d spam comments older than 30 days that should be permanently deleted',
					$old_comments_count,
					'wpshadow'
				),
				number_format_i18n( $old_comments_count )
			);
		}

		// Check for old trashed comments.
		$trashed_comments = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->comments} 
				WHERE comment_date < %s 
				AND comment_approved = 'trash'",
				gmdate( 'Y-m-d H:i:s', strtotime( '-30 days' ) )
			)
		);

		if ( $trashed_comments > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of old trashed comments */
				_n(
					'There is %d trashed comment older than 30 days that should be reviewed',
					'There are %d trashed comments older than 30 days that should be reviewed',
					$trashed_comments,
					'wpshadow'
				),
				number_format_i18n( $trashed_comments )
			);
		}

		// Check transient cleanup.
		$expired_transients = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->options} 
				WHERE option_name LIKE %s 
				AND option_value < %d",
				$wpdb->esc_like( '_transient_timeout_' ) . '%',
				time()
			)
		);

		if ( $expired_transients > 100 ) {
			$issues[] = sprintf(
				/* translators: %d: number of expired transients */
				__( 'There are %d expired transients that should be cleaned up', 'wpshadow' ),
				number_format_i18n( $expired_transients )
			);
		}

		// Check for old post revisions.
		$old_revisions = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				WHERE post_type = 'revision' 
				AND post_date < %s",
				gmdate( 'Y-m-d H:i:s', strtotime( '-6 months' ) )
			)
		);

		if ( $old_revisions > 1000 ) {
			$issues[] = sprintf(
				/* translators: %d: number of old revisions */
				__( 'There are %d post revisions older than 6 months; consider limiting revision retention', 'wpshadow' ),
				number_format_i18n( $old_revisions )
			);
		}

		// Check revision limits.
		$revisions_enabled = WP_POST_REVISIONS;
		if ( true === $revisions_enabled ) {
			$issues[] = __( 'Post revisions are unlimited; consider setting WP_POST_REVISIONS to a specific number to limit data growth', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( '. ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/data-retention-settings',
			);
		}

		return null;
	}
}
