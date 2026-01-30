<?php
/**
 * Comment Spam Filter Bypasses Diagnostic
 *
 * Identifies spam patterns getting past filters.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1630
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Spam Filter Bypasses Class
 *
 * Detects approved comments with spam characteristics,
 * indicating filter bypasses or false negatives.
 *
 * @since 1.5029.1630
 */
class Diagnostic_Comment_Spam_Filter_Bypasses extends Diagnostic_Base {

	protected static $slug        = 'comment-spam-filter-bypasses';
	protected static $title       = 'Comment Spam Filter Bypasses';
	protected static $description = 'Identifies spam getting past filters';
	protected static $family      = 'comments';

	public static function check() {
		$cache_key = 'wpshadow_spam_filter_bypasses';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Get approved comments using WordPress API (NO $wpdb).
		$comments = get_comments( array(
			'status' => 'approve',
			'number' => 200,
			'orderby' => 'comment_date',
			'order' => 'DESC',
		) );

		if ( empty( $comments ) ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$spam_indicators = array(
			'links' => array( 'viagra', 'cialis', 'casino', 'poker', 'loan', 'credit', 'pills' ),
			'patterns' => array(
				'/http[s]?:\/\/[^\s]{5,}/i', // Multiple URLs.
				'/\[url=/i',                  // BBCode links.
				'/click here/i',              // Common spam phrase.
				'/buy now/i',                 // Commercial spam.
				'/limited time/i',            // Urgency spam.
			),
		);

		$bypassed_spam = array();

		foreach ( $comments as $comment ) {
			$content = $comment->comment_content;
			$spam_signals = array();

			// Check for spam keywords.
			foreach ( $spam_indicators['links'] as $keyword ) {
				if ( stripos( $content, $keyword ) !== false ) {
					$spam_signals[] = "Contains keyword: {$keyword}";
					break;
				}
			}

			// Check for spam patterns.
			$url_count = preg_match_all( '/http[s]?:\/\//i', $content );
			if ( $url_count > 3 ) {
				$spam_signals[] = "Excessive URLs ({$url_count} found)";
			}

			foreach ( $spam_indicators['patterns'] as $pattern ) {
				if ( preg_match( $pattern, $content ) ) {
					$spam_signals[] = 'Spam pattern detected';
					break;
				}
			}

			// Check author URL for suspicious domains.
			if ( ! empty( $comment->comment_author_url ) ) {
				$suspicious_tlds = array( '.xyz', '.top', '.work', '.ru', '.cn' );
				foreach ( $suspicious_tlds as $tld ) {
					if ( strpos( $comment->comment_author_url, $tld ) !== false ) {
						$spam_signals[] = 'Suspicious TLD in author URL';
						break;
					}
				}
			}

			if ( count( $spam_signals ) >= 2 ) {
				$bypassed_spam[] = array(
					'id'       => $comment->comment_ID,
					'author'   => $comment->comment_author,
					'date'     => $comment->comment_date,
					'signals'  => $spam_signals,
					'excerpt'  => substr( $content, 0, 100 ) . '...',
				);
			}
		}

		if ( ! empty( $bypassed_spam ) ) {
			$bypass_rate = ( count( $bypassed_spam ) / count( $comments ) ) * 100;

			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: bypassed count, 2: bypass rate */
					__( '%1$d approved comments appear to be spam (%.1f%% bypass rate). Review filter settings.', 'wpshadow' ),
					count( $bypassed_spam ),
					$bypass_rate
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/security-spam-filter-bypasses',
				'data'         => array(
					'bypassed_spam' => array_slice( $bypassed_spam, 0, 30 ),
					'bypass_count'  => count( $bypassed_spam ),
					'bypass_rate'   => round( $bypass_rate, 1 ),
					'total_checked' => count( $comments ),
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
