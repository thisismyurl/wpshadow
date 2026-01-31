<?php
/**
 * FluentCRM Email Delivery Diagnostic
 *
 * FluentCRM email sending slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.486.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FluentCRM Email Delivery Diagnostic Class
 *
 * @since 1.486.0000
 */
class Diagnostic_FluentcrmEmailDelivery extends Diagnostic_Base {

	protected static $slug = 'fluentcrm-email-delivery';
	protected static $title = 'FluentCRM Email Delivery';
	protected static $description = 'FluentCRM email sending slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'FLUENTCRM' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: SMTP configured
		$smtp = get_option( 'fluentcrm_smtp_configured', 0 );
		if ( ! $smtp ) {
			$issues[] = 'SMTP not properly configured';
		}

		// Check 2: Sending queue enabled
		$queue = get_option( 'fluentcrm_sending_queue_enabled', 0 );
		if ( ! $queue ) {
			$issues[] = 'Email sending queue not enabled';
		}

		// Check 3: Batch size optimization
		$batch = absint( get_option( 'fluentcrm_email_batch_size', 0 ) );
		if ( $batch <= 0 ) {
			$issues[] = 'Email batch size not configured';
		}

		// Check 4: Retry mechanism
		$retry = get_option( 'fluentcrm_email_retry_enabled', 0 );
		if ( ! $retry ) {
			$issues[] = 'Email retry mechanism not enabled';
		}

		// Check 5: Delivery tracking
		$tracking = get_option( 'fluentcrm_delivery_tracking_enabled', 0 );
		if ( ! $tracking ) {
			$issues[] = 'Delivery tracking not enabled';
		}

		// Check 6: Bounce handling
		$bounce = get_option( 'fluentcrm_bounce_handling_enabled', 0 );
		if ( ! $bounce ) {
			$issues[] = 'Bounce handling not configured';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d email delivery issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/fluentcrm-email-delivery',
			);
		}

		return null;
	}
}
