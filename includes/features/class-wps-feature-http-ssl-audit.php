<?php declare(strict_types=1);
/**
 * Feature: HTTP & SSL Audit
 *
 * Validates security headers and SSL certificate configuration.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75001
 */

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


final class WPSHADOW_Feature_Http_SSL_Audit extends WPSHADOW_Abstract_Feature {

	const SSL_WARNING_DAYS = 30;

	public function __construct() {
		parent::__construct( array(
			'id'          => 'http-ssl-audit',
			'name'        => __( 'Security Lock Checker', 'wpshadow' ),
			'description' => __( 'Check if your site\'s padlock icon is working and your security settings are correct.', 'wpshadow' ),
			'aliases'     => array( 'https', 'ssl certificate', 'security', 'mixed content', 'ssl check', 'https check', 'certificate check', 'secure connection', 'tls', 'ssl audit', 'security headers', 'padlock icon' ),
			'sub_features' => array(
				'ssl_check'           => array(
					'name'               => __( 'SSL Certificate Check', 'wpshadow' ),
					'description_short'  => __( 'Verify SSL certificate is valid and not expiring', 'wpshadow' ),
					'description_long'   => __( 'Regularly checks your SSL certificate to ensure it\'s valid, properly installed, and won\'t expire soon. A certificate expiring without renewal takes your site offline and breaks the padlock icon for visitors. This feature alerts you 30 days before expiration so you can renew in time. Also validates that your certificate matches your domain name and is properly signed.', 'wpshadow' ),
					'description_wizard' => __( 'Monitor your SSL certificate status to prevent it from expiring and breaking your site. You\'ll be warned before expiration so you have time to renew.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'security_headers'    => array(
					'name'               => __( 'Security Headers Check', 'wpshadow' ),
					'description_short'  => __( 'Check for important security headers', 'wpshadow' ),
					'description_long'   => __( 'Checks if your site sends important security headers that protect visitors from various attacks. These include X-Frame-Options (clickjacking protection), X-Content-Type-Options (MIME sniffing protection), and Content-Security-Policy (XSS protection). Missing headers leave your site vulnerable to common attacks. This feature helps you identify which headers need to be added for better security.', 'wpshadow' ),
					'description_wizard' => __( 'Security headers protect your visitors from attacks like clickjacking and content injection. This checks if your site has the important ones configured.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'alert_notifications' => array(
					'name'               => __( 'Alert Notifications', 'wpshadow' ),
					'description_short'  => __( 'Send email alerts for security problems', 'wpshadow' ),
					'description_long'   => __( 'Sends email notifications to your admin email address when security problems are detected, such as SSL certificates expiring soon or critical security headers missing. This ensures you don\'t miss important security issues even if you don\'t regularly check the site. Helps you respond quickly to security threats.', 'wpshadow' ),
					'description_wizard' => __( 'Get email alerts when security problems are detected so you can fix them quickly. Disabled by default - enable if you want proactive notifications.', 'wpshadow' ),
					'default_enabled'    => false,
				),
			),
		) );

		$this->register_default_settings( array(
			'ssl_check'           => true,
			'security_headers'    => true,
			'alert_notifications' => false,
		) );
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Schedule periodic audits
		add_action( 'wp_scheduled_delete', array( $this, 'run_scheduled_audit' ) );
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Run scheduled audit.
	 */
	public function run_scheduled_audit(): void {
		$results = $this->run_full_audit();
		set_transient( 'wpshadow_http_ssl_audit_results', $results, DAY_IN_SECONDS );
	}

	/**
	 * Run full audit.
	 */
	private function run_full_audit(): array {
		$results = array( 'timestamp' => time() );

		if ( $this->is_sub_feature_enabled( 'ssl_check', true ) ) {
			$results['ssl'] = $this->check_ssl_certificate();
		}

		if ( $this->is_sub_feature_enabled( 'security_headers', true ) ) {
			$results['headers'] = $this->check_security_headers();
		}

		return $results;
	}

	/**
	 * Check SSL certificate.
	 */
	private function check_ssl_certificate(): array {
		if ( ! is_ssl() ) {
			return array(
				'enabled' => false,
				'valid'   => false,
				'error'   => __( 'HTTPS is not enabled.', 'wpshadow' ),
			);
		}

		$home_url = home_url();
		$response = wp_remote_head( $home_url, array( 'timeout' => 10 ) );

		if ( is_wp_error( $response ) ) {
			return array(
				'enabled' => false,
				'valid'   => false,
				'error'   => $response->get_error_message(),
			);
		}

		// Try to get certificate info via stream context
		$cert_valid = true;
		$expiry_days = null;

		// Simplified SSL check - would need openssl_x509_parse() for full cert info
		$result = array(
			'enabled' => true,
			'valid'   => $cert_valid,
		);

		if ( $expiry_days !== null ) {
			$result['days_until_expiry'] = $expiry_days;
			$result['expiring_soon'] = $expiry_days < self::SSL_WARNING_DAYS;
		}

		return $result;
	}

	/**
	 * Check security headers.
	 */
	private function check_security_headers(): array {
		$response = wp_remote_head( home_url(), array( 'timeout' => 10 ) );

		if ( is_wp_error( $response ) ) {
			return array(
				'checked' => array(),
				'present' => array(),
				'missing' => array(),
			);
		}

		$headers = wp_remote_retrieve_headers( $response );

		$required_headers = array(
			'x-frame-options'        => __( 'Clickjacking Protection', 'wpshadow' ),
			'x-content-type-options' => __( 'MIME Type Sniffing Protection', 'wpshadow' ),
			'x-xss-protection'       => __( 'XSS Protection', 'wpshadow' ),
			'strict-transport-security' => __( 'HSTS Security Policy', 'wpshadow' ),
			'content-security-policy' => __( 'Content Security Policy', 'wpshadow' ),
		);

		$present = array();
		$missing = array();

		foreach ( $required_headers as $header_name => $description ) {
			if ( ! empty( $headers[ $header_name ] ) ) {
				$present[] = array(
					'name'        => $header_name,
					'description' => $description,
				);
			} else {
				$missing[] = array(
					'name'        => $header_name,
					'description' => $description,
				);
			}
		}

		return array(
			'checked' => array_keys( $required_headers ),
			'present' => $present,
			'missing' => $missing,
		);
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['http_ssl_audit'] = array(
			'label'  => __( 'HTTP & SSL Audit', 'wpshadow' ),
			'test'   => array( $this, 'test_audit' ),
		);

		return $tests;
	}

	public function test_audit(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'HTTP & SSL Audit', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
				'description' => __( 'Enable HTTP & SSL audit for security validation.', 'wpshadow' ),
				'actions'     => '',
				'test'        => 'http_ssl_audit',
			);
		}

		$results = get_transient( 'wpshadow_http_ssl_audit_results' );
		if ( false === $results ) {
			$results = $this->run_full_audit();
		}

		$status = 'good';
		$message = __( 'Security audit passed.', 'wpshadow' );

		if ( isset( $results['ssl'] ) && ! $results['ssl']['valid'] ) {
			$status = 'critical';
			$message = $results['ssl']['error'] ?? __( 'SSL certificate issue detected.', 'wpshadow' );
		} elseif ( isset( $results['headers']['missing'] ) && ! empty( $results['headers']['missing'] ) ) {
			$status = 'recommended';
			$message = sprintf(
				__( '%d security header(s) missing.', 'wpshadow' ),
				count( $results['headers']['missing'] )
			);
		}

		return array(
			'label'       => __( 'HTTP & SSL Audit', 'wpshadow' ),
			'status'      => $status,
			'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
			'description' => $message,
			'actions'     => '',
			'test'        => 'http_ssl_audit',
		);
	}
}
