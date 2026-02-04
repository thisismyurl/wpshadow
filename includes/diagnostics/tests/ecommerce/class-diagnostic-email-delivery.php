<?php
/**
 * Email Delivery Diagnostic
 *
 * Checks if order confirmation emails are sending correctly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email Delivery Diagnostic Class
 *
 * Verifies that transactional emails (order confirmations, etc.) are
 * being sent and delivered properly by the store.
 *
 * @since 1.6035.1400
 */
class Diagnostic_Email_Delivery extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-delivery';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Delivery';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if order confirmation emails are sending correctly';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the email delivery diagnostic check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if email delivery issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for WooCommerce.
		if ( ! function_exists( 'wc' ) || ! class_exists( 'WooCommerce' ) ) {
			$warnings[] = __( 'WooCommerce not active - skipping email delivery check', 'wpshadow' );
			return null;
		}

		// Check email sending function.
		$mailer = WC()->mailer();
		$stats['woocommerce_mailer'] = ( $mailer ? 'available' : 'unavailable' );

		// Get WooCommerce email settings.
		$from_name = get_option( 'woocommerce_email_from_name' );
		$from_address = get_option( 'woocommerce_email_from_address' );

		$stats['from_name'] = $from_name ?: 'Not set';
		$stats['from_address'] = $from_address ?: 'Not set';

		if ( empty( $from_name ) ) {
			$warnings[] = __( 'WooCommerce from name not set', 'wpshadow' );
		}

		if ( empty( $from_address ) || ! is_email( $from_address ) ) {
			$issues[] = __( 'WooCommerce from address not set or invalid', 'wpshadow' );
		}

		// Check email headers.
		$email_footer_text = get_option( 'woocommerce_email_footer_text' );
		$stats['has_footer'] = ! empty( $email_footer_text );

		// Check SMTP configuration.
		$smtp_host = get_option( 'post_smtp_host' );
		$has_smtp = ! empty( $smtp_host );
		$stats['smtp_configured'] = $has_smtp;

		if ( ! $has_smtp ) {
			$warnings[] = __( 'No SMTP configured - using PHP mail() which may be unreliable', 'wpshadow' );
		}

		// Check email log plugin.
		$has_email_log = class_exists( 'Email_Log' ) || is_plugin_active( 'email-log/email-log.php' );
		$stats['email_logging'] = $has_email_log;

		if ( ! $has_email_log ) {
			$warnings[] = __( 'No email logging plugin active - can\'t track delivery issues', 'wpshadow' );
		}

		// Check for failed orders (may indicate email issues).
		$failed_orders = wc_get_orders( array(
			'status'         => 'failed',
			'posts_per_page' => 5,
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );

		$stats['recent_failed_orders'] = count( $failed_orders );

		if ( count( $failed_orders ) > 0 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( '%d failed orders in last check - customers may not have received confirmation', 'wpshadow' ),
				count( $failed_orders )
			);
		}

		// Check email templates directory.
		$theme = wp_get_theme();
		$theme_dir = $theme->get_stylesheet_directory();
		$woocommerce_templates = $theme_dir . '/woocommerce/emails';

		$has_custom_templates = is_dir( $woocommerce_templates );
		$stats['custom_email_templates'] = $has_custom_templates;

		// Check for mail plugin conflicts.
		$mail_plugins = array(
			'wp-mail-smtp/wp-mail-smtp.php',
			'post-smtp/postman-smtp.php',
			'sendgrid-email-delivery-simplified/sendgrid-email-delivery-simplified.php',
		);

		$active_mail_plugin = null;
		foreach ( $mail_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_mail_plugin = $plugin;
				break;
			}
		}

		$stats['mail_plugin'] = $active_mail_plugin ?: 'None';

		// Check email testing capability.
		$test_email_sent = get_option( 'woocommerce_test_email_sent' );
		$stats['has_test_email'] = ! empty( $test_email_sent );

		// Check for disabled email notifications.
		$emails = WC()->mailer()->get_emails();
		$disabled_emails = 0;

		foreach ( $emails as $email ) {
			if ( isset( $email->enabled ) && $email->enabled === 'no' ) {
				$disabled_emails++;
			}
		}

		$stats['disabled_email_types'] = $disabled_emails;

		if ( $disabled_emails > 0 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( '%d email types disabled - customers won\'t receive all notifications', 'wpshadow' ),
				$disabled_emails
			);
		}

		// Check for pending orders.
		$pending_orders = wc_get_orders( array(
			'status' => 'pending',
		) );

		$stats['pending_orders_awaiting_payment'] = count( $pending_orders );

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Email delivery has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-delivery',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Email delivery has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-delivery',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Email delivery is working.
	}
}
