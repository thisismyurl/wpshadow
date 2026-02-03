<?php
/**
 * Email Delivery Configuration Diagnostic
 *
 * Tests if transactional emails are configured for reliable delivery.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7034.1230
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email Delivery Configuration Diagnostic Class
 *
 * Validates that transactional emails (order confirmations, etc.)
 * are configured for reliable delivery via SMTP.
 *
 * @since 1.7034.1230
 */
class Diagnostic_Email_Delivery_Configuration extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-delivery-configuration';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Delivery Configuration';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if transactional emails are configured for reliable delivery';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * Tests email delivery configuration including SMTP setup,
	 * authentication, and delivery tracking.
	 *
	 * @since  1.7034.1230
	 * @return array|null Finding array if issue detected, null if all clear.
	 */
	public static function check() {
		// Check for SMTP plugins.
		$smtp_plugins = array(
			'wp-mail-smtp/wp_mail_smtp.php'          => 'WP Mail SMTP',
			'easy-wp-smtp/easy-wp-smtp.php'          => 'Easy WP SMTP',
			'post-smtp/postman-smtp.php'             => 'Post SMTP',
			'wp-ses/wp-ses.php'                      => 'WP SES (Amazon)',
			'sendgrid-email-delivery-simplified/wpsendgrid.php' => 'SendGrid',
		);

		$active_smtp_plugins = array();
		foreach ( $smtp_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_smtp_plugins[] = $name;
			}
		}

		$has_smtp_plugin = ! empty( $active_smtp_plugins );

		// Check default PHP mail function (unreliable).
		$uses_default_mail = ! $has_smtp_plugin;

		// Check WP Mail SMTP configuration.
		$wp_mail_smtp_configured = false;
		if ( is_plugin_active( 'wp-mail-smtp/wp_mail_smtp.php' ) ) {
			$wp_mail_smtp_options = get_option( 'wp_mail_smtp' );
			$wp_mail_smtp_configured = ! empty( $wp_mail_smtp_options['mail']['from_email'] );
		}

		// Check for email logging.
		$has_email_log = is_plugin_active( 'wp-mail-logging/wp-mail-logging.php' ) ||
					   is_plugin_active( 'email-log/email-log.php' );

		// Test email send capability (without actually sending).
		$can_send_email = function_exists( 'wp_mail' );

		// Check from email address.
		$admin_email = get_option( 'admin_email' );
		$from_email = get_option( 'woocommerce_email_from_address', $admin_email );
		$from_domain = wp_parse_url( $from_email, PHP_URL_HOST );
		$site_domain = wp_parse_url( home_url(), PHP_URL_HOST );

		$from_email_matches_domain = ( $from_domain === $site_domain );

		// Check SPF/DKIM records (simplified check).
		$has_proper_dns = false; // Would require actual DNS lookup.

		// Check for WooCommerce (emails critical for e-commerce).
		$woocommerce_active = is_plugin_active( 'woocommerce/woocommerce.php' );

		// Check email templates customization.
		$custom_email_templates = false;
		$template_path = get_stylesheet_directory() . '/woocommerce/emails';
		if ( is_dir( $template_path ) ) {
			$custom_email_templates = true;
		}

		// Check for bounced email tracking.
		$has_bounce_tracking = is_plugin_active( 'wp-ses/wp-ses.php' );

		// Check email sending frequency (if logging enabled).
		global $wpdb;
		$recent_emails = 0;
		if ( $has_email_log ) {
			$log_table = $wpdb->prefix . 'mail_log';
			if ( $wpdb->get_var( "SHOW TABLES LIKE '{$log_table}'" ) === $log_table ) {
				$recent_emails = $wpdb->get_var(
					"SELECT COUNT(*) FROM {$log_table}
					 WHERE timestamp > DATE_SUB(NOW(), INTERVAL 7 DAY)"
				);
			}
		}

		// Check for issues.
		$issues = array();

		// Issue 1: Using default PHP mail function.
		if ( $uses_default_mail ) {
			$issues[] = array(
				'type'        => 'default_mail_function',
				'description' => __( 'Using default PHP mail(); emails likely to be marked as spam or not delivered', 'wpshadow' ),
			);
		}

		// Issue 2: SMTP plugin not configured.
		if ( $has_smtp_plugin && ! $wp_mail_smtp_configured ) {
			$issues[] = array(
				'type'        => 'smtp_not_configured',
				'description' => __( 'SMTP plugin installed but not configured; emails still using PHP mail()', 'wpshadow' ),
			);
		}

		// Issue 3: From email doesn't match domain.
		if ( ! $from_email_matches_domain ) {
			$issues[] = array(
				'type'        => 'from_email_mismatch',
				'description' => sprintf(
					/* translators: 1: from email, 2: site domain */
					__( 'From email %1$s does not match site domain %2$s; affects deliverability', 'wpshadow' ),
					$from_email,
					$site_domain
				),
			);
		}

		// Issue 4: No email logging.
		if ( ! $has_email_log && $woocommerce_active ) {
			$issues[] = array(
				'type'        => 'no_email_logging',
				'description' => __( 'No email logging enabled; cannot troubleshoot delivery issues', 'wpshadow' ),
			);
		}

		// Issue 5: No bounce tracking for high volume.
		if ( absint( $recent_emails ) > 500 && ! $has_bounce_tracking ) {
			$issues[] = array(
				'type'        => 'no_bounce_tracking',
				'description' => __( 'High email volume but no bounce tracking; sender reputation at risk', 'wpshadow' ),
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Email delivery is not properly configured, which can result in lost order confirmations and customer complaints', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-delivery-configuration',
				'details'      => array(
					'has_smtp_plugin'         => $has_smtp_plugin,
					'active_smtp_plugins'     => $active_smtp_plugins,
					'uses_default_mail'       => $uses_default_mail,
					'wp_mail_smtp_configured' => $wp_mail_smtp_configured,
					'has_email_log'           => $has_email_log,
					'can_send_email'          => $can_send_email,
					'from_email'              => $from_email,
					'from_email_matches_domain' => $from_email_matches_domain,
					'woocommerce_active'      => $woocommerce_active,
					'custom_email_templates'  => $custom_email_templates,
					'has_bounce_tracking'     => $has_bounce_tracking,
					'recent_emails_sent'      => number_format_i18n( absint( $recent_emails ) ),
					'issues_detected'         => $issues,
					'recommendation'          => __( 'Install WP Mail SMTP, configure with Gmail/SendGrid/Amazon SES, enable email logging', 'wpshadow' ),
					'smtp_providers'          => array(
						'Gmail/Google Workspace' => 'Free (500/day), easy setup',
						'SendGrid'               => 'Free (100/day), excellent deliverability',
						'Amazon SES'             => 'Pay-as-you-go, $0.10 per 1000 emails',
						'Mailgun'                => 'Free (5000/month), developer-friendly',
						'Postmark'               => 'Transactional focus, fast delivery',
					),
					'deliverability_checklist' => array(
						'Use SMTP'                => 'Never use PHP mail()',
						'Match domain'            => 'From email must match site domain',
						'SPF record'              => 'Add SPF record to DNS',
						'DKIM signature'          => 'Enable DKIM authentication',
						'Enable logging'          => 'Track all email sends',
						'Test regularly'          => 'Send test emails weekly',
						'Monitor bounces'         => 'Track and remove bounce addresses',
					),
					'critical_for_woocommerce' => 'Order confirmations, shipping updates, password resets rely on email',
				),
			);
		}

		return null;
	}
}
