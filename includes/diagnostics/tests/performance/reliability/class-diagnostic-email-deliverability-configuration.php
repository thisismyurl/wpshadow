<?php
/**
 * Email Deliverability Configuration Diagnostic
 *
 * Issue #4906: Email Not Configured (Goes to Spam or Fails)
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if email is properly configured for reliable delivery.
 * WordPress default mail() function often fails or goes to spam.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Email_Deliverability_Configuration Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Email_Deliverability_Configuration extends Diagnostic_Base {

	protected static $slug = 'email-deliverability-configuration';
	protected static $title = 'Email Not Configured (Goes to Spam or Fails)';
	protected static $description = 'Checks if email delivery is properly configured';
	protected static $family = 'reliability';

	public static function check() {
		$issues = array();

		// Check for SMTP configuration
		$has_smtp = false;
		$smtp_plugins = array(
			'wp-mail-smtp/wp_mail_smtp.php',
			'easy-wp-smtp/easy-wp-smtp.php',
			'post-smtp/postman-smtp.php',
		);

		foreach ( $smtp_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_smtp = true;
				break;
			}
		}

		if ( ! $has_smtp ) {
			$issues[] = __( 'Configure SMTP instead of PHP mail() function', 'wpshadow' );
			$issues[] = __( 'Set proper From address (not wordpress@yoursite.com)', 'wpshadow' );
			$issues[] = __( 'Configure SPF, DKIM, DMARC for domain authentication', 'wpshadow' );
			$issues[] = __( 'Use email service: SendGrid, Mailgun, Amazon SES', 'wpshadow' );
			$issues[] = __( 'Test email delivery regularly', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'WordPress emails often fail or go to spam. Proper SMTP configuration with SPF/DKIM/DMARC ensures reliable delivery.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/email-deliverability',
				'details'      => array(
					'recommendations'         => $issues,
					'failure_rate'            => 'Up to 30% of mail() emails fail or go to spam',
					'smtp_services'           => 'SendGrid (100 free/day), Mailgun (10k free/month)',
					'dns_records'             => 'SPF: Sender authentication, DKIM: Message signing',
				),
			);
		}

		return null;
	}
}
