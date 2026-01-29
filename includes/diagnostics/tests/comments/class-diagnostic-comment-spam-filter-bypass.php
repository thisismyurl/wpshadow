<?php
/**
 * Comment Spam Filter Bypass Diagnostic
 *
 * Identifies spam patterns that are successfully bypassing
 * active spam filters, indicating filter weaknesses.
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
 * Comment Spam Filter Bypass Class
 *
 * Analyzes approved comments for spam patterns that bypassed filters.
 * Helps identify filter configuration issues or new spam techniques.
 *
 * @since 1.5028.1630
 */
class Diagnostic_Comment_Spam_Filter_Bypass extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-spam-filter-bypass';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Spam Filter Bypasses';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects spam patterns bypassing active filters';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the diagnostic check.
	 *
	 * Analyzes recent approved comments for spam patterns using get_comments().
	 * Detects suspicious URLs, keywords, and patterns.
	 *
	 * @since  1.5028.1630
	 * @return array|null Finding array if bypasses detected, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_spam_filter_bypass_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Use get_comments() API (NO $wpdb).
		$recent_comments = get_comments(
			array(
				'status'  => 'approve',
				'number'  => 100,
				'orderby' => 'comment_date',
				'order'   => 'DESC',
			)
		);

		if ( empty( $recent_comments ) ) {
			set_transient( $cache_key, null, 12 * HOUR_IN_SECONDS );
			return null;
		}

		$suspicious_comments = array();
		$spam_patterns       = self::get_spam_patterns();

		foreach ( $recent_comments as $comment ) {
			$suspicion_score = 0;
			$matched_patterns = array();

			// Check content for spam patterns.
			$content = $comment->comment_content;
			$author_url = $comment->comment_author_url;

			foreach ( $spam_patterns as $pattern_name => $pattern ) {
				if ( preg_match( $pattern, $content ) || ( ! empty( $author_url ) && preg_match( $pattern, $author_url ) ) ) {
					$suspicion_score++;
					$matched_patterns[] = $pattern_name;
				}
			}

			// Check for excessive links.
			$link_count = substr_count( $content, 'http' );
			if ( $link_count > 2 ) {
				$suspicion_score += 2;
				$matched_patterns[] = 'excessive-links';
			}

			// Check for generic spam phrases.
			$spam_phrases = array( 'viagra', 'cialis', 'casino', 'poker', 'loans', 'insurance', 'pharmacy' );
			foreach ( $spam_phrases as $phrase ) {
				if ( stripos( $content, $phrase ) !== false ) {
					$suspicion_score += 2;
					$matched_patterns[] = "keyword-{$phrase}";
				}
			}

			// If suspicion score >= 3, likely spam bypass.
			if ( $suspicion_score >= 3 ) {
				$suspicious_comments[] = array(
					'comment_id' => $comment->comment_ID,
					'author'     => $comment->comment_author,
					'date'       => $comment->comment_date,
					'score'      => $suspicion_score,
					'patterns'   => $matched_patterns,
				);
			}
		}

		// If 5+ suspicious comments in last 100, flag it.
		if ( count( $suspicious_comments ) >= 5 ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of suspicious comments */
					__( 'Detected %d spam comments that bypassed filters. Review spam filter settings and consider strengthening protection.', 'wpshadow' ),
					count( $suspicious_comments )
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/comments-spam-filter-bypass',
				'data'         => array(
					'bypass_count'        => count( $suspicious_comments ),
					'suspicious_comments' => array_slice( $suspicious_comments, 0, 10 ),
					'total_analyzed'      => count( $recent_comments ),
				),
			);

			set_transient( $cache_key, $result, 6 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 12 * HOUR_IN_SECONDS );
		return null;
	}

	/**
	 * Get spam detection patterns.
	 *
	 * @since  1.5028.1630
	 * @return array Regex patterns for spam detection.
	 */
	private static function get_spam_patterns() {
		return array(
			'pharma-keywords'   => '/\b(viagra|cialis|levitra|pharmacy|pills|meds)\b/i',
			'gambling-keywords' => '/\b(casino|poker|slots|betting|lottery)\b/i',
			'finance-keywords'  => '/\b(loan|credit|insurance|mortgage|payday)\b/i',
			'suspicious-tlds'   => '/\.(ru|cn|tk|ml|ga|cf|gq)\b/i',
			'base64-encoded'    => '/^[A-Za-z0-9+\/=]{50,}$/',
		);
	}
}
