<?php
/**
 * Spam Comments Accumulation Diagnostic
 *
 * Detects excessive spam comments clogging the database,
 * which can slow performance and waste storage.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5028.1630
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Spam Comments Accumulation Class
 *
 * Monitors the accumulation of spam comments that should be purged.
 * Large spam queues impact database performance and backups.
 *
 * @since 1.5028.1630
 */
class Diagnostic_Spam_Comments_Accumulation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'spam-comments-accumulation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Spam Comments Accumulation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects excessive spam comments bloating the database';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the diagnostic check.
	 *
	 * Uses wp_count_comments() to detect spam accumulation.
	 * Flags if spam count exceeds 1000 (performance impact).
	 *
	 * @since  1.5028.1630
	 * @return array|null Finding array if excessive spam, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_spam_accumulation_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Use WordPress API (NO $wpdb).
		$comment_stats = wp_count_comments();

		if ( ! $comment_stats ) {
			set_transient( $cache_key, null, 12 * HOUR_IN_SECONDS );
			return null;
		}

		$spam_count = (int) $comment_stats->spam;

		// Threshold for performance impact: 1000 spam comments.
		if ( $spam_count > 1000 ) {
			$threat_level = 50;
			$severity     = 'medium';

			if ( $spam_count > 5000 ) {
				$threat_level = 65;
				$severity     = 'high';
			}

			// Check if auto-deletion is configured.
			$days_to_delete = (int) get_option( 'akismet_discard_month', 0 );
			$has_auto_purge = $days_to_delete > 0;

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: spam comment count */
					__( 'Excessive spam comments detected: %s spam comments are clogging your database. Consider enabling auto-deletion.', 'wpshadow' ),
					number_format_i18n( $spam_count )
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comments-spam-accumulation',
				'data'         => array(
					'spam_count'    => $spam_count,
					'has_auto_purge' => $has_auto_purge,
					'auto_purge_days' => $days_to_delete,
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 12 * HOUR_IN_SECONDS );
		return null;
	}
}
