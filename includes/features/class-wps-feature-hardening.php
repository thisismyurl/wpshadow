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
 * - HTTP Strict Transport Security (HSTS) header
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
				'description'        => __( 'Applies common hardening steps in one toggle: disable XML-RPC if not needed, restrict wp-json exposure, prevent directory listing, validate security salts, check file permissions, and enable HTTP Strict Transport Security (HSTS) to force HTTPS connections. Reduces attack surface, limits information leakage, prevents downgrade attacks, and aligns with best practices while keeping necessary features available when explicitly required by themes, plugins, or integrations.', 'plugin-wpshadow' ),
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

		// Add HSTS (HTTP Strict Transport Security) header.
		add_filter( 'wp_headers', array( $this, 'add_hsts_header' ) );
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
	 * Add HTTP Strict Transport Security (HSTS) header.
	 *
	 * Forces browsers to always use HTTPS for the domain, preventing downgrade attacks.
	 * Only applied when the site is accessed via HTTPS.
	 *
	 * @param array $headers The array of HTTP headers to be sent.
	 * @return array Modified headers array with HSTS header.
	 */
	public function add_hsts_header( array $headers ): array {
		// Only add HSTS header if site is accessed via HTTPS.
		if ( ! is_ssl() ) {
			return $headers;
		}

		// HSTS header with:
		// - max-age: 31536000 seconds (1 year)
		// - includeSubDomains: Apply to all subdomains
		// - preload: Allow inclusion in browser preload lists
		$headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains; preload';

		return $headers;
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
}
