<?php
/**
 * Comment Spam Detection Rate Diagnostic
 *
 * Measures the percentage of comments being flagged as spam,
 * indicating potential spam attack or inadequate protection.
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
 * Comment Spam Detection Rate Class
 *
 * Analyzes the ratio of spam comments to legitimate comments
 * to detect spam attacks or filter effectiveness issues.
 *
 * @since 1.5028.1630
 */
class Diagnostic_Comment_Spam_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-spam-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Spam Detection Rate';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors spam comment ratio to detect protection issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the diagnostic check.
	 *
	 * Analyzes comment spam rate using wp_count_comments() API.
	 * Flags if spam rate exceeds 30% threshold.
	 *
	 * @since  1.5028.1630
	 * @return array|null Finding array if high spam detected, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_comment_spam_detection_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Use WordPress API to get comment counts (NO $wpdb).
		$comment_stats = wp_count_comments();

		if ( ! $comment_stats ) {
			set_transient( $cache_key, null, 6 * HOUR_IN_SECONDS );
			return null;
		}

		$total_comments = (int) $comment_stats->total_comments;
		$spam_comments  = (int) $comment_stats->spam;
		$approved       = (int) $comment_stats->approved;

		// Need sufficient data to analyze.
		if ( $total_comments < 10 ) {
			set_transient( $cache_key, null, 6 * HOUR_IN_SECONDS );
			return null;
		}

		// Calculate spam rate.
		$spam_rate = ( $total_comments > 0 ) ? ( $spam_comments / $total_comments ) * 100 : 0;

		// Check for spam protection plugins.
		$has_akismet    = is_plugin_active( 'akismet/akismet.php' );
		$has_antispam   = is_plugin_active( 'antispam-bee/antispam_bee.php' );
		$has_recaptcha  = is_plugin_active( 'google-captcha/google-captcha.php' );
		$has_protection = $has_akismet || $has_antispam || $has_recaptcha;

		// High spam rate threshold: 30%.
		if ( $spam_rate > 30 ) {
			$threat_level = 70;
			$severity     = 'high';

			if ( $spam_rate > 50 ) {
				$threat_level = 85;
			}

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: spam rate percentage, 2: spam count, 3: total count */
					__( 'High spam detection rate: %.1f%% (%d spam out of %d comments). This indicates inadequate spam protection.', 'wpshadow' ),
					$spam_rate,
					$spam_comments,
					$total_comments
				),
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comments-spam-detection',
				'data'         => array(
					'spam_rate'        => $spam_rate,
					'spam_count'       => $spam_comments,
					'total_comments'   => $total_comments,
					'approved_count'   => $approved,
					'has_protection'   => $has_protection,
					'has_akismet'      => $has_akismet,
					'has_antispam_bee' => $has_antispam,
					'has_recaptcha'    => $has_recaptcha,
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 12 * HOUR_IN_SECONDS );
		return null;
	}
}
