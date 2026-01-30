<?php
/**
 * Wp Mail Smtp Provider Api Limits Diagnostic
 *
 * Wp Mail Smtp Provider Api Limits issue found.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1459.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Mail Smtp Provider Api Limits Diagnostic Class
 *
 * @since 1.1459.0000
 */
class Diagnostic_WpMailSmtpProviderApiLimits extends Diagnostic_Base {

	protected static $slug = 'wp-mail-smtp-provider-api-limits';
	protected static $title = 'Wp Mail Smtp Provider Api Limits';
	protected static $description = 'Wp Mail Smtp Provider Api Limits issue found';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WPMS_PLUGIN_VER' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Provider rate limits
		$settings = get_option( 'wp_mail_smtp', array() );
		$provider = isset( $settings['mail']['mailer'] ) ? $settings['mail']['mailer'] : '';
		$rate_limit_configured = get_option( 'wp_mail_smtp_rate_limit_' . $provider, '0' );
		if ( ! empty( $provider ) && '0' === $rate_limit_configured ) {
			$issues[] = "no rate limiting for {$provider} (may hit API limits)";
		}
		
		// Check 2: Daily sending quota
		$daily_sent = get_transient( 'wp_mail_smtp_daily_count' );
		$daily_limit = get_option( 'wp_mail_smtp_daily_limit_' . $provider, 0 );
		if ( ! empty( $daily_limit ) && ! empty( $daily_sent ) ) {
			$percent_used = ( $daily_sent / $daily_limit ) * 100;
			if ( $percent_used > 80 ) {
				$issues[] = round( $percent_used ) . "% of daily sending quota used";
			}
		}
		
		// Check 3: API errors from provider
		$api_errors = get_transient( 'wp_mail_smtp_api_errors_' . $provider );
		if ( ! empty( $api_errors ) && is_array( $api_errors ) ) {
			$error_count = count( $api_errors );
			if ( $error_count > 5 ) {
				$issues[] = "{$error_count} API errors from {$provider}";
			}
		}
		
		// Check 4: Throttle backoff strategy
		$backoff_enabled = get_option( 'wp_mail_smtp_backoff_enabled', '0' );
		if ( '0' === $backoff_enabled && ! empty( $provider ) ) {
			$issues[] = 'no exponential backoff on API failures';
		}
		
		// Check 5: Queue for rate-limited emails
		$queue_enabled = get_option( 'wp_mail_smtp_queue_enabled', '0' );
		if ( '0' === $queue_enabled && ! empty( $daily_limit ) ) {
			$issues[] = 'no email queue (may lose emails at limit)';
		}
		
		// Check 6: Monitoring alerts
		$alert_threshold = get_option( 'wp_mail_smtp_alert_threshold', 0 );
		if ( ! empty( $daily_limit ) && empty( $alert_threshold ) ) {
			$issues[] = 'no alerts configured for quota limits';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 95, 70 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'WP Mail SMTP API limit issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/wp-mail-smtp-provider-api-limits',
			);
		}
		
		return null;
	}
}
