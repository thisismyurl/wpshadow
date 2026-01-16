<?php
/**
 * Feature: Hotlink Protection
 *
 * Provides comprehensive hotlink protection to prevent bandwidth theft:
 * - Apache .htaccess rules for referrer validation
 * - Nginx configuration guidance
 * - CDN-level blocking recommendations
 * - Configurable allowed domains and file types
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_Hotlink_Protection
 *
 * Hotlink protection implementation to prevent unauthorized media embedding.
 */
final class WPSHADOW_Feature_Hotlink_Protection extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'hotlink-protection',
				'name'               => __( 'Hotlink Protection', 'plugin-wpshadow' ),
				'description'        => __( 'Prevents other sites from embedding your images or media directly from your server, protecting your bandwidth. Automatically configures Apache .htaccess rules, provides Nginx configuration guidance, and includes CDN-level blocking recommendations for Cloudflare, KeyCDN, and other providers.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'security',
				'widget_label'       => __( 'Security', 'plugin-wpshadow' ),
				'widget_description' => __( 'Advanced security features to protect your WordPress installation', 'plugin-wpshadow' ),
			)
		);
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		// Always check for cleanup, even if disabled.
		add_action( 'admin_init', array( $this, 'check_cleanup' ), 1 );

		if ( ! static::is_enabled() ) {
			return;
		}

		// Initialize on admin_init to configure hotlink protection.
		add_action( 'admin_init', array( $this, 'configure_hotlink_protection' ), 5 );

		// Add admin notice for Nginx users.
		add_action( 'admin_notices', array( $this, 'show_nginx_guidance' ) );

		// Add settings page actions.
		add_action( 'admin_init', array( $this, 'handle_settings_update' ) );
	}

	/**
	 * Check if feature was disabled and perform cleanup.
	 *
	 * @return void
	 */
	public function check_cleanup(): void {
		// Check if feature is disabled but was previously configured.
		if ( ! static::is_enabled() && $this->get_setting( 'apache_configured', false ) ) {
			$this->remove_apache_protection();
			$this->update_setting( 'apache_configured', false );
			delete_transient( 'wpshadow_hotlink_protection_last_config' );
		}
	}

	/**
	 * Configure hotlink protection based on server environment.
	 *
	 * @return void
	 */
	public function configure_hotlink_protection(): void {
		// Only run configuration once per day to avoid performance impact.
		$last_config = get_transient( 'wpshadow_hotlink_protection_last_config' );
		if ( false !== $last_config ) {
			return;
		}

		// Set transient for 24 hours.
		set_transient( 'wpshadow_hotlink_protection_last_config', time(), DAY_IN_SECONDS );

		// Detect server type.
		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';
		$is_apache       = stripos( $server_software, 'apache' ) !== false || stripos( $server_software, 'litespeed' ) !== false;
		$is_nginx        = stripos( $server_software, 'nginx' ) !== false;

		if ( $is_apache ) {
			$this->configure_apache_protection();
		} elseif ( $is_nginx ) {
			// Nginx requires manual configuration - we'll show guidance.
			$this->update_setting( 'nginx_detected', true );
		}
	}

	/**
	 * Configure Apache/LiteSpeed hotlink protection via .htaccess.
	 *
	 * @return void
	 */
	private function configure_apache_protection(): void {
		$uploads_dir = wp_upload_dir();
		$htaccess_file = trailingslashit( $uploads_dir['basedir'] ) . '.htaccess';

		// Get configured settings.
		$allowed_domains = $this->get_setting( 'allowed_domains', array() );
		$protected_types = $this->get_setting( 'protected_types', array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg' ) );

		// Add current domain if not in allowed list.
		$site_domain = parse_url( home_url(), PHP_URL_HOST );
		if ( $site_domain && ! in_array( $site_domain, $allowed_domains, true ) ) {
			$allowed_domains[] = $site_domain;
		}

		// Build valid_referers list.
		$valid_referers = array( 'none', 'blocked' );
		foreach ( $allowed_domains as $domain ) {
			$valid_referers[] = sanitize_text_field( $domain );
			$valid_referers[] = '*.' . sanitize_text_field( $domain );
		}

		// Build file extensions pattern.
		$extensions = implode( '|', array_map( 'preg_quote', $protected_types ) );

		// Build .htaccess content.
		$htaccess_content  = "\n# BEGIN WPShadow Hotlink Protection\n";
		$htaccess_content .= "<IfModule mod_rewrite.c>\n";
		$htaccess_content .= "    RewriteEngine On\n";
		$htaccess_content .= "    RewriteCond %{HTTP_REFERER} !^$\n";
		
		foreach ( $valid_referers as $referer ) {
			if ( 'none' === $referer || 'blocked' === $referer ) {
				continue;
			}
			$htaccess_content .= "    RewriteCond %{HTTP_REFERER} !^https?://([^.]+\\.)?{$referer} [NC]\n";
		}
		
		$htaccess_content .= "    RewriteRule \\.({$extensions})$ - [F,L]\n";
		$htaccess_content .= "</IfModule>\n";
		$htaccess_content .= "# END WPShadow Hotlink Protection\n";

		// Read existing .htaccess content.
		$existing_content = '';
		if ( file_exists( $htaccess_file ) ) {
			$existing_content = file_get_contents( $htaccess_file );
			if ( false === $existing_content ) {
				$existing_content = '';
			}
		}

		// Remove old WPShadow hotlink protection rules if they exist.
		$existing_content = preg_replace(
			'/\n?# BEGIN WPShadow Hotlink Protection.*?# END WPShadow Hotlink Protection\n?/s',
			'',
			$existing_content
		);

		// Combine content.
		$new_content = $existing_content . $htaccess_content;

		// Attempt to write the file.
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		$result = @file_put_contents( $htaccess_file, $new_content );

		if ( false === $result ) {
			$this->add_admin_notice(
				'htaccess_write_failed',
				__( 'Hotlink Protection: Could not write to uploads/.htaccess. Please check file permissions.', 'plugin-wpshadow' ),
				'error'
			);
		} else {
			$this->update_setting( 'apache_configured', true );
			$this->add_admin_notice(
				'hotlink_protection_active',
				__( 'Hotlink Protection: Successfully configured Apache rules in uploads/.htaccess', 'plugin-wpshadow' ),
				'success'
			);
		}
	}

	/**
	 * Remove hotlink protection when feature is disabled.
	 *
	 * @return void
	 */
	public function remove_apache_protection(): void {
		$uploads_dir = wp_upload_dir();
		$htaccess_file = trailingslashit( $uploads_dir['basedir'] ) . '.htaccess';

		if ( ! file_exists( $htaccess_file ) ) {
			return;
		}

		$existing_content = file_get_contents( $htaccess_file );
		if ( false === $existing_content ) {
			return;
		}

		// Remove WPShadow hotlink protection rules.
		$new_content = preg_replace(
			'/\n?# BEGIN WPShadow Hotlink Protection.*?# END WPShadow Hotlink Protection\n?/s',
			'',
			$existing_content
		);

		if ( $new_content !== $existing_content ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
			@file_put_contents( $htaccess_file, $new_content );
		}
	}

	/**
	 * Show Nginx configuration guidance to admins.
	 *
	 * @return void
	 */
	public function show_nginx_guidance(): void {
		// Only show if Nginx is detected and user hasn't dismissed.
		if ( ! $this->get_setting( 'nginx_detected', false ) ) {
			return;
		}

		$dismissed = get_user_meta( get_current_user_id(), 'wpshadow_hotlink_nginx_dismissed', true );
		if ( $dismissed ) {
			return;
		}

		$allowed_domains = $this->get_setting( 'allowed_domains', array() );
		$protected_types = $this->get_setting( 'protected_types', array( 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg' ) );

		// Add current domain if not in allowed list.
		$site_domain = parse_url( home_url(), PHP_URL_HOST );
		if ( $site_domain && ! in_array( $site_domain, $allowed_domains, true ) ) {
			$allowed_domains[] = $site_domain;
		}

		$extensions = implode( '|', $protected_types );
		$domain_list = implode( ' ', array_map(
			function( $domain ) {
				return esc_html( $domain ) . ' *.' . esc_html( $domain );
			},
			$allowed_domains
		) );

		?>
		<div class="notice notice-info is-dismissible" data-dismissible="wpshadow-hotlink-nginx">
			<h3><?php esc_html_e( 'WPShadow Hotlink Protection - Nginx Configuration', 'plugin-wpshadow' ); ?></h3>
			<p><?php esc_html_e( 'Nginx server detected. Please add the following configuration to your server config:', 'plugin-wpshadow' ); ?></p>
			<pre style="background: #f5f5f5; padding: 10px; overflow-x: auto;"><code>location ~ \.(<?php echo esc_html( $extensions ); ?>)$ {
   valid_referers none blocked <?php echo esc_html( $domain_list ); ?>;
   if ($invalid_referer) {
       return 403;
   }
}</code></pre>
			<p>
				<strong><?php esc_html_e( 'CDN-Level Protection (Recommended):', 'plugin-wpshadow' ); ?></strong><br>
				<?php esc_html_e( 'For even better protection, enable hotlink protection at your CDN level:', 'plugin-wpshadow' ); ?>
			</p>
			<ul style="list-style: disc; margin-left: 20px;">
				<li><strong>Cloudflare:</strong> <?php esc_html_e( 'Enable "Scrape Shield" → "Hotlink Protection" in your dashboard', 'plugin-wpshadow' ); ?></li>
				<li><strong>KeyCDN:</strong> <?php esc_html_e( 'Enable "Zone Referrer" in zone settings', 'plugin-wpshadow' ); ?></li>
				<li><strong>BunnyCDN:</strong> <?php esc_html_e( 'Use "Token Authentication" or "Allowed Referrers" in pull zone settings', 'plugin-wpshadow' ); ?></li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Handle settings form submission.
	 *
	 * @return void
	 */
	public function handle_settings_update(): void {
		// Check if this is a settings update request.
		if ( ! isset( $_POST['wpshadow_hotlink_protection_nonce'] ) ) {
			return;
		}

		// Verify nonce.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wpshadow_hotlink_protection_nonce'] ) ), 'wpshadow_hotlink_protection_settings' ) ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Process allowed domains.
		$allowed_domains = array();
		if ( isset( $_POST['wpshadow_hotlink_allowed_domains'] ) ) {
			$domains_raw = sanitize_textarea_field( wp_unslash( $_POST['wpshadow_hotlink_allowed_domains'] ) );
			$domains_array = explode( "\n", $domains_raw );
			foreach ( $domains_array as $domain ) {
				$domain = trim( $domain );
				if ( ! empty( $domain ) ) {
					// Remove protocol if present.
					$domain = preg_replace( '#^https?://#', '', $domain );
					$allowed_domains[] = sanitize_text_field( $domain );
				}
			}
		}
		$this->update_setting( 'allowed_domains', $allowed_domains );

		// Process protected file types.
		$protected_types = array();
		if ( isset( $_POST['wpshadow_hotlink_protected_types'] ) && is_array( $_POST['wpshadow_hotlink_protected_types'] ) ) {
			foreach ( $_POST['wpshadow_hotlink_protected_types'] as $type ) {
				$protected_types[] = sanitize_text_field( wp_unslash( $type ) );
			}
		}
		if ( empty( $protected_types ) ) {
			$protected_types = array( 'jpg', 'jpeg', 'png', 'gif' );
		}
		$this->update_setting( 'protected_types', $protected_types );

		// Clear configuration transient to force reconfiguration.
		delete_transient( 'wpshadow_hotlink_protection_last_config' );

		// Reconfigure protection.
		$this->configure_hotlink_protection();

		// Add success notice.
		add_action(
			'admin_notices',
			function (): void {
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Hotlink protection settings saved successfully.', 'plugin-wpshadow' ); ?></p>
				</div>
				<?php
			}
		);
	}

	/**
	 * Add an admin notice.
	 *
	 * @param string $id      Unique notice ID.
	 * @param string $message Notice message.
	 * @param string $type    Notice type (error, warning, info, success).
	 * @return void
	 */
	private function add_admin_notice( string $id, string $message, string $type = 'info' ): void {
		add_action(
			'admin_notices',
			function () use ( $message, $type ): void {
				printf(
					'<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
					esc_attr( $type ),
					wp_kses_post( $message )
				);
			}
		);
	}
}
