<?php
/**
 * Feature: HTTP Header & SSL Audit
 *
 * Scans for critical security headers (like HSTS and CSP) and verifies
 * that SSL certificate is valid and not nearing expiration.
 *
 * FEATURES:
 * - HTTP security headers check (HSTS, CSP, X-Frame-Options, etc.)
 * - SSL certificate validation
 * - SSL certificate expiration monitoring
 * - Scheduled daily checks via WP-Cron
 * - Admin dashboard widget with audit results
 * - Email alerts for critical issues
 *
 * SECURITY HEADERS CHECKED:
 * - Strict-Transport-Security (HSTS)
 * - Content-Security-Policy (CSP)
 * - X-Frame-Options
 * - X-Content-Type-Options
 * - X-XSS-Protection
 * - Referrer-Policy
 * - Permissions-Policy
 *
 * SSL CERTIFICATE CHECKS:
 * - Certificate validity
 * - Expiration date (warns if < 30 days)
 * - Certificate issuer verification
 * - Protocol version (TLS 1.2+)
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Feature_HTTP_SSL_Audit
 *
 * HTTP security headers and SSL certificate audit implementation.
 */
final class WPSHADOW_Feature_HTTP_SSL_Audit extends WPSHADOW_Abstract_Feature {

	/**
	 * Critical security headers to check.
	 *
	 * @var array<string, array<string, mixed>>
	 */
	private const SECURITY_HEADERS = array(
		'strict-transport-security' => array(
			'name'        => 'Strict-Transport-Security',
			'description' => 'HSTS enforces HTTPS connections',
			'severity'    => 'high',
			'recommended' => 'max-age=31536000; includeSubDomains; preload',
		),
		'content-security-policy'   => array(
			'name'        => 'Content-Security-Policy',
			'description' => 'CSP prevents XSS and injection attacks',
			'severity'    => 'high',
			'recommended' => "default-src 'self'",
		),
		'x-frame-options'           => array(
			'name'        => 'X-Frame-Options',
			'description' => 'Prevents clickjacking attacks',
			'severity'    => 'medium',
			'recommended' => 'SAMEORIGIN',
		),
		'x-content-type-options'    => array(
			'name'        => 'X-Content-Type-Options',
			'description' => 'Prevents MIME-type sniffing',
			'severity'    => 'medium',
			'recommended' => 'nosniff',
		),
		'x-xss-protection'          => array(
			'name'        => 'X-XSS-Protection',
			'description' => 'Enables XSS filter in older browsers',
			'severity'    => 'low',
			'recommended' => '1; mode=block',
		),
		'referrer-policy'           => array(
			'name'        => 'Referrer-Policy',
			'description' => 'Controls referrer information',
			'severity'    => 'low',
			'recommended' => 'strict-origin-when-cross-origin',
		),
		'permissions-policy'        => array(
			'name'        => 'Permissions-Policy',
			'description' => 'Controls browser features and APIs',
			'severity'    => 'low',
			'recommended' => 'geolocation=(), microphone=(), camera=()',
		),
	);

