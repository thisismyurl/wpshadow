<?php
/**
 * Newsletter Plugin Database Optimization Diagnostic
 *
 * Newsletter Plugin Database Optimization configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.720.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Newsletter Plugin Database Optimization Diagnostic Class
 *
 * @since 1.720.0000
 */
class Diagnostic_NewsletterPluginDatabaseOptimization extends Diagnostic_Base {

	protected static $slug = 'newsletter-plugin-database-optimization';
	protected static $title = 'Newsletter Plugin Database Optimization';
	protected static $description = 'Newsletter Plugin Database Optimization configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		// Check for Newsletter plugins
		$has_newsletter = defined( 'NEWSLETTER_VERSION' ) ||
		                  class_exists( 'Newsletter' ) ||
		                  function_exists( 'newsletter_get_sender_name' );
		
		if ( ! $has_newsletter ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		
		// Check 1: Subscriber table size
		$table = $wpdb->prefix . 'newsletter';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) ) === $table ) {
			$subscriber_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" );
			
			if ( $subscriber_count > 100000 ) {
				$issues[] = sprintf( __( '%s subscribers (consider segmentation)', 'wpshadow' ), number_format_i18n( $subscriber_count ) );
			}
		}
		
		// Check 2: Orphaned sent emails
		$sent_table = $wpdb->prefix . 'newsletter_sent';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $sent_table ) ) === $sent_table ) {
			$old_sent = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$sent_table} WHERE time < %d",
					time() - ( 180 * DAY_IN_SECONDS )
				)
			);
			
			if ( $old_sent > 10000 ) {
				$issues[] = sprintf( __( '%s sent records >6 months old (database bloat)', 'wpshadow' ), number_format_i18n( $old_sent ) );
			}
		}
		
		// Check 3: Bounce table cleanup
		$bounce_table = $wpdb->prefix . 'newsletter_user_logs';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $bounce_table ) ) === $bounce_table ) {
			$auto_cleanup = get_option( 'newsletter_bounce_cleanup', false );
			if ( ! $auto_cleanup ) {
				$issues[] = __( 'Bounce log auto-cleanup disabled (growing table)', 'wpshadow' );
			}
		}
		
		// Check 4: Statistics table indexes
		$stats_table = $wpdb->prefix . 'newsletter_stats';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $stats_table ) ) === $stats_table ) {
			$indexes = $wpdb->get_results( "SHOW INDEX FROM {$stats_table}" );
			if ( count( $indexes ) < 2 ) {
				$issues[] = __( 'Statistics table missing indexes (slow queries)', 'wpshadow' );
			}
		}
		
		// Check 5: Unconfirmed subscribers cleanup
		$retention_days = get_option( 'newsletter_unconfirmed_retention', 0 );
		if ( $retention_days === 0 ) {
			$issues[] = __( 'Unconfirmed subscribers never deleted (GDPR issue)', 'wpshadow' );
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
				/* translators: %s: list of database optimization issues */
				__( 'Newsletter plugin database has %d optimization issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/newsletter-plugin-database-optimization',
		);
	}
}
