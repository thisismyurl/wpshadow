<?php
/**
 * Comment Spam Detection Rate Diagnostic
 *
 * Measures effectiveness of spam detection mechanisms.
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
 * Comment Spam Detection Rate Class
 *
 * Analyzes spam detection rate to identify filter effectiveness.
 * High spam rates indicate weak protection or filter misconfiguration.
 *
 * @since 1.5029.1630
 */
class Diagnostic_Comment_Spam_Detection_Rate extends Diagnostic_Base {

	protected static $slug        = 'comment-spam-detection-rate';
	protected static $title       = 'Comment Spam Detection Rate';
	protected static $description = 'Measures spam detection effectiveness';
	protected static $family      = 'comments';

	public static function check() {
		$cache_key = 'wpshadow_spam_detection_rate';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Get comment counts using WordPress API (NO $wpdb).
		$comment_stats = wp_count_comments();

		if ( empty( $comment_stats ) ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$spam_count = (int) $comment_stats->spam;
		$approved_count = (int) $comment_stats->approved;
		$pending_count = (int) $comment_stats->moderated;
		$total_received = $spam_count + $approved_count + $pending_count;

		if ( $total_received < 100 ) {
			// Not enough data.
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$spam_rate = ( $spam_count / $total_received ) * 100;

		// Check for common anti-spam plugins.
		$has_spam_protection = is_plugin_active( 'akismet/akismet.php' )
			|| is_plugin_active( 'antispam-bee/antispam_bee.php' )
			|| is_plugin_active( 'stop-spammer-registrations-plugin/stop-spammer-registrations-new.php' );

		// High spam rate indicates weak protection.
		if ( $spam_rate > 60 && ! $has_spam_protection ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: spam percentage */
					__( '%.1f%% of comments are spam. No anti-spam plugin detected. Install protection now.', 'wpshadow' ),
					$spam_rate
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/security-comment-spam-protection',
				'data'         => array(
					'spam_rate'          => round( $spam_rate, 1 ),
					'spam_count'         => $spam_count,
					'approved_count'     => $approved_count,
					'total_received'     => $total_received,
					'has_protection'     => $has_spam_protection,
					'suggested_plugins'  => array( 'Akismet', 'Antispam Bee', 'Stop Spammer Registrations' ),
				),
			);

			set_transient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
