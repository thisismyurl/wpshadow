<?php
/**
 * Email Deliverability Configuration Diagnostic
 *
 * Checks if email configuration is optimized for deliverability.
 *
 * @package WPShadow\Diagnostics
 * @since   1.6032.0146
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Email Deliverability Configuration
 *
 * Detects email configuration issues that impact deliverability.
 */
class Diagnostic_Email_Deliverability_Configuration extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-deliverability-configuration';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Deliverability Configuration';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies email configuration for optimal deliverability';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'email';

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Finding array if issues detected, null otherwise
	 */
	public static function check() {
		$issues  = array();
		$stats   = array();
		$plugins = array(
			'wp-mail-smtp/wp_mail_smtp.php'              => 'WP Mail SMTP',
			'mailgun/mailgun.php'                        => 'Mailgun',
			'sendgrid-email-delivery-simplified/sendgrid-email-delivery-simplified.php' => 'SendGrid',
			'postman-smtp/postman-smtp.php'              => 'Postman SMTP',
		);

		$active = array();
		foreach ( $plugins as $file => $name ) {
			if ( is_plugin_active( $file ) ) {
				$active[] = $name;
			}
		}

		$stats['smtp_plugins_active']  = count( $active );
		$stats['smtp_plugins']         = $active;

		// Check default from address
		$from_address = get_option( 'admin_email' );
		$stats['from_email'] = $from_address;
		$stats['from_domain_matches_site'] = preg_match( '/@' . preg_quote( wp_parse_url( home_url(), PHP_URL_HOST ), '/' ) . '/', $from_address );

		if ( empty( $active ) && ! $stats['from_domain_matches_site'] ) {
			$issues[] = __( 'No SMTP plugin configured and email domain mismatch detected', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Email deliverability depends on proper SMTP configuration, authentication (SPF/DKIM/DMARC), and matching domain names. Poor email configuration causes transactional emails to be marked as spam, damaging customer relationships.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/email-deliverability',
				'context'       => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
