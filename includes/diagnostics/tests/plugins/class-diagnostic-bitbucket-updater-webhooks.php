<?php
/**
 * Bitbucket Updater Webhooks Diagnostic
 *
 * Bitbucket Updater Webhooks issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1080.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bitbucket Updater Webhooks Diagnostic Class
 *
 * @since 1.1080.0000
 */
class Diagnostic_BitbucketUpdaterWebhooks extends Diagnostic_Base {

	protected static $slug = 'bitbucket-updater-webhooks';
	protected static $title = 'Bitbucket Updater Webhooks';
	protected static $description = 'Bitbucket Updater Webhooks issue detected';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();
		
		// Check 1: Webhook URL configured
		$webhook_url = get_option( 'bitbucket_updater_webhook_url', '' );
		if ( empty( $webhook_url ) ) {
			$issues[] = 'Webhook URL not configured';
		}
		
		// Check 2: Webhook secret key
		$secret_key = get_option( 'bitbucket_updater_webhook_secret', '' );
		if ( empty( $secret_key ) ) {
			$issues[] = 'Webhook secret key not set';
		}
		
		// Check 3: SSL verification enabled
		$ssl_verify = get_option( 'bitbucket_updater_ssl_verify', false );
		if ( ! $ssl_verify ) {
			$issues[] = 'SSL verification disabled';
		}
		
		// Check 4: Webhook retry mechanism
		$retry_enabled = get_option( 'bitbucket_updater_retry_enabled', false );
		if ( ! $retry_enabled ) {
			$issues[] = 'Retry mechanism not enabled';
		}
		
		// Check 5: Payload validation
		$payload_validation = get_option( 'bitbucket_updater_payload_validation', false );
		if ( ! $payload_validation ) {
			$issues[] = 'Payload validation disabled';
		}
		
		// Check 6: Webhook logs enabled
		$webhook_logs = get_option( 'bitbucket_updater_webhook_logs', false );
		if ( ! $webhook_logs ) {
			$issues[] = 'Webhook logs disabled';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 35 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Bitbucket Updater webhook issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/bitbucket-updater-webhooks',
			);
		}
		
		return null;
	}
		}
		return null;
	}
}
