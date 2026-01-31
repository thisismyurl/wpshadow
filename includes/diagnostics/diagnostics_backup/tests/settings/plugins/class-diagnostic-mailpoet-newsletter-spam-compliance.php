<?php
/**
 * Mailpoet Newsletter Spam Compliance Diagnostic
 *
 * Mailpoet Newsletter Spam Compliance configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.712.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mailpoet Newsletter Spam Compliance Diagnostic Class
 *
 * @since 1.712.0000
 */
class Diagnostic_MailpoetNewsletterSpamCompliance extends Diagnostic_Base {

	protected static $slug = 'mailpoet-newsletter-spam-compliance';
	protected static $title = 'Mailpoet Newsletter Spam Compliance';
	protected static $description = 'Mailpoet Newsletter Spam Compliance configuration issues';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'MailPoet\Config\Initializer' ) ) {
			return null;
		}
		
		global $wpdb;
		$issues = array();
		$threat_level = 0;

		// Check sender information
		$settings = get_option( 'mailpoet_settings', array() );
		$sender_name = isset( $settings['sender']['name'] ) ? $settings['sender']['name'] : '';
		$sender_address = isset( $settings['sender']['address'] ) ? $settings['sender']['address'] : '';
		if ( empty( $sender_name ) || empty( $sender_address ) ) {
			$issues[] = 'incomplete_sender_info';
			$threat_level += 20;
		}

		// Check physical address (CAN-SPAM requirement)
		$physical_address = isset( $settings['physical_address'] ) ? $settings['physical_address'] : '';
		if ( empty( $physical_address ) ) {
			$issues[] = 'no_physical_address';
			$threat_level += 25;
		}

		// Check unsubscribe link in templates
		$newsletters_table = $wpdb->prefix . 'mailpoet_newsletters';
		$newsletters_without_unsub = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$newsletters_table} 
				 WHERE body NOT LIKE %s AND type = %s",
				'%[link:subscription_unsubscribe_url]%',
				'standard'
			)
		);
		if ( $newsletters_without_unsub > 0 ) {
			$issues[] = 'newsletters_missing_unsubscribe';
			$threat_level += 25;
		}

		// Check double opt-in
		$signup_confirmation = isset( $settings['signup_confirmation']['enabled'] ) ? $settings['signup_confirmation']['enabled'] : false;
		if ( ! $signup_confirmation ) {
			$issues[] = 'double_optin_disabled';
			$threat_level += 15;
		}

		// Check bounce handling
		$bounce_email = isset( $settings['bounce']['address'] ) ? $settings['bounce']['address'] : '';
		if ( empty( $bounce_email ) ) {
			$issues[] = 'no_bounce_handling';
			$threat_level += 10;
		}

		// Check for high bounce rate
		$stats_table = $wpdb->prefix . 'mailpoet_statistics_bounces';
		$bounce_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$stats_table}" );
		$sent_table = $wpdb->prefix . 'mailpoet_statistics_newsletters';
		$sent_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$sent_table}" );
		if ( $sent_count > 0 && ( $bounce_count / $sent_count ) > 0.05 ) {
			$issues[] = 'high_bounce_rate';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of spam compliance issues */
				__( 'MailPoet newsletter spam compliance has violations: %s. This can result in spam complaints, blacklisting, and legal issues under CAN-SPAM/GDPR.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/mailpoet-newsletter-spam-compliance',
			);
		}
		
		return null;
	}
}