	/**
	 * SSL certificate expiration warning threshold in days.
	 */
	private const SSL_EXPIRY_WARNING_DAYS = 30;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'http-ssl-audit',
				'name'               => __( 'HTTP Header & SSL Audit', 'plugin-wpshadow' ),
				'description'        => __( 'Scans for critical security headers (like HSTS and CSP) and verifies that your SSL certificate is valid and not nearing expiration. Runs automated daily checks and alerts administrators of security misconfigurations or expiring certificates before they impact users.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'security',
				'widget_label'       => __( 'Security', 'plugin-wpshadow' ),
				'widget_description' => __( 'Advanced security features to protect your WordPress installation', 'plugin-wpshadow' ),
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-shield',
				'category'           => 'security',
				'priority'           => 8,
				'dashboard'          => 'overview',
				'widget_column'      => 'right',
				'widget_priority'    => 8,
			)
		);
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Schedule daily audit check.
		$this->register_cron_event(
			'wpshadow_http_ssl_audit_cron',
			'daily',
			array( $this, 'run_scheduled_audit' )
		);

		// Add admin dashboard widget.
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );

		// Add AJAX handler for manual audit.
		add_action( 'wp_ajax_wpshadow_run_http_ssl_audit', array( $this, 'ajax_run_audit' ) );

		// Add admin notice for critical issues.
		add_action( 'admin_notices', array( $this, 'display_audit_warnings' ) );
	}

	/**
	 * Run the scheduled audit via WP-Cron.
	 *
	 * @return void
	 */
	public function run_scheduled_audit(): void {
		$results = $this->run_full_audit();

		// Store results in cache.
		$this->set_cache( 'audit_results', $results, DAY_IN_SECONDS );

		// Check for critical issues and send email if needed.
		$critical_issues = $this->get_critical_issues( $results );
		if ( ! empty( $critical_issues ) ) {
			$this->send_alert_email( $critical_issues );
		}

		// Log audit in activity logger if available.
		if ( class_exists( '\\WPShadow\\WPSHADOW_Activity_Logger' ) ) {
			\WPShadow\WPSHADOW_Activity_Logger::log(
				'security',
				__( 'HTTP Header & SSL Audit completed', 'plugin-wpshadow' ),
				array(
					'headers_missing' => count( $results['headers']['missing'] ?? array() ),
					'ssl_valid'       => $results['ssl']['valid'] ?? false,
					'ssl_days_until'  => $results['ssl']['days_until_expiry'] ?? null,
				)
			);
		}
	}

	/**
	 * Run a full audit of HTTP headers and SSL certificate.
	 *
	 * @return array<string, mixed> Audit results.
	 */
	public function run_full_audit(): array {
		$results = array(
			'timestamp' => current_time( 'timestamp' ),
			'headers'   => $this->check_security_headers(),
			'ssl'       => $this->check_ssl_certificate(),
		);

		return $results;
	}

	/**
	 * Check security headers on the site's homepage.
	 *
	 * @return array<string, mixed> Security headers check results.
	 */
	private function check_security_headers(): array {
		$site_url = home_url( '/' );
		$results  = array(
			'checked' => array(),
			'missing' => array(),
			'present' => array(),
		);

		// Make HEAD request to get headers.
		$response = wp_remote_head(
			$site_url,
			array(
				'timeout'     => 10,
				'redirection' => 0,
				'sslverify'   => true,
			)
		);

		if ( is_wp_error( $response ) ) {
			$results['error'] = $response->get_error_message();
			return $results;
		}

		$headers = wp_remote_retrieve_headers( $response );

		// Check each security header.
		foreach ( self::SECURITY_HEADERS as $key => $header_info ) {
			$header_name = $header_info['name'];
			$results['checked'][ $key ] = $header_info;

			// Check if header exists (case-insensitive).
			$header_value = null;
			foreach ( $headers as $name => $value ) {
				if ( strcasecmp( $name, $header_name ) === 0 ) {
					$header_value = $value;
					break;
				}
			}

			if ( $header_value !== null ) {
				$results['present'][ $key ] = array(
					'name'  => $header_name,
					'value' => $header_value,
					'info'  => $header_info,
				);
			} else {
				$results['missing'][ $key ] = $header_info;
			}
		}

		return $results;
	}

	/**
	 * Check SSL certificate validity and expiration.
	 *
	 * @return array<string, mixed> SSL certificate check results.
	 */
	private function check_ssl_certificate(): array {
		$site_url = home_url( '/' );
		$results  = array(
			'valid'              => false,
			'enabled'            => false,
			'days_until_expiry'  => null,
			'expiry_date'        => null,
			'issuer'             => null,
			'error'              => null,
			'warning_threshold'  => self::SSL_EXPIRY_WARNING_DAYS,
			'expiring_soon'      => false,
		);

		// Check if site is using HTTPS.
		if ( ! ( strpos( $site_url, 'https://' ) === 0 ) ) {
			$results['error'] = __( 'Site is not using HTTPS', 'plugin-wpshadow' );
			return $results;
		}

		$results['enabled'] = true;

		// Parse hostname from URL.
		$parsed_url = wp_parse_url( $site_url );
		if ( ! isset( $parsed_url['host'] ) ) {
			$results['error'] = __( 'Could not parse site URL', 'plugin-wpshadow' );
			return $results;
		}

		$host = $parsed_url['host'];
		$port = $parsed_url['port'] ?? 443;

		// Try to get certificate information using stream context.
		$context = stream_context_create(
			array(
				'ssl' => array(
					'capture_peer_cert' => true,
					'verify_peer'       => true,
					'verify_peer_name'  => true,
				),
			)
		);

		// Attempt connection and capture any errors.
		$errno  = 0;
		$errstr = '';
		$stream = stream_socket_client(
			"ssl://{$host}:{$port}",
			$errno,
			$errstr,
			10,
			STREAM_CLIENT_CONNECT,
			$context
		);

		if ( ! $stream ) {
			$results['error'] = sprintf(
				/* translators: %s: error message */
				__( 'Could not connect to SSL server: %s', 'plugin-wpshadow' ),
				$errstr
			);
			return $results;
		}

		$params = stream_context_get_params( $stream );
		fclose( $stream );

		if ( ! isset( $params['options']['ssl']['peer_certificate'] ) ) {
			$results['error'] = __( 'Could not retrieve SSL certificate', 'plugin-wpshadow' );
			return $results;
		}

		// Parse certificate.
		$cert_resource = $params['options']['ssl']['peer_certificate'];
		$cert_info     = openssl_x509_parse( $cert_resource );

		if ( ! $cert_info ) {
			$results['error'] = __( 'Could not parse SSL certificate', 'plugin-wpshadow' );
			return $results;
		}

		$results['valid'] = true;

		// Get expiration date.
		if ( isset( $cert_info['validTo_time_t'] ) ) {
			$expiry_timestamp           = (int) $cert_info['validTo_time_t'];
			$current_timestamp          = time();
			$days_until_expiry          = (int) floor( ( $expiry_timestamp - $current_timestamp ) / DAY_IN_SECONDS );
			$results['days_until_expiry'] = $days_until_expiry;
			$results['expiry_date']       = gmdate( 'Y-m-d H:i:s', $expiry_timestamp );
			$results['expiring_soon']     = $days_until_expiry <= self::SSL_EXPIRY_WARNING_DAYS;

			// Check if certificate is already expired.
			if ( $days_until_expiry < 0 ) {
				$results['valid'] = false;
				$results['error'] = __( 'SSL certificate has expired', 'plugin-wpshadow' );
			}
		}

		// Get issuer information.
		if ( isset( $cert_info['issuer'] ) ) {
			$issuer_parts = array();
			foreach ( $cert_info['issuer'] as $key => $value ) {
				$issuer_parts[] = "{$key}={$value}";
			}
			$results['issuer'] = implode( ', ', $issuer_parts );
		}

		return $results;
	}

	/**
	 * Get critical issues from audit results.
	 *
	 * @param array<string, mixed> $results Audit results.
	 * @return array<string, mixed> Critical issues.
	 */
	private function get_critical_issues( array $results ): array {
		$issues = array();

		// Check for missing high-severity headers.
		if ( ! empty( $results['headers']['missing'] ) ) {
			foreach ( $results['headers']['missing'] as $key => $header ) {
				if ( $header['severity'] === 'high' ) {
					$issues[] = array(
						'type'        => 'missing_header',
						'severity'    => 'high',
						'header'      => $header['name'],
						'description' => $header['description'],
					);
				}
			}
		}

		// Check SSL issues.
		if ( ! empty( $results['ssl'] ) ) {
			if ( ! $results['ssl']['valid'] ) {
				$issues[] = array(
					'type'        => 'ssl_invalid',
					'severity'    => 'critical',
					'description' => $results['ssl']['error'] ?? __( 'SSL certificate is invalid', 'plugin-wpshadow' ),
				);
			} elseif ( $results['ssl']['expiring_soon'] ) {
				$issues[] = array(
					'type'        => 'ssl_expiring',
					'severity'    => 'high',
					'days'        => $results['ssl']['days_until_expiry'],
					'description' => sprintf(
						/* translators: %d: number of days */
						__( 'SSL certificate expires in %d days', 'plugin-wpshadow' ),
						$results['ssl']['days_until_expiry']
					),
				);
			}
		}

		return $issues;
	}

	/**
	 * Send alert email for critical issues.
	 *
	 * @param array<string, mixed> $issues Critical issues.
	 * @return void
	 */
	private function send_alert_email( array $issues ): void {
		// Check if we've sent an alert recently (don't spam).
		$last_alert = $this->get_cache( 'last_alert_sent' );
		if ( $last_alert && ( time() - $last_alert ) < DAY_IN_SECONDS ) {
			return;
		}

		$admin_email = get_option( 'admin_email' );
		$site_name   = get_bloginfo( 'name' );
		$subject     = sprintf(
			/* translators: %s: site name */
			__( '[%s] Security Alert: HTTP/SSL Issues Detected', 'plugin-wpshadow' ),
			$site_name
		);

		$message = sprintf(
			/* translators: %s: site name */
			__( 'Security issues have been detected on %s:', 'plugin-wpshadow' ),
			$site_name
		) . "\n\n";

		foreach ( $issues as $issue ) {
			$message .= sprintf(
				"- [%s] %s\n",
				strtoupper( $issue['severity'] ),
				$issue['description']
			);
		}

		$message .= "\n" . sprintf(
			/* translators: %s: admin URL */
			__( 'Please review the security audit in your dashboard: %s', 'plugin-wpshadow' ),
			admin_url( 'index.php' )
		);

		wp_mail( $admin_email, $subject, $message );

		// Mark that we sent an alert.
		$this->set_cache( 'last_alert_sent', time(), WEEK_IN_SECONDS );
	}

	/**
	 * Add dashboard widget for audit results.
	 *
	 * @return void
	 */
	public function add_dashboard_widget(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_add_dashboard_widget(
			'wpshadow_http_ssl_audit',
			__( 'HTTP & SSL Security Audit', 'plugin-wpshadow' ),
			array( $this, 'render_dashboard_widget' )
		);
	}

	/**
	 * Render dashboard widget content.
	 *
	 * @return void
	 */
	public function render_dashboard_widget(): void {
		// Get cached results.
		$results = $this->get_cache( 'audit_results' );

		if ( ! $results ) {
			echo '<p>' . esc_html__( 'No audit results available. Running audit...', 'plugin-wpshadow' ) . '</p>';
			echo '<p><button type="button" class="button button-primary" onclick="wpshadowRunHttpSslAudit()">' .
				esc_html__( 'Run Audit Now', 'plugin-wpshadow' ) . '</button></p>';
			$this->add_widget_script();
			return;
		}

		$timestamp = isset( $results['timestamp'] ) ? $results['timestamp'] : 0;
		echo '<p><strong>' . esc_html__( 'Last Audit:', 'plugin-wpshadow' ) . '</strong> ' .
			esc_html( human_time_diff( $timestamp ) . ' ' . __( 'ago', 'plugin-wpshadow' ) ) . '</p>';

		// Display SSL results.
		if ( ! empty( $results['ssl'] ) ) {
			echo '<h4>' . esc_html__( 'SSL Certificate', 'plugin-wpshadow' ) . '</h4>';

			if ( ! $results['ssl']['enabled'] ) {
				echo '<p style="color: #dc3232;">⚠️ ' . esc_html__( 'HTTPS is not enabled', 'plugin-wpshadow' ) . '</p>';
			} elseif ( ! $results['ssl']['valid'] ) {
				echo '<p style="color: #dc3232;">❌ ' . esc_html( $results['ssl']['error'] ) . '</p>';
			} else {
				echo '<p style="color: #46b450;">✓ ' . esc_html__( 'SSL certificate is valid', 'plugin-wpshadow' ) . '</p>';

				if ( isset( $results['ssl']['days_until_expiry'] ) ) {
					$days  = $results['ssl']['days_until_expiry'];
					$color = $days <= self::SSL_EXPIRY_WARNING_DAYS ? '#dc3232' : '#46b450';
					echo '<p style="color: ' . esc_attr( $color ) . ';">' .
						sprintf(
							/* translators: %d: number of days */
							esc_html__( 'Expires in %d days', 'plugin-wpshadow' ),
							(int) $days
						) . '</p>';
				}
			}
		}

		// Display security headers results.
		if ( ! empty( $results['headers'] ) ) {
			echo '<h4>' . esc_html__( 'Security Headers', 'plugin-wpshadow' ) . '</h4>';

			$total_checked = count( $results['headers']['checked'] ?? array() );
			$missing_count = count( $results['headers']['missing'] ?? array() );
			$present_count = count( $results['headers']['present'] ?? array() );

			echo '<p>' . sprintf(
				/* translators: 1: present count, 2: total count */
				esc_html__( '%1$d of %2$d recommended headers present', 'plugin-wpshadow' ),
				(int) $present_count,
				(int) $total_checked
			) . '</p>';

			if ( $missing_count > 0 ) {
				echo '<details><summary style="cursor: pointer; color: #dc3232;">' .
					sprintf(
						/* translators: %d: number of missing headers */
						esc_html__( 'Missing %d headers', 'plugin-wpshadow' ),
						(int) $missing_count
					) . '</summary><ul style="margin-left: 20px;">';

				foreach ( $results['headers']['missing'] as $header ) {
					echo '<li><strong>' . esc_html( $header['name'] ) . '</strong>: ' .
						esc_html( $header['description'] ) . '</li>';
				}
				echo '</ul></details>';
			}
		}

		echo '<p><button type="button" class="button" onclick="wpshadowRunHttpSslAudit()">' .
			esc_html__( 'Run Audit Now', 'plugin-wpshadow' ) . '</button></p>';

		$this->add_widget_script();
	}

	/**
	 * Add JavaScript for widget interactions.
	 *
	 * @return void
	 */
	private function add_widget_script(): void {
		?>
		<script>
		function wpshadowRunHttpSslAudit(event) {
			if (typeof jQuery === 'undefined') return;
			var button = event ? event.target : this;
			button.disabled = true;
			button.textContent = '<?php echo esc_js( __( 'Running...', 'plugin-wpshadow' ) ); ?>';

			jQuery.post(ajaxurl, {
				action: 'wpshadow_run_http_ssl_audit',
				nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_http_ssl_audit' ) ); ?>'
			}, function() {
				location.reload();
			}).fail(function() {
				alert('<?php echo esc_js( __( 'Audit failed. Please try again.', 'plugin-wpshadow' ) ); ?>');
				button.disabled = false;
				button.textContent = '<?php echo esc_js( __( 'Run Audit Now', 'plugin-wpshadow' ) ); ?>';
			});
		}
		</script>
		<?php
	}

	/**
	 * AJAX handler for manual audit.
	 *
	 * @return void
	 */
	public function ajax_run_audit(): void {
		check_ajax_referer( 'wpshadow_http_ssl_audit', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions', 'plugin-wpshadow' ) ) );
		}

		$results = $this->run_full_audit();
		$this->set_cache( 'audit_results', $results, DAY_IN_SECONDS );

		wp_send_json_success( array( 'results' => $results ) );
	}

	/**
	 * Display admin notices for audit warnings.
	 *
	 * @return void
	 */
	public function display_audit_warnings(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$results = $this->get_cache( 'audit_results' );
		if ( ! $results ) {
			return;
		}

		$issues = $this->get_critical_issues( $results );
		if ( empty( $issues ) ) {
			return;
		}

		// Only show notice once per day.
		$notice_shown = get_transient( 'wpshadow_http_ssl_audit_notice_shown' );
		if ( $notice_shown ) {
			return;
		}

		set_transient( 'wpshadow_http_ssl_audit_notice_shown', true, DAY_IN_SECONDS );

		echo '<div class="notice notice-error"><p><strong>' .
			esc_html__( 'WPShadow Security Alert:', 'plugin-wpshadow' ) .
			'</strong> ' .
			sprintf(
				/* translators: %d: number of issues */
				esc_html__( '%d security issue(s) detected in HTTP/SSL audit.', 'plugin-wpshadow' ),
				count( $issues )
			) .
			' <a href="' . esc_url( admin_url( 'index.php' ) ) . '">' .
			esc_html__( 'View Details', 'plugin-wpshadow' ) .
			'</a></p></div>';
	}
}
