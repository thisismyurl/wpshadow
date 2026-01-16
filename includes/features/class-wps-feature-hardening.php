<?php
/**
 * Feature: One-Click Security Hardening
 *
 * Provides comprehensive security hardening features including:
 * - XML-RPC disabling
 * - wp-json selective lockdown
 * - Directory listing protection
 * - Secure salts validation
 * - File permissions check
 * - HTTPS enforcement (Enforce HTTPS Everywhere)
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_Hardening
 *
 * One-click security hardening implementation.
 */
final class WPSHADOW_Feature_Hardening extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'security-hardening',
				'name'               => __( 'One-Click Security Hardening', 'plugin-wpshadow' ),
				'description'        => __( 'Applies common hardening steps in one toggle: disable XML-RPC if not needed, restrict wp-json exposure, prevent directory listing, validate security salts, check file permissions, and enforce HTTPS everywhere. Reduces attack surface, limits information leakage, and aligns with best practices while keeping necessary features available when explicitly required by themes, plugins, or integrations.', 'plugin-wpshadow' ),
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
		if ( ! $this->is_enabled() ) {
			return;
		}

		// XML-RPC disabling.
		add_filter( 'xmlrpc_enabled', '__return_false' );

		// Selective wp-json lockdown.
		add_filter( 'rest_authentication_errors', array( $this, 'restrict_rest_api_access' ) );

		// Initialize on admin_init for checks that need admin context.
		add_action( 'admin_init', array( $this, 'perform_security_checks' ) );

		// Apply directory listing protection.
		add_action( 'admin_init', array( $this, 'protect_directory_listing' ), 5 );

		// HTTPS enforcement.
		$this->enforce_https();
	}

	/**
	 * Restrict REST API access to authenticated users.
	 * Allows public access to specific endpoints that are commonly needed.
	 *
	 * @param \WP_Error|mixed $result Error from previous authentication handler, null if no error.
	 * @return \WP_Error|mixed WP_Error if authentication error, otherwise passed through.
	 */
	public function restrict_rest_api_access( $result ) {
		// If already an error, pass it through.
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		// Allow authenticated users.
		if ( is_user_logged_in() ) {
			return $result;
		}

		// Get the current route.
		$route = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		// Allow specific public endpoints.
		$public_routes = array(
			'/wp-json/$',                           // Root endpoint for discovery.
			'/wp-json/oembed/',                     // oEmbed endpoints.
			'/wp-json/wp/v2/posts',                 // Public posts (read-only via GET).
			'/wp-json/wp/v2/pages',                 // Public pages (read-only via GET).
			'/wp-json/wp/v2/categories',            // Public taxonomies.
			'/wp-json/wp/v2/tags',                  // Public taxonomies.
		);

		// Check if current route matches public routes.
		foreach ( $public_routes as $public_route ) {
			if ( preg_match( '#' . $public_route . '#', $route ) ) {
				// Allow GET requests to these endpoints.
				if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'GET' === $_SERVER['REQUEST_METHOD'] ) {
					return $result;
				}
			}
		}

		// Block all other unauthenticated REST API access.
		return new \WP_Error(
			'rest_not_logged_in',
			__( 'You are not currently logged in.', 'plugin-wpshadow' ),
			array( 'status' => 401 )
		);
	}

	/**
	 * Perform security checks: salts validation and file write permissions.
	 *
	 * @return void
	 */
	public function perform_security_checks(): void {
		// Only run checks once per day to avoid performance impact.
		$last_check = get_transient( 'wpshadow_security_hardening_last_check' );
		if ( false !== $last_check ) {
			return;
		}

		// Set transient for 24 hours.
		set_transient( 'wpshadow_security_hardening_last_check', time(), DAY_IN_SECONDS );

		// Run checks.
		$this->check_security_salts();
		$this->check_file_permissions();
	}

	/**
	 * Check if WordPress security salts are properly configured.
	 *
	 * @return void
	 */
	private function check_security_salts(): void {
		$salts_to_check = array(
			'AUTH_KEY',
			'SECURE_AUTH_KEY',
			'LOGGED_IN_KEY',
			'NONCE_KEY',
			'AUTH_SALT',
			'SECURE_AUTH_SALT',
			'LOGGED_IN_SALT',
			'NONCE_SALT',
		);

		$weak_salts = array();

		foreach ( $salts_to_check as $salt ) {
			if ( ! defined( $salt ) ) {
				$weak_salts[] = $salt;
				continue;
			}

			$value = constant( $salt );

			// Check if salt is the default placeholder or too short.
			if ( empty( $value ) ||
				'put your unique phrase here' === $value ||
				strlen( $value ) < 64 ) {
				$weak_salts[] = $salt;
			}
		}

		if ( ! empty( $weak_salts ) ) {
			$this->add_security_notice(
				'weak_salts',
				sprintf(
					/* translators: %s: comma-separated list of weak salt constants */
					__( 'Security Warning: The following security keys/salts are weak or undefined: %s. Visit https://api.wordpress.org/secret-key/1.1/salt/ to generate new keys.', 'plugin-wpshadow' ),
					implode( ', ', $weak_salts )
				),
				'error'
			);
		}
	}

	/**
	 * Check critical file and directory permissions.
	 *
	 * @return void
	 */
	private function check_file_permissions(): void {
		$wp_config_path = ABSPATH . 'wp-config.php';
		if ( ! file_exists( $wp_config_path ) ) {
			// Check one directory up for wp-config.php.
			$wp_config_path = dirname( ABSPATH ) . '/wp-config.php';
		}

		$checks = array();

		// Check wp-config.php permissions.
		if ( file_exists( $wp_config_path ) ) {
			$perms = fileperms( $wp_config_path );
			// Check if wp-config.php is world-readable (other can read).
			if ( $perms & 0x0004 ) {
				$checks[] = __( 'wp-config.php is world-readable. Recommended permissions: 0600 or 0400.', 'plugin-wpshadow' );
			}
		}

		// Check if .htaccess is writable (if it exists).
		$htaccess_path = ABSPATH . '.htaccess';
		if ( file_exists( $htaccess_path ) && is_writable( $htaccess_path ) ) {
			// This is actually normal for WordPress to function, but note if world-writable.
			$perms = fileperms( $htaccess_path );
			if ( $perms & 0x0002 ) { // World-writable.
				$checks[] = __( '.htaccess is world-writable. Consider restricting permissions to 0644.', 'plugin-wpshadow' );
			}
		}

		// Check wp-content permissions.
		$wp_content_path = WP_CONTENT_DIR;
		if ( file_exists( $wp_content_path ) ) {
			$perms = fileperms( $wp_content_path );
			if ( $perms & 0x0002 ) { // World-writable.
				$checks[] = __( 'wp-content directory is world-writable. Consider restricting permissions to 0755.', 'plugin-wpshadow' );
			}
		}

		if ( ! empty( $checks ) ) {
			$this->add_security_notice(
				'file_permissions',
				implode( ' ', $checks ),
				'warning'
			);
		}
	}

	/**
	 * Protect against directory listing.
	 *
	 * @return void
	 */
	public function protect_directory_listing(): void {
		// Protect wp-content and common subdirectories.
		$directories = array(
			WP_CONTENT_DIR,
			WP_CONTENT_DIR . '/uploads',
			WP_CONTENT_DIR . '/plugins',
			WP_CONTENT_DIR . '/themes',
		);

		foreach ( $directories as $directory ) {
			if ( ! file_exists( $directory ) ) {
				continue;
			}

			// Add .htaccess to prevent directory listing.
			$this->add_directory_protection( $directory );

			// Add index.php for additional protection.
			$this->add_index_file( $directory );
		}
	}

	/**
	 * Add .htaccess file to prevent directory listing.
	 *
	 * @param string $directory Directory path.
	 * @return void
	 */
	private function add_directory_protection( string $directory ): void {
		$htaccess_file = trailingslashit( $directory ) . '.htaccess';

		// Don't overwrite if already exists with our content.
		if ( file_exists( $htaccess_file ) ) {
			$existing_content = file_get_contents( $htaccess_file );
			if ( false !== $existing_content && strpos( $existing_content, 'Options -Indexes' ) !== false ) {
				return;
			}
		}

		$htaccess_content  = "# WPS Security Hardening\n";
		$htaccess_content .= "Options -Indexes\n";
		$htaccess_content .= "<IfModule mod_autoindex.c>\n";
		$htaccess_content .= "    IndexIgnore *\n";
		$htaccess_content .= "</IfModule>\n";

		// If file exists, append to it; otherwise create new.
		if ( file_exists( $htaccess_file ) ) {
			$existing_content = file_get_contents( $htaccess_file );
			if ( false !== $existing_content ) {
				$htaccess_content = $existing_content . "\n" . $htaccess_content;
			}
		}

		// Attempt to write the file.
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		$result = @file_put_contents( $htaccess_file, $htaccess_content );

		if ( false === $result ) {

		}
	}

	/**
	 * Add index.php file to directory for additional protection.
	 *
	 * @param string $directory Directory path.
	 * @return void
	 */
	private function add_index_file( string $directory ): void {
		$index_file = trailingslashit( $directory ) . 'index.php';

		// Don't overwrite existing index.php.
		if ( file_exists( $index_file ) ) {
			return;
		}

		$index_content = "<?php\n// Silence is golden.\n";

		// Attempt to write the file.
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents
		$result = @file_put_contents( $index_file, $index_content );

		if ( false === $result ) {

		}
	}

	/**
	 * Add a security notice to be displayed in admin.
	 *
	 * @param string $id      Unique notice ID.
	 * @param string $message Notice message.
	 * @param string $type    Notice type (error, warning, info).
	 * @return void
	 */
	private function add_security_notice( string $id, string $message, string $type = 'warning' ): void {
		$notices = $this->get_setting( 'wpshadow_security_hardening_notices', array( ) );

		$notices[ $id ] = array(
			'message' => $message,
			'type'    => $type,
			'time'    => time(),
		);

		$this->update_setting( 'wpshadow_security_hardening_notices', $notices  );

		// Also add as WordPress admin notice.
		add_action(
			'admin_notices',
			function () use ( $message, $type ) {
				printf(
					'<div class="notice notice-%s is-dismissible"><p><strong>WPS Security:</strong> %s</p></div>',
					esc_attr( $type ),
					wp_kses_post( $message )
				);
			}
		);
	}

	/**
	 * Enforce HTTPS everywhere on the site.
	 * Redirects HTTP requests to HTTPS and forces SSL for admin area.
	 *
	 * @return void
	 */
	private function enforce_https(): void {
		// Check if site is already using HTTPS.
		$site_url = get_option( 'siteurl' );
		$home_url = get_option( 'home' );
		
		if ( ! is_string( $site_url ) || ! is_string( $home_url ) ) {
			return;
		}

		$is_https = strpos( $site_url, 'https://' ) === 0 && strpos( $home_url, 'https://' ) === 0;

		// Force SSL for admin and logins using WordPress hooks.
		// Note: FORCE_SSL_ADMIN constant should be defined in wp-config.php for best results.
		add_filter( 'force_ssl_admin', '__return_true' );

		// Redirect HTTP to HTTPS for all requests.
		add_action( 'template_redirect', array( $this, 'redirect_to_https' ), 1 );
		add_action( 'admin_init', array( $this, 'redirect_admin_to_https' ), 1 );

		// Add HTTPS check to admin notices if not yet using HTTPS.
		if ( ! $is_https ) {
			add_action( 'admin_notices', array( $this, 'https_warning_notice' ) );
		}

		// Filter home and site URL to use HTTPS.
		add_filter( 'home_url', array( $this, 'force_https_url' ), 10, 1 );
		add_filter( 'site_url', array( $this, 'force_https_url' ), 10, 1 );
		add_filter( 'admin_url', array( $this, 'force_https_url' ), 10, 1 );
		add_filter( 'wp_redirect', array( $this, 'force_https_url' ), 10, 1 );
		add_filter( 'content_url', array( $this, 'force_https_url' ), 10, 1 );
		add_filter( 'plugins_url', array( $this, 'force_https_url' ), 10, 1 );
	}

	/**
	 * Redirect HTTP requests to HTTPS on the frontend.
	 *
	 * @return void
	 */
	public function redirect_to_https(): void {
		if ( ! is_ssl() && ! is_admin() ) {
			$this->perform_https_redirect();
		}
	}

	/**
	 * Redirect HTTP requests to HTTPS in the admin area.
	 *
	 * @return void
	 */
	public function redirect_admin_to_https(): void {
		if ( ! is_ssl() && is_admin() ) {
			$this->perform_https_redirect();
		}
	}

	/**
	 * Perform the actual HTTPS redirect with proper security measures.
	 *
	 * @return void
	 */
	private function perform_https_redirect(): void {
		// Get current URL components.
		$http_host = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		
		if ( empty( $http_host ) ) {
			return;
		}
		
		// Get and sanitize REQUEST_URI immediately.
		// Use esc_url_raw for sanitization while preserving URL structure.
		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
		
		// Build HTTPS URL.
		$redirect_url = 'https://' . $http_host . $request_uri;
		
		// Validate the URL before redirecting.
		if ( wp_http_validate_url( $redirect_url ) ) {
			wp_safe_redirect( $redirect_url, 301 );
			exit;
		}
	}

	/**
	 * Display admin notice warning about HTTP usage.
	 *
	 * @return void
	 */
	public function https_warning_notice(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		printf(
			'<div class="notice notice-warning is-dismissible"><p><strong>%s</strong> %s <a href="https://wordpress.org/support/article/https-for-wordpress/" target="_blank">%s</a></p></div>',
			esc_html__( 'WPS Security Warning:', 'plugin-wpshadow' ),
			esc_html__( 'Your site is not fully configured for HTTPS. Modern browsers may flag non-HTTPS sites as "Not Secure". Update your WordPress Address (URL) and Site Address (URL) to use HTTPS in Settings → General.', 'plugin-wpshadow' ),
			esc_html__( 'Learn more about HTTPS', 'plugin-wpshadow' )
		);
	}

	/**
	 * Force URLs to use HTTPS scheme.
	 *
	 * @param string $url The URL to filter.
	 * @return string The URL with HTTPS scheme.
	 */
	public function force_https_url( string $url ): string {
		if ( empty( $url ) ) {
			return $url;
		}

		// Convert http:// to https://.
		if ( strpos( $url, 'http://' ) === 0 ) {
			$url = 'https://' . substr( $url, 7 );
		}

		return $url;
	}
}
