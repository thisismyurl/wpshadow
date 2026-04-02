<?php
/**
 * Comment Spam Filter Diagnostic
 *
 * Checks if effective spam filtering is active.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Spam Filter Diagnostic Class
 *
 * Verifies that effective spam filtering is active and that the site
 * is protected from comment spam.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Comment_Spam_Filter extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-spam-filter';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Spam Filter';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if effective spam filtering is active';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'publisher';

	/**
	 * Run the comment spam filter diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if spam filter issues detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for Akismet.
		$akismet_key = get_option( 'akismet_api_key' );
		$has_akismet = ! empty( $akismet_key );
		$stats['akismet_enabled'] = $has_akismet;

		if ( ! $has_akismet ) {
			$issues[] = __( 'Akismet is not configured - no anti-spam protection', 'wpshadow' );
		}

		// Check for reCAPTCHA.
		$recaptcha_key = get_option( 'recaptcha_site_key' );
		$has_recaptcha = ! empty( $recaptcha_key );
		$stats['recaptcha_enabled'] = $has_recaptcha;

		if ( ! $has_recaptcha ) {
			$warnings[] = __( 'reCAPTCHA not configured - consider adding for extra protection', 'wpshadow' );
		}

		// Check for spam protection plugins.
		$spam_plugins = array(
			'wordpress-zero-spam/wordpress-zero-spam.php' => 'WP Zero Spam',
			'honeypot/honeypot.php'                       => 'Honeypot',
			'wpforms/wpforms.php'                         => 'WPForms (with spam protection)',
		);

		$active_spam_plugin = null;
		foreach ( $spam_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_spam_plugin = $name;
				break;
			}
		}

		$stats['spam_plugin'] = $active_spam_plugin;

		// Check spam comment ratio.
		$spam_comments = intval(
			$wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = 'spam'"
			)
		);

		$total_comments = intval(
			$wpdb->get_var(
				"SELECT COUNT(*) FROM {$wpdb->comments}"
			)
		);

		$stats['spam_count'] = $spam_comments;
		$stats['total_comments'] = $total_comments;

		if ( $total_comments > 0 ) {
			$spam_ratio = ( $spam_comments / $total_comments ) * 100;
			$stats['spam_ratio_percent'] = round( $spam_ratio, 1 );

			if ( $spam_ratio > 50 ) {
				$issues[] = sprintf(
					/* translators: %d: percentage */
					__( 'High spam ratio: %d%% of comments are spam', 'wpshadow' ),
					intval( $spam_ratio )
				);
			} elseif ( $spam_ratio > 30 ) {
				$warnings[] = sprintf(
					/* translators: %d: percentage */
					__( 'Moderate spam ratio: %d%% of comments are spam', 'wpshadow' ),
					intval( $spam_ratio )
				);
			}
		}

		// Check comment moderation settings.
		$comment_moderation = get_option( 'comment_moderation' );
		$stats['moderation_enabled'] = boolval( $comment_moderation );

		if ( ! $comment_moderation ) {
			$warnings[] = __( 'Comment moderation is disabled - enable to catch spam', 'wpshadow' );
		}

		// Check hold keywords.
		$comment_hold = get_option( 'comment_moderation_keys' );
		$stats['hold_keywords_configured'] = ! empty( $comment_hold );

		if ( empty( $comment_hold ) ) {
			$warnings[] = __( 'No comment hold keywords configured - add some for spam detection', 'wpshadow' );
		}

		// Check disallowed keywords.
		$comment_disallow = get_option( 'disallowed_keys' );
		$stats['disallow_keywords_configured'] = ! empty( $comment_disallow );

		if ( empty( $comment_disallow ) ) {
			$warnings[] = __( 'No disallowed comment keywords configured', 'wpshadow' );
		}

		// Check comment approval requirement for first-time commenters.
		$comment_whitelist = get_option( 'comment_previously_approved', 1 );
		$stats['whitelist_enabled'] = boolval( $comment_whitelist );

		if ( ! $comment_whitelist ) {
			$warnings[] = __( 'First-time comments not held for moderation - consider enabling', 'wpshadow' );
		}

		// Check thread depth (prevents deeply nested spam).
		$thread_comments_depth = get_option( 'thread_comments_depth', 5 );
		$stats['thread_depth'] = intval( $thread_comments_depth );

		// Check comment links limit.
		$comment_links_limit = get_option( 'comment_max_links', 2 );
		$stats['max_links_per_comment'] = intval( $comment_links_limit );

		if ( $comment_links_limit > 5 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( 'Comments allow many links (%d) - consider reducing to prevent spam', 'wpshadow' ),
				$comment_links_limit
			);
		}

		// Check for URL requirements in comments.
		$require_name_email = true;
		if ( false === get_option( 'require_name_email' ) ) {
			$require_name_email = false;
		}

		$stats['require_name_email'] = $require_name_email;

		if ( ! $require_name_email ) {
			$warnings[] = __( 'Name and email not required for comments - easier for spam bots', 'wpshadow' );
		}

		// Check Akismet status.
		if ( $has_akismet ) {
			$akismet_stats = get_transient( 'akismet_stats' );
			if ( $akismet_stats ) {
				$stats['akismet_stats'] = $akismet_stats;
			}
		}

		// Check spam per day rate.
		$last_30_days = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->comments}
			 WHERE comment_approved = 'spam'
			 AND comment_date > DATE_SUB(NOW(), INTERVAL 30 DAY)"
		); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Static query without user input.

		$avg_spam_per_day = intval( $last_30_days ) / 30;
		$stats['avg_spam_per_day'] = round( $avg_spam_per_day, 1 );

		if ( $avg_spam_per_day > 10 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( 'Averaging %d spam comments per day - increase filtering', 'wpshadow' ),
				intval( $avg_spam_per_day )
			);
		}

		// Check if comments are closed on old posts (anti-spam strategy).
		$old_posts = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->posts} 
			 WHERE post_status = 'publish' 
			 AND post_date < DATE_SUB(NOW(), INTERVAL 1 YEAR)
			 AND comment_status = 'open'"
		);

		if ( $old_posts > 10 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( '%d old posts still accepting comments - consider closing comments on posts >1 year old', 'wpshadow' ),
				intval( $old_posts )
			);
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Comment spam filter has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-spam-filter',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Comment spam filter has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comment-spam-filter',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Comment spam filter is effective.
	}
}
