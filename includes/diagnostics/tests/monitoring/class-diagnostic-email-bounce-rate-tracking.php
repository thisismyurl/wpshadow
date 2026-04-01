<?php
/**
 * Email Bounce Rate Tracking Diagnostic
 *
 * Monitors email bounce rate to identify deliverability issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email Bounce Rate Tracking Diagnostic Class
 *
 * Tracks email bounce rates and alerts if they exceed acceptable thresholds.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Email_Bounce_Rate_Tracking extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-bounce-rate-tracking';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Bounce Rate Tracking';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors email bounce rate for deliverability issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email-deliverability';

	/**
	 * Bounce rate threshold (percentage)
	 *
	 * @var int
	 */
	private const BOUNCE_THRESHOLD = 5;

	/**
	 * Run the email bounce rate diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if bounce rate issue detected, null otherwise.
	 */
	public static function check() {
		$bounce_data = self::get_bounce_statistics();

		if ( empty( $bounce_data['total_sent'] ) ) {
			// No email activity to track yet.
			return null;
		}

		$bounce_rate = ( $bounce_data['total_bounced'] / $bounce_data['total_sent'] ) * 100;

		if ( $bounce_rate > self::BOUNCE_THRESHOLD ) {
			$severity = 'medium';
			$threat_level = 60;

			if ( $bounce_rate > 10 ) {
				$severity = 'high';
				$threat_level = 80;
			}

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: bounce rate percentage, 2: threshold percentage */
					__( 'Email bounce rate is %1$.1f%% (threshold: %2$d%%). This indicates email deliverability problems.', 'wpshadow' ),
					$bounce_rate,
					self::BOUNCE_THRESHOLD
				),
				'severity'    => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/email-bounce-rate?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'        => array(
					'bounce_rate'    => round( $bounce_rate, 2 ),
					'total_sent'     => $bounce_data['total_sent'],
					'total_bounced'  => $bounce_data['total_bounced'],
					'period_days'    => $bounce_data['period_days'],
				),
			);
		}

		return null;
	}

	/**
	 * Get email bounce statistics from Activity Logger.
	 *
	 * @since 0.6093.1200
	 * @return array Bounce statistics.
	 */
	private static function get_bounce_statistics(): array {
		$period_days = 30;
		$start_time = time() - ( $period_days * DAY_IN_SECONDS );
		$activity_log = get_option( \WPShadow\Core\Activity_Logger::OPTION_NAME, array() );

		if ( ! is_array( $activity_log ) ) {
			return array(
				'total_sent'    => 0,
				'total_bounced' => 0,
				'period_days'   => $period_days,
			);
		}

		$total_sent    = 0;
		$total_bounced = 0;

		foreach ( $activity_log as $entry ) {
			$entry_time = isset( $entry['timestamp'] ) ? (int) $entry['timestamp'] : 0;
			$action     = isset( $entry['action'] ) ? (string) $entry['action'] : '';

			if ( $entry_time < $start_time ) {
				continue;
			}

			if ( 'email_sent' === $action ) {
				++$total_sent;
			} elseif ( 'email_bounced' === $action ) {
				++$total_bounced;
			}
		}

		return array(
			'total_sent'    => (int) $total_sent,
			'total_bounced' => (int) $total_bounced,
			'period_days'   => $period_days,
		);
	}

	/**
	 * Log email bounce event.
	 *
	 * This method should be called by email handling code when a bounce is detected.
	 *
	 * @since 0.6093.1200
	 * @param  string $email  Email address that bounced.
	 * @param  string $reason Bounce reason.
	 * @return void
	 */
	public static function log_bounce( string $email, string $reason = '' ): void {
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'email_bounced',
				__( 'Email bounce detected', 'wpshadow' ),
				'email',
				array(
					'recipient' => $email,
					'reason'    => $reason,
					'timestamp' => time(),
				)
			);
		}
	}

	/**
	 * Log email sent event.
	 *
	 * This method should be called by email handling code when an email is sent.
	 *
	 * @since 0.6093.1200
	 * @param  string $email Email address.
	 * @return void
	 */
	public static function log_sent( string $email ): void {
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'email_sent',
				__( 'Email sent', 'wpshadow' ),
				'email',
				array(
					'recipient' => $email,
					'timestamp' => time(),
				)
			);
		}
	}
}
