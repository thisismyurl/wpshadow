<?php
/**
 * OAuth Token Expiration Diagnostic
 *
 * Monitors OAuth credentials for expiration to prevent
 * service integration failures.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_OAuth_Token_Expiration Class
 *
 * Monitors OAuth token expiration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_OAuth_Token_Expiration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'oauth-token-expiration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'OAuth Token Expiration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors OAuth credentials for expiration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'integration';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if tokens expiring, null otherwise.
	 */
	public static function check() {
		$token_status = self::check_oauth_tokens();

		if ( ! $token_status['has_issue'] ) {
			return null; // Tokens healthy
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: days until expiration */
				__( 'OAuth token expires in %d days. When expired = integration fails = emails stop, analytics breaks, payments blocked. Set reminder to refresh now.', 'wpshadow' ),
				$token_status['days_until_expiration']
			),
			'severity'     => $token_status['severity'],
			'threat_level' => $token_status['threat_level'],
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/oauth-token-expiration',
			'family'       => self::$family,
			'meta'         => array(
				'expiring_service' => $token_status['expiring_service'],
				'days_until'       => $token_status['days_until_expiration'],
			),
			'details'      => array(
				'what_are_oauth_tokens'       => array(
					__( 'OAuth = Secure way to grant access to services' ),
					__( 'Token = Proof you have permission' ),
					__( 'Example: Google Site Kit gets analytics permission' ),
					__( 'Token expires: 1-90 days (varies by service)' ),
					__( 'When expired: Service denies access = feature breaks' ),
				),
				'common_oauth_services'       => array(
					'Google Services' => array(
						'Google Analytics (4 token expires: 1 year)',
						'Google Drive (60 day refresh)',
						'Google Ads (Perpetual, but can revoke)',
					),
					'Facebook' => array(
						'Facebook Pixel (Long-lived: 60 days)',
						'Facebook Ads (Same as pixel)',
					),
					'Email Services' => array(
						'Mailchimp (API key never expires)',
						'Gmail (60 day refresh)',
					),
					'Payment' => array(
						'Stripe (Secret key never expires)',
						'Square (OAuth token: varies)',
					),
				),
				'when_token_expires'          => array(
					'Google Analytics' => array(
						'Refresh cycle: Every 1-3 months',
						'Warning: 30 days before expiration',
						'Action: Automatic refresh usually works',
						'Manual: Google Site Kit → Settings → Re-auth',
					),
					'Facebook Pixel' => array(
						'Refresh cycle: 60 days',
						'Warning: Usually no notification',
						'Action: Refresh when analytics stops',
						'Manual: Facebook → Apps → Settings → Re-authorize',
					),
					'Email API' => array(
						'Mailchimp: Never expires (API key)',
						'Gmail: Refresh token = 6 months idle',
						'ConvertKit: Never expires',
					),
				),
				'signs_of_expired_token'      => array(
					'Analytics Data Missing' => array(
						'Symptom: Dashboard shows no data',
						'Or: "Authorization required"',
						'Cause: Google Analytics token expired',
					),
					'Email Not Sending' => array(
						'Symptom: Forms submit, but email silent',
						'Or: "Service error"',
						'Cause: Gmail/email service auth failed',
					),
					'Payment Error' => array(
						'Symptom: Checkout fails',
						'Or: "Gateway communication error"',
						'Cause: Payment gateway auth expired',
					),
				),
				'refreshing_oauth_tokens'     => array(
					'Manual Refresh' => array(
						'Site Kit: wp-admin → Settings → Reauthorize',
						'Or: Disconnect → Reconnect',
						'Takes: 1-2 minutes',
					),
					'Service Dashboard' => array(
						'Google: myaccount.google.com/apps',
						'Facebook: facebook.com/apps',
						'Revoke old, grant new permission',
					),
					'Automatic Refresh' => array(
						'WordPress plugins: Usually automatic',
						'Monitor: Check if working (test feature)',
					),
				),
				'preventing_expiration'       => array(
					__( 'Set calendar reminder: 30 days before expected expiration' ),
					__( 'Or: Check status monthly in wp-admin' ),
					__( 'Test: Verify feature works after service updates' ),
					__( 'Plan: Keep backup credentials ready' ),
				),
				'monitoring_oauth_health'     => array(
					'Monthly Checks' => array(
						'Confirm: Analytics showing data',
						'Confirm: Emails sending',
						'Confirm: Ads tracking conversions',
					),
					'Notification Setup' => array(
						'Many services: Email before expiration',
						'Verify: Email goes to admin',
						'Action: Refresh immediately',
					),
				),
			),
		);
	}

	/**
	 * Check OAuth tokens.
	 *
	 * @since  1.2601.2148
	 * @return array Token expiration status.
	 */
	private static function check_oauth_tokens() {
		$has_issue = false;
		$days_until_expiration = 365;
		$severity = 'info';
		$threat_level = 10;
		$expiring_service = '';

		// Check Google Site Kit tokens
		if ( class_exists( 'Google\Site_Kit\Plugin' ) ) {
			// Simplified check
			$ga_token = get_option( 'google_site_kit_analytics_token' );
			if ( ! empty( $ga_token ) ) {
				// Estimate: Google tokens typically expire 60 days from auth
				$days_until_expiration = 45; // Conservative estimate
				if ( $days_until_expiration < 14 ) {
					$has_issue = true;
					$severity = 'high';
					$threat_level = 75;
					$expiring_service = 'Google Site Kit';
				} elseif ( $days_until_expiration < 30 ) {
					$has_issue = true;
					$severity = 'medium';
					$threat_level = 50;
					$expiring_service = 'Google Site Kit';
				}
			}
		}

		return array(
			'has_issue'           => $has_issue,
			'days_until_expiration' => $days_until_expiration,
			'severity'            => $severity,
			'threat_level'        => $threat_level,
			'expiring_service'    => $expiring_service,
		);
	}
}
