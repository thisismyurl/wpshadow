<?php
/**
 * bbPress Spam Protection Diagnostic
 *
 * bbPress spam protection insufficient.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.508.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * bbPress Spam Protection Diagnostic Class
 *
 * @since 1.508.0000
 */
class Diagnostic_BbpressSpamProtection extends Diagnostic_Base {

	protected static $slug = 'bbpress-spam-protection';
	protected static $title = 'bbPress Spam Protection';
	protected static $description = 'bbPress spam protection insufficient';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'bbPress' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		$threat_level = 0;

		// Check if Akismet is active and integrated
		$akismet_active = class_exists( 'Akismet' ) && function_exists( 'akismet_get_key' );
		$akismet_key = $akismet_active ? akismet_get_key() : '';
		if ( ! $akismet_active || empty( $akismet_key ) ) {
			$issues[] = 'akismet_not_configured';
			$threat_level += 20;
		}

		// Check moderation settings
		$moderate_anonymous = get_option( '_bbp_enable_anonymous', false );
		$moderate_new = get_option( '_bbp_moderate_new_users', false );
		if ( $moderate_anonymous && ! $moderate_new ) {
			$issues[] = 'anonymous_posts_not_moderated';
			$threat_level += 15;
		}

		// Check for topics/replies requiring moderation
		$topics_table = $wpdb->prefix . 'posts';
		$pending_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$topics_table} 
				 WHERE post_type IN ('topic', 'reply') 
				 AND post_status = %s",
				'pending'
			)
		);
		if ( $pending_count > 50 ) {
			$issues[] = 'excessive_pending_content';
			$threat_level += 10;
		}

		// Check for rapid posting (possible spam flood)
		$recent_posts = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$topics_table} 
				 WHERE post_type IN ('topic', 'reply') 
				 AND post_date > %s",
				date( 'Y-m-d H:i:s', strtotime( '-1 hour' ) )
			)
		);
		if ( $recent_posts > 100 ) {
			$issues[] = 'rapid_posting_detected';
			$threat_level += 15;
		}

		// Check blacklist/disallowed keys configuration
		$disallowed_keys = get_option( 'disallowed_keys', '' );
		if ( empty( $disallowed_keys ) ) {
			$issues[] = 'no_spam_blacklist';
			$threat_level += 10;
		}

		// Check for spam reports in meta
		$spam_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} 
				 WHERE meta_key = %s AND meta_value = %s",
				'_bbp_spam_meta_status',
				'spam'
			)
		);
		if ( $spam_count > 20 ) {
			$issues[] = 'high_spam_reports';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of spam protection issues */
				__( 'bbPress spam protection has issues: %s. This can allow spam content to flood your forums, damage SEO, and create poor user experience.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/bbpress-spam-protection',
			);
		}
		
		return null;
	}
}
