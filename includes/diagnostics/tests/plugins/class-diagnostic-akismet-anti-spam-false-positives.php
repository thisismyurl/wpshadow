<?php
/**
 * Akismet Anti Spam False Positives Diagnostic
 *
 * Akismet Anti Spam False Positives issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1446.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Akismet Anti Spam False Positives Diagnostic Class
 *
 * @since 1.1446.0000
 */
class Diagnostic_AkismetAntiSpamFalsePositives extends Diagnostic_Base {

	protected static $slug = 'akismet-anti-spam-false-positives';
	protected static $title = 'Akismet Anti Spam False Positives';
	protected static $description = 'Akismet Anti Spam False Positives issue found';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'AKISMET_VERSION' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Spam queue size
		$spam_count = wp_count_comments();
		if ( isset( $spam_count->spam ) && $spam_count->spam > 500 ) {
			$issues[] = sprintf( __( '%d spam comments (review queue bloat)', 'wpshadow' ), $spam_count->spam );
		}
		
		// Check 2: Auto-delete spam
		$auto_delete = get_option( 'akismet_strictness', '0' );
		if ( '1' === $auto_delete ) {
			$issues[] = __( 'Auto-delete spam enabled (false positives lost)', 'wpshadow' );
		}
		
		// Check 3: Comment moderation
		$moderation = get_option( 'comment_moderation', 0 );
		if ( ! $moderation ) {
			$issues[] = __( 'Comment moderation disabled (spam may appear)', 'wpshadow' );
		}
		
		// Check 4: API key
		$api_key = get_option( 'wordpress_api_key', '' );
		if ( empty( $api_key ) ) {
			$issues[] = __( 'Akismet API key missing (no spam protection)', 'wpshadow' );
		}
		
		// Check 5: Discard policy
		$discard_month = get_option( 'akismet_discard_month', 'false' );
		if ( 'true' === $discard_month ) {
			$issues[] = __( 'Auto-discard after 1 month (false positives deleted)', 'wpshadow' );
		}
		
		// Check 6: Show comment count
		$show_count = get_option( 'akismet_show_user_comments_approved', false );
		if ( ! $show_count ) {
			$issues[] = __( 'Approved comment count hidden (trust signals lost)', 'wpshadow' );
		}
		
		if ( empty( $issues ) ) {
			return null;
		}
		
		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}
		
		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of spam filtering issues */
				__( 'Akismet has %d spam filtering issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/akismet-anti-spam-false-positives',
		);
	}
}
