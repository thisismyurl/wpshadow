<?php
/**
 * Mailpoet Newsletter Bounce Handling Diagnostic
 *
 * Mailpoet Newsletter Bounce Handling configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.714.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mailpoet Newsletter Bounce Handling Diagnostic Class
 *
 * @since 1.714.0000
 */
class Diagnostic_MailpoetNewsletterBounceHandling extends Diagnostic_Base {

	protected static $slug = 'mailpoet-newsletter-bounce-handling';
	protected static $title = 'Mailpoet Newsletter Bounce Handling';
	protected static $description = 'Mailpoet Newsletter Bounce Handling configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! class_exists( 'MailPoet\Config\Initializer' ) && ! defined( 'MAILPOET_VERSION' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Bounce handling enabled
		$bounce_enabled = get_option( 'mailpoet_bounce_enabled', '0' );
		if ( '0' === $bounce_enabled ) {
			$issues[] = 'bounce handling not enabled (list hygiene affected)';
		}

		// Check 2: Bounce email address configured
		$bounce_email = get_option( 'mailpoet_bounce_address', '' );
		if ( empty( $bounce_email ) ) {
			$issues[] = 'no bounce email address configured';
		} elseif ( ! is_email( $bounce_email ) ) {
			$issues[] = 'invalid bounce email address';
		}

		// Check 3: Bounce threshold settings
		$bounce_threshold = get_option( 'mailpoet_bounce_threshold', 5 );
		if ( $bounce_threshold > 10 ) {
			$issues[] = "high bounce threshold ({$bounce_threshold} bounces before unsubscribe)";
		}

		// Check 4: Bounce processing frequency
		$process_frequency = get_option( 'mailpoet_bounce_check_frequency', 'daily' );
		if ( 'never' === $process_frequency ) {
			$issues[] = 'bounce processing disabled';
		} elseif ( 'weekly' === $process_frequency ) {
			$issues[] = 'infrequent bounce checking (weekly)';
		}

		// Check 5: Bounce rate monitoring
		$bounce_rate = get_transient( 'mailpoet_bounce_rate' );
		if ( ! empty( $bounce_rate ) && $bounce_rate > 5 ) {
			$issues[] = "high bounce rate ({$bounce_rate}%, affects deliverability)";
		}

		// Check 6: Automatic subscriber cleanup
		$auto_cleanup = get_option( 'mailpoet_bounce_auto_cleanup', '1' );
		if ( '0' === $auto_cleanup ) {
			$issues[] = 'automatic cleanup of bounced addresses disabled';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 70, 40 + ( count( $issues ) * 6 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'MailPoet bounce handling issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/mailpoet-newsletter-bounce-handling',
			);
		}

		return null;
	}
}
