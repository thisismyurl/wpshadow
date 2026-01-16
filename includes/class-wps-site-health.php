<?php
/**
 * Site Health integration for WPS Suite.
 *
 * @package wpshadow_SUPPORT
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-wps-health-renderer.php';

/**
 * Site Health integration class.
 */
class WPSHADOW_Site_Health {

	/**
	 * Registry of module-specific health checks.
	 *
	 * @var array
	 */
	private static $module_checks = array();

	/**
	 * Initialize Site Health integration.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_filter( 'site_status_tests', array( __CLASS__, 'add_tests' ) );
		add_filter( 'debug_information', array( __CLASS__, 'add_debug_info' ) );

		// Hook into module state changes to refresh health checks.
		add_action( 'wpshadow_module_enabled', array( __CLASS__, 'on_module_state_change' ) );
		add_action( 'wpshadow_module_disabled', array( __CLASS__, 'on_module_state_change' ) );

		// Register built-in module checks once during init.
		add_action( 'plugins_loaded', array( __CLASS__, 'register_builtin_module_checks' ), 20 );
	}

	/**
	 * Register health checks for a specific module.
	 *
	 * @param string $module_slug Module slug (e.g., 'vault-wpshadow').
	 * @param array  $checks      Array of health check definitions.
	 * @return void
	 */
	public static function register_module_checks( string $module_slug, array $checks ): void {
		if ( empty( $module_slug ) || empty( $checks ) ) {
			return;
		}

		self::$module_checks[ $module_slug ] = $checks;
	}

	/**
	 * Handle module state changes to trigger health check updates.
	 *
	 * @param string $module_slug Module slug that changed state.
	 * @return void
	 */
	public static function on_module_state_change( string $module_slug ): void {
		// Clear any cached Site Health data when module state changes.
		delete_transient( 'health-check-site-status-result' );

		/**
		 * Action fired when a module's health checks should be refreshed.
		 *
		 * @param string $module_slug Module slug.
		 */
		do_action( 'wpshadow_health_checks_updated', $module_slug );
	}

	/**
	 * Register WPS health tests.
	 *
	 * @param array $tests Site Health tests array.
	 * @return array
	 */
	public static function add_tests( array $tests ): array {
		// Add core-level tests (always active).
		$core_checks = self::get_core_checks();
		foreach ( $core_checks as $check_id => $check_config ) {
			$tests['direct'][ $check_id ] = $check_config;
		}

		// Add module-specific tests only for enabled modules.
		foreach ( self::$module_checks as $module_slug => $module_check_list ) {
			// Check if module is enabled.
			if ( ! WPSHADOW_Module_Registry::is_enabled( $module_slug ) ) {
				continue;
			}

			// Add each check from this module.
			foreach ( $module_check_list as $check_id => $check_config ) {
				$tests['direct'][ $check_id ] = $check_config;
			}
		}

		return $tests;
	}

	/**
	 * Get core-level health checks (always active).
	 *
	 * @return array
	 */
	private static function get_core_checks(): array {
		return array(
			'wpshadow_openssl_extension'         => array(
				'label' => __( 'OpenSSL extension', 'plugin-wpshadow' ),
				'test'  => array( __CLASS__, 'test_openssl_extension' ),
			),
			'wpshadow_php_version'               => array(
				'label' => __( 'PHP version compliance', 'plugin-wpshadow' ),
				'test'  => array( __CLASS__, 'test_php_version' ),
			),
			'wpshadow_wordpress_version'         => array(
				'label' => __( 'WordPress version compliance', 'plugin-wpshadow' ),
				'test'  => array( __CLASS__, 'test_wordpress_version' ),
			),
			'wpshadow_module_status'             => array(
				'label' => __( 'Module status', 'plugin-wpshadow' ),
				'test'  => array( __CLASS__, 'test_module_status' ),
			),
			'wpshadow_https_enforcement'         => array(
				'label' => __( 'HTTPS enforcement', 'plugin-wpshadow' ),
				'test'  => array( __CLASS__, 'test_https_enforcement' ),
			),
			'wpshadow_environment_compatibility' => array(
				'label' => __( 'Environment compatibility', 'plugin-wpshadow' ),
				'test'  => array( __CLASS__, 'test_environment_compatibility' ),
			),
			'wpshadow_memory_limit'              => array(
				'label' => __( 'Memory limit status', 'plugin-wpshadow' ),
				'test'  => array( __CLASS__, 'test_memory_limit' ),
			),
			'wpshadow_execution_time'            => array(
				'label' => __( 'Execution time limit', 'plugin-wpshadow' ),
				'test'  => array( __CLASS__, 'test_execution_time' ),
			),
			'wpshadow_required_extensions'       => array(
				'label' => __( 'Required PHP extensions', 'plugin-wpshadow' ),
				'test'  => array( __CLASS__, 'test_required_extensions' ),
			),
			'wpshadow_resource_usage'            => array(
				'label' => __( 'Current resource usage', 'plugin-wpshadow' ),
				'test'  => array( __CLASS__, 'test_resource_usage' ),
			),
		);
	}

	/**
	 * Register built-in module health checks.
	 *
	 * @return void
	 */
	public static function register_builtin_module_checks(): void {
		// Vault module checks.
		self::register_module_checks(
			'vault-wpshadow',
			array(
				'wpshadow_vault_directory'   => array(
					'label' => __( 'Vault directory status', 'plugin-wpshadow' ),
					'test'  => array( __CLASS__, 'test_vault_directory' ),
				),
				'wpshadow_encryption_config' => array(
					'label' => __( 'Encryption configuration', 'plugin-wpshadow' ),
					'test'  => array( __CLASS__, 'test_encryption_config' ),
				),
				'wpshadow_vault_permissions' => array(
					'label' => __( 'Vault write permissions', 'plugin-wpshadow' ),
					'test'  => array( __CLASS__, 'test_vault_permissions' ),
				),
			)
		);

		/**
		 * Allow other modules to register their health checks.
		 */
		do_action( 'wpshadow_register_health_checks' );
	}

	/**
	 * Extract test method name from check configuration.
	 *
	 * @param array $check_config Check configuration array.
	 * @return string Test method name or empty string.
	 */
	private static function extract_test_method( array $check_config ): string {
		if ( ! isset( $check_config['test'] ) ) {
			return '';
		}

		$test = $check_config['test'];
		return is_array( $test ) && isset( $test[1] ) ? $test[1] : '';
	}

	/**
	 * Build a test map for get_health_check_results() with module attribution.
	 *
	 * Maps test IDs to their configuration including method names and module ownership.
	 *
	 * @return array Test map with module attribution.
	 */
	private static function build_test_map(): array {
		$map = array();

		// Add core checks with attribution (derive from get_core_checks).
		$core_checks = self::get_core_checks();
		foreach ( $core_checks as $check_id => $check_config ) {
			// Extract method name from test callback.
			$test_method = self::extract_test_method( $check_config );

			// Convert check_id to test_id (remove WPSHADOW_ prefix).
			$test_id = str_replace( 'wpshadow_', '', $check_id );

			$map[ $test_id ] = array(
				'label'  => $check_config['label'],
				'test'   => $test_method,
				'module' => 'core',
			);
		}

		// Add module-specific checks from registry.
		foreach ( self::$module_checks as $module_slug => $module_check_list ) {
			// Check if module is enabled.
			if ( ! WPSHADOW_Module_Registry::is_enabled( $module_slug ) ) {
				continue;
			}

			// Extract module name from slug (e.g., 'vault-wpshadow' -> 'vault').
			$module_name = str_replace( '-wpshadow', '', $module_slug );

			foreach ( $module_check_list as $check_id => $check_config ) {
				// Extract method name from test callback.
				$test_method = self::extract_test_method( $check_config );

				// Convert check_id to test_id (remove WPSHADOW_ prefix).
				$test_id = str_replace( 'wpshadow_', '', $check_id );

				$map[ $test_id ] = array(
					'label'  => $check_config['label'],
					'test'   => $test_method,
					'module' => $module_name,
				);
			}
		}

		return $map;
	}

	/**
	 * Test vault directory status.
	 *
	 * @return array
	 */
	public static function test_vault_directory(): array {
		$upload_dir    = wp_upload_dir();
		$vault_dirname = get_option( 'wpshadow_vault_dirname' );

		if ( empty( $vault_dirname ) ) {
			return WPSHADOW_Health_Renderer::build_result(
				__( 'Vault directory not configured', 'plugin-wpshadow' ),
				'recommended',
				esc_html__( 'The vault directory has not been created yet. It will be created automatically on first use.', 'plugin-wpshadow' ),
				'wpshadow_vault_directory',
				'',
				'orange'
			);
		}

		$vault_path = $upload_dir['basedir'] . '/' . $vault_dirname;

		if ( ! file_exists( $vault_path ) ) {
			return WPSHADOW_Health_Renderer::build_result(
				__( 'Vault directory missing', 'plugin-wpshadow' ),
				'critical',
				sprintf(
					/* translators: %s: vault path */
					esc_html__( 'The vault directory was configured but does not exist at: %s', 'plugin-wpshadow' ),
					'<code>' . esc_html( $vault_path ) . '</code>'
				),
				'wpshadow_vault_directory',
				'',
				'red'
			);
		}

		return WPSHADOW_Health_Renderer::build_result(
			__( 'Vault directory configured', 'plugin-wpshadow' ),
			'good',
			sprintf(
				/* translators: %s: vault path */
				esc_html__( 'The vault directory exists at: %s', 'plugin-wpshadow' ),
				'<code>' . esc_html( $vault_path ) . '</code>'
			),
			'wpshadow_vault_directory'
		);
	}

	/**
	 * Test encryption configuration.
	 *
	 * @return array
	 */
	public static function test_encryption_config(): array {
		$is_production = 'production' === wp_get_environment_type();

		if ( defined( 'wpshadow_VAULT_KEY' ) && WPSHADOW_VAULT_KEY ) {
			return WPSHADOW_Health_Renderer::build_result(
				__( 'Encryption key configured in wp-config.php', 'plugin-wpshadow' ),
				'good',
				esc_html__( 'Encryption key is properly defined in wp-config.php.', 'plugin-wpshadow' ),
				'wpshadow_encryption_config'
			);
		}

		$stored_key = get_option( 'wpshadow_vault_encryption_key' );

		if ( ! empty( $stored_key ) && $is_production ) {
			return WPSHADOW_Health_Renderer::build_result(
				__( 'Encryption key should be in wp-config.php', 'plugin-wpshadow' ),
				'critical',
				sprintf(
					'%s<br><br>%s',
					esc_html__( 'For production sites, encryption keys must be defined in wp-config.php, not stored in the database.', 'plugin-wpshadow' ),
					sprintf(
						/* translators: %s: example code */
						esc_html__( 'Add this line to your wp-config.php: %s', 'plugin-wpshadow' ),
						'<code>define( "wpshadow_VAULT_KEY", "' . esc_html( $stored_key ) . '" );</code>'
					)
				),
				'wpshadow_encryption_config',
				'',
				'red'
			);
		}

		if ( ! empty( $stored_key ) ) {
			return WPSHADOW_Health_Renderer::build_result(
				__( 'Encryption key in options (development mode)', 'plugin-wpshadow' ),
				'recommended',
				esc_html__( 'Encryption key is stored in the database. This is acceptable for development but should be moved to wp-config.php for production.', 'plugin-wpshadow' ),
				'wpshadow_encryption_config',
				'',
				'orange'
			);
		}

		return WPSHADOW_Health_Renderer::build_result(
			__( 'Encryption key not configured', 'plugin-wpshadow' ),
			'recommended',
			esc_html__( 'No encryption key is configured. An encryption key will be generated automatically when needed.', 'plugin-wpshadow' ),
			'wpshadow_encryption_config',
			'',
			'orange'
		);
	}

	/**
	 * Test OpenSSL extension.
	 *
	 * @return array
	 */
	public static function test_openssl_extension(): array {
		if ( extension_loaded( 'openssl' ) ) {
			return WPSHADOW_Health_Renderer::build_result(
				__( 'OpenSSL extension is available', 'plugin-wpshadow' ),
				'good',
				esc_html__( 'The OpenSSL PHP extension is loaded and encryption features are available.', 'plugin-wpshadow' ),
				'wpshadow_openssl_extension'
			);
		}

		return WPSHADOW_Health_Renderer::build_result(
			__( 'OpenSSL extension is not available', 'plugin-wpshadow' ),
			'critical',
			esc_html__( 'The OpenSSL PHP extension is required for encryption features. Contact your hosting provider to enable it.', 'plugin-wpshadow' ),
			'wpshadow_openssl_extension',
			'',
			'red'
		);
	}

	/**
	 * Test HTTPS enforcement.
	 *
	 * @return array
	 */
	public static function test_https_enforcement(): array {
		$site_url = get_option( 'siteurl' );
		$home_url = get_option( 'home' );
		
		$is_https = strpos( $site_url, 'https://' ) === 0 && strpos( $home_url, 'https://' ) === 0;
		$is_ssl   = is_ssl();
		
		// Check if hardening feature is enabled.
		$hardening_enabled = false;
		if ( class_exists( 'WPShadow\CoreSupport\WPSHADOW_Feature_Registry' ) ) {
			$feature = WPSHADOW_Feature_Registry::get_feature( 'security-hardening' );
			if ( $feature ) {
				$hardening_enabled = $feature->is_enabled();
			}
		}

		if ( $is_https && $is_ssl ) {
			$description = esc_html__( 'Your site is configured to use HTTPS. All traffic is encrypted and secure.', 'plugin-wpshadow' );
			if ( $hardening_enabled ) {
				$description .= ' ' . esc_html__( 'HTTPS enforcement is active via WPS Security Hardening.', 'plugin-wpshadow' );
			}
			
			return WPSHADOW_Health_Renderer::build_result(
				__( 'HTTPS is properly configured', 'plugin-wpshadow' ),
				'good',
				$description,
				'wpshadow_https_enforcement'
			);
		}

		if ( $is_https && ! $is_ssl ) {
			return WPSHADOW_Health_Renderer::build_result(
				__( 'HTTPS is configured but current request is not secure', 'plugin-wpshadow' ),
				'recommended',
				esc_html__( 'Your WordPress URLs are set to HTTPS, but the current page was loaded over HTTP. Enable HTTPS enforcement in WPS Security Hardening to redirect all HTTP traffic to HTTPS.', 'plugin-wpshadow' ),
				'wpshadow_https_enforcement',
				'',
				'orange'
			);
		}

		// Site is not using HTTPS at all.
		$description = esc_html__( 'Your site is not using HTTPS. Modern browsers may flag non-HTTPS sites as "Not Secure", which can reduce visitor trust and harm SEO rankings.', 'plugin-wpshadow' );
		$description .= ' ' . esc_html__( 'To fix this: 1) Obtain an SSL certificate from your hosting provider, 2) Update WordPress Address and Site Address URLs to use https:// in Settings → General, 3) Enable HTTPS enforcement in WPS Security Hardening.', 'plugin-wpshadow' );

		return WPSHADOW_Health_Renderer::build_result(
			__( 'HTTPS is not enabled', 'plugin-wpshadow' ),
			'critical',
			$description,
			'wpshadow_https_enforcement',
			'',
			'red'
		);
	}

	/**
	 * Test PHP version compliance.
	 *
	 * @return array
	 */
	public static function test_php_version(): array {
		if ( version_compare( PHP_VERSION, WPSHADOW_MIN_PHP, '>=' ) ) {
			return WPSHADOW_Health_Renderer::build_result(
				sprintf(
					/* translators: %s: PHP version */
					__( 'PHP version %s meets requirements', 'plugin-wpshadow' ),
					PHP_VERSION
				),
				'good',
				sprintf(
					/* translators: 1: current PHP version, 2: minimum required version */
					esc_html__( 'Your PHP version (%1$s) meets the minimum requirement of %2$s.', 'plugin-wpshadow' ),
					PHP_VERSION,
					WPSHADOW_MIN_PHP
				),
				'wpshadow_php_version'
			);
		}

		return WPSHADOW_Health_Renderer::build_result(
			__( 'PHP version below minimum requirement', 'plugin-wpshadow' ),
			'critical',
			sprintf(
				/* translators: 1: current PHP version, 2: minimum required version */
				esc_html__( 'Your PHP version (%1$s) is below the minimum requirement of %2$s. Contact your hosting provider to upgrade PHP.', 'plugin-wpshadow' ),
				PHP_VERSION,
				WPSHADOW_MIN_PHP
			),
			'wpshadow_php_version',
			'',
			'red'
		);
	}

	/**
	 * Test WordPress version compliance.
	 *
	 * @return array
	 */
	public static function test_wordpress_version(): array {
		global $wp_version;

		if ( version_compare( $wp_version, WPSHADOW_MIN_WP, '>=' ) ) {
			return WPSHADOW_Health_Renderer::build_result(
				sprintf(
					/* translators: %s: WordPress version */
					__( 'WordPress version %s meets requirements', 'plugin-wpshadow' ),
					$wp_version
				),
				'good',
				sprintf(
					/* translators: 1: current WordPress version, 2: minimum required version */
					esc_html__( 'Your WordPress version (%1$s) meets the minimum requirement of %2$s.', 'plugin-wpshadow' ),
					$wp_version,
					WPSHADOW_MIN_WP
				),
				'wpshadow_wordpress_version'
			);
		}

		return WPSHADOW_Health_Renderer::build_result(
			__( 'WordPress version below minimum requirement', 'plugin-wpshadow' ),
			'critical',
			sprintf(
				/* translators: 1: current WordPress version, 2: minimum required version */
				esc_html__( 'Your WordPress version (%1$s) is below the minimum requirement of %2$s. Please update WordPress.', 'plugin-wpshadow' ),
				$wp_version,
				WPSHADOW_MIN_WP
			),
			'wpshadow_wordpress_version',
			'',
			'red'
		);
	}

	/**
	 * Test vault write permissions.
	 *
	 * @return array
	 */
	public static function test_vault_permissions(): array {
		$upload_dir    = wp_upload_dir();
		$vault_dirname = get_option( 'wpshadow_vault_dirname' );

		if ( empty( $vault_dirname ) ) {
			return WPSHADOW_Health_Renderer::build_result(
				__( 'Vault not configured yet', 'plugin-wpshadow' ),
				'recommended',
				esc_html__( 'Vault directory will be created with appropriate permissions when first needed.', 'plugin-wpshadow' ),
				'wpshadow_vault_permissions',
				'',
				'gray'
			);
		}

		$vault_path = $upload_dir['basedir'] . '/' . $vault_dirname;

		if ( ! file_exists( $vault_path ) ) {
			return WPSHADOW_Health_Renderer::build_result(
				__( 'Vault directory does not exist', 'plugin-wpshadow' ),
				'critical',
				esc_html__( 'The vault directory was expected but does not exist. It will be recreated on next use.', 'plugin-wpshadow' ),
				'wpshadow_vault_permissions',
				'',
				'red'
			);
		}

		if ( ! wp_is_writable( $vault_path ) ) {
			return WPSHADOW_Health_Renderer::build_result(
				__( 'Vault directory is not writable', 'plugin-wpshadow' ),
				'critical',
				sprintf(
					/* translators: %s: vault path */
					esc_html__( 'The vault directory exists but is not writable: %s. Check directory permissions.', 'plugin-wpshadow' ),
					'<code>' . esc_html( $vault_path ) . '</code>'
				),
				'wpshadow_vault_permissions',
				'',
				'red'
			);
		}

		return WPSHADOW_Health_Renderer::build_result(
			__( 'Vault directory has correct permissions', 'plugin-wpshadow' ),
			'good',
			esc_html__( 'The vault directory is writable and ready for use.', 'plugin-wpshadow' ),
			'wpshadow_vault_permissions'
		);
	}

	/**
	 * Test module status.
	 *
	 * @return array
	 */
	public static function test_module_status(): array {
		$modules      = WPSHADOW_Module_Registry::get_catalog_with_status();
		$active_count = 0;
		$hub_count    = 0;
		$spoke_count  = 0;

		foreach ( $modules as $module ) {
			// Only count enabled modules.
			if ( empty( $module['enabled'] ) ) {
				continue;
			}
			++$active_count;
			if ( 'hub' === ( $module['type'] ?? '' ) ) {
				++$hub_count;
			} elseif ( 'spoke' === ( $module['type'] ?? '' ) ) {
				++$spoke_count;
			}
		}

		$module_count = $active_count;

		if ( 0 === $module_count ) {
			return WPSHADOW_Health_Renderer::build_result(
				__( 'No modules registered', 'plugin-wpshadow' ),
				'recommended',
				esc_html__( 'No WPS Suite modules have been registered yet. This is normal if no additional modules are installed.', 'plugin-wpshadow' ),
				'wpshadow_module_status',
				'',
				'gray'
			);
		}

		return WPSHADOW_Health_Renderer::build_result(
			sprintf(
				/* translators: 1: number of active modules, 2: total modules */
				__( '%1$d of %2$d modules active', 'plugin-wpshadow' ),
				$active_count,
				$module_count
			),
			'good',
			sprintf(
				/* translators: 1: hub count, 2: spoke count */
				esc_html__( 'WPS Suite has %1$d hubs and %2$d spokes registered.', 'plugin-wpshadow' ),
				$hub_count,
				$spoke_count
			),
			'wpshadow_module_status'
		);
	}

	/**
	 * Add debug information.
	 *
	 * @param array $info Debug information array.
	 * @return array
	 */
	public static function add_debug_info( array $info ): array {
		$upload_dir    = wp_upload_dir();
		$vault_dirname = get_option( 'wpshadow_vault_dirname', __( 'Not configured', 'plugin-wpshadow' ) );
		$vault_path    = ! empty( $vault_dirname ) && 'Not configured' !== $vault_dirname
			? $upload_dir['basedir'] . '/' . $vault_dirname
			: __( 'Not configured', 'plugin-wpshadow' );

		$modules     = WPSHADOW_Module_Registry::get_catalog_with_status();
		$module_list = array();
		foreach ( $modules as $slug => $module ) {
			// Only show enabled modules in the site report.
			if ( empty( $module['enabled'] ) ) {
				continue;
			}
			$module_list[] = sprintf( '%s v%s', $module['name'] ?? $slug, $module['version'] ?? '?' );
		}

		$encryption_key_source = defined( 'wpshadow_VAULT_KEY' ) && WPSHADOW_VAULT_KEY
			? __( 'wp-config.php', 'plugin-wpshadow' )
			: ( get_option( 'wpshadow_vault_encryption_key' ) ? __( 'Options table', 'plugin-wpshadow' ) : __( 'Not configured', 'plugin-wpshadow' ) );

		$info['wps-suite'] = array(
			'label'  => __( 'WPS Suite', 'plugin-wpshadow' ),
			'fields' => array(
				'core_version'          => array(
					'label' => __( 'Core version', 'plugin-wpshadow' ),
					'value' => WPSHADOW_VERSION,
				),
				'suite_id'              => array(
					'label' => __( 'Suite ID', 'plugin-wpshadow' ),
					'value' => WPSHADOW_SUITE_ID,
				),
				'text_domain'           => array(
					'label' => __( 'Text domain', 'plugin-wpshadow' ),
					'value' => WPSHADOW_TEXT_DOMAIN,
				),
				'vault_dirname'         => array(
					'label' => __( 'Vault directory name', 'plugin-wpshadow' ),
					'value' => $vault_dirname,
				),
				'vault_path'            => array(
					'label' => __( 'Vault path', 'plugin-wpshadow' ),
					'value' => $vault_path,
				),
				'encryption_key_source' => array(
					'label' => __( 'Encryption key source', 'plugin-wpshadow' ),
					'value' => $encryption_key_source,
				),
				'openssl_loaded'        => array(
					'label' => __( 'OpenSSL extension', 'plugin-wpshadow' ),
					'value' => extension_loaded( 'openssl' ) ? __( 'Loaded', 'plugin-wpshadow' ) : __( 'Not loaded', 'plugin-wpshadow' ),
				),
				'registered_modules'    => array(
					'label' => __( 'Registered modules', 'plugin-wpshadow' ),
					'value' => ! empty( $module_list ) ? implode( "\n", $module_list ) : __( 'None', 'plugin-wpshadow' ),
				),
			),
		);

		// Add environment information.
		if ( class_exists( '\\WPShadow\\WPSHADOW_Environment_Checker' ) ) {
			$env_status = \WPShadow\WPSHADOW_Environment_Checker::get_environment_status();

			$info['wps-environment'] = array(
				'label'  => __( 'WPS Environment', 'plugin-wpshadow' ),
				'fields' => array(
					'compatibility_status'   => array(
						'label' => __( 'Environment compatibility', 'plugin-wpshadow' ),
						'value' => $env_status['is_compatible'] ? __( 'Compatible', 'plugin-wpshadow' ) : __( 'Incompatible', 'plugin-wpshadow' ),
					),
					'resource_constraints'   => array(
						'label' => __( 'Resource constraints', 'plugin-wpshadow' ),
						'value' => $env_status['has_constraints'] ? __( 'Yes', 'plugin-wpshadow' ) : __( 'No', 'plugin-wpshadow' ),
					),
					'memory_limit'           => array(
						'label' => __( 'Memory limit', 'plugin-wpshadow' ),
						'value' => $env_status['memory_limit']['current'] . ' (' . $env_status['memory_limit']['level'] . ')',
					),
					'execution_time'         => array(
						'label' => __( 'Max execution time', 'plugin-wpshadow' ),
						'value' => 0 === $env_status['execution_time']['current']
							? __( 'Unlimited', 'plugin-wpshadow' )
							: $env_status['execution_time']['current'] . 's (' . $env_status['execution_time']['level'] . ')',
					),
					'upload_max_filesize'    => array(
						'label' => __( 'Upload max filesize', 'plugin-wpshadow' ),
						'value' => $env_status['upload_limit']['upload_max_filesize'],
					),
					'post_max_size'          => array(
						'label' => __( 'Post max size', 'plugin-wpshadow' ),
						'value' => $env_status['upload_limit']['post_max_size'],
					),
					'required_extensions'    => array(
						'label' => __( 'Required extensions', 'plugin-wpshadow' ),
						'value' => $env_status['extensions']['all_required_loaded']
							? __( 'All loaded', 'plugin-wpshadow' )
							: __( 'Missing: ', 'plugin-wpshadow' ) . implode( ', ', $env_status['extensions']['required_missing'] ),
					),
					'recommended_extensions' => array(
						'label' => __( 'Recommended extensions', 'plugin-wpshadow' ),
						'value' => empty( $env_status['extensions']['recommended_missing'] )
							? __( 'All loaded', 'plugin-wpshadow' )
							: __( 'Missing: ', 'plugin-wpshadow' ) . implode( ', ', $env_status['extensions']['recommended_missing'] ),
					),
					'diagnostic_logging'     => array(
						'label' => __( 'Diagnostic logging', 'plugin-wpshadow' ),
						'value' => get_option( 'wpshadow_diagnostic_logging_enabled', false ) ? __( 'Enabled', 'plugin-wpshadow' ) : __( 'Disabled', 'plugin-wpshadow' ),
					),
					'heavy_tasks_disabled'   => array(
						'label' => __( 'Heavy tasks disabled', 'plugin-wpshadow' ),
						'value' => \WPShadow\WPSHADOW_Environment_Checker::should_disable_heavy_tasks() ? __( 'Yes', 'plugin-wpshadow' ) : __( 'No', 'plugin-wpshadow' ),
					),
					'batching_enabled'       => array(
						'label' => __( 'Task batching', 'plugin-wpshadow' ),
						'value' => \WPShadow\WPSHADOW_Environment_Checker::should_batch_tasks() ? __( 'Enabled', 'plugin-wpshadow' ) : __( 'Not required', 'plugin-wpshadow' ),
					),
				),
			);
		}

		// Add resource usage information.
		if ( class_exists( '\\WPShadow\\WPSHADOW_Server_Limits' ) ) {
			$resource_status = WPSHADOW_Server_Limits::get_resource_status();

			$info['wps-resources'] = array(
				'label'  => __( 'WPS Resource Usage', 'plugin-wpshadow' ),
				'fields' => array(
					'memory_usage'      => array(
						'label' => __( 'Current memory usage', 'plugin-wpshadow' ),
						'value' => \WPShadow\WPSHADOW_Environment_Checker::format_bytes( $resource_status['memory']['current_usage'] ) . ' / ' . $resource_status['memory']['limit'],
					),
					'memory_percentage' => array(
						'label' => __( 'Memory usage percentage', 'plugin-wpshadow' ),
						'value' => number_format( $resource_status['memory']['usage_percentage'], 2 ) . '%',
					),
					'peak_memory'       => array(
						'label' => __( 'Peak memory usage', 'plugin-wpshadow' ),
						'value' => \WPShadow\WPSHADOW_Environment_Checker::format_bytes( $resource_status['memory']['peak_usage'] ),
					),
					'time_elapsed'      => array(
						'label' => __( 'Time elapsed', 'plugin-wpshadow' ),
						'value' => number_format( $resource_status['time']['elapsed'], 2 ) . 's',
					),
					'time_percentage'   => array(
						'label' => __( 'Time usage percentage', 'plugin-wpshadow' ),
						'value' => number_format( $resource_status['time']['usage_percentage'], 2 ) . '%',
					),
					'resource_level'    => array(
						'label' => __( 'Overall resource level', 'plugin-wpshadow' ),
						'value' => ucfirst( $resource_status['level'] ),
					),
					'batch_size'        => array(
						'label' => __( 'Recommended batch size', 'plugin-wpshadow' ),
						'value' => (string) \WPShadow\WPSHADOW_Server_Limits::get_batch_size(),
					),
				),
			);
		}

		return $info;
	}

	/**
	 * Get health test results for a module and its dependents.
	 *
	 * @param string|null $module_filter Optional module filter (for hub-specific health).
	 * @return array {
	 *     @type int    'score'    Overall health score percentage
	 *     @type string 'status'   Status: 'good', 'recommended', or 'critical'
	 *     @type array  'results'  Array of test results with module info
	 *     @type array  'dependents' Hierarchical dependent health data
	 * }
	 */
	public static function get_health_check_results( ?string $module_filter = null ): array {
		// Build test map from registry.
		$test_map = self::build_test_map();

		$results        = array();
		$critical_count = 0;
		$warning_count  = 0;
		$good_count     = 0;

		foreach ( $test_map as $test_id => $test_data ) {
			// Filter by module if specified.
			if ( $module_filter && isset( $test_data['module'] ) && $test_data['module'] !== $module_filter ) {
				continue;
			}

			if ( method_exists( __CLASS__, $test_data['test'] ) ) {
				$result = call_user_func( array( __CLASS__, $test_data['test'] ) );
				$status = $result['status'] ?? 'good';

				$results[ $test_id ] = array(
					'label'   => $test_data['label'],
					'status'  => $status,
					'module'  => $test_data['module'] ?? 'core',
					'details' => $result['description'] ?? '',
				);

				if ( 'critical' === $status ) {
					++$critical_count;
				} elseif ( 'recommended' === $status ) {
					++$warning_count;
				} else {
					++$good_count;
				}
			}
		}

		$total = count( $results );
		$score = $total > 0 ? round( ( $good_count / $total ) * 100 ) : 100;

		// Determine overall status.
		if ( $critical_count > 0 ) {
			$status = 'critical';
		} elseif ( $warning_count > 0 ) {
			$status = 'recommended';
		} else {
			$status = 'good';
		}

		return array(
			'score'   => $score,
			'status'  => $status,
			'results' => $results,
			'counts'  => array(
				'good'     => $good_count,
				'warning'  => $warning_count,
				'critical' => $critical_count,
				'total'    => $total,
			),
		);
	}

	/**
	 * Get health results with dependent modules.
	 *
	 * @param string $module_id The module ID to get health for.
	 * @return array Health data including dependents.
	 */
	public static function get_hierarchical_health( string $module_id ): array {
		$self_health = self::get_health_check_results( $module_id );

		// Get dependent modules from registry.
		$catalog         = \WPShadow\WPSHADOW_Module_Registry::get_catalog_with_status();
		$dependents_data = array();

		foreach ( $catalog as $mod ) {
			$mod_slug = $mod['slug'] ?? '';
			if ( empty( $mod['dependencies'] ) ) {
				continue;
			}

			// Check if current module is a dependency.
			if ( in_array( $module_id, (array) $mod['dependencies'], true ) ) {
				$dep_health                   = self::get_health_check_results( $mod_slug );
				$dependents_data[ $mod_slug ] = array(
					'name'   => $mod['name'] ?? $mod_slug,
					'health' => $dep_health,
				);
			}
		}

		return array(
			'self'       => $self_health,
			'dependents' => $dependents_data,
		);
	}

	/**
	 * Get health checks grouped by module type (core, hub, spoke).
	 *
	 * Provides a hierarchical view of health checks organized by module ownership.
	 *
	 * @return array {
	 *     @type array 'core'   Core-level health checks
	 *     @type array 'hubs'   Hub-level health checks grouped by module
	 *     @type array 'spokes' Spoke-level health checks grouped by module
	 * }
	 */
	public static function get_health_by_module_type(): array {
		$modules     = WPSHADOW_Module_Registry::get_catalog_with_status();
		$test_map    = self::build_test_map();
		$core_tests  = array();
		$hub_tests   = array();
		$spoke_tests = array();

		foreach ( $test_map as $test_id => $test_data ) {
			$module = $test_data['module'] ?? 'core';

			// Run the test.
			if ( method_exists( __CLASS__, $test_data['test'] ) ) {
				$result = call_user_func( array( __CLASS__, $test_data['test'] ) );
				$status = $result['status'] ?? 'good';

				$test_result = array(
					'label'   => $test_data['label'],
					'status'  => $status,
					'module'  => $module,
					'details' => $result['description'] ?? '',
				);

				// Categorize by type.
				if ( 'core' === $module ) {
					$core_tests[ $test_id ] = $test_result;
				} else {
					// Determine if hub or spoke based on module slug.
					$module_slug = $module . '-wpshadow';
					$module_info = $modules[ $module_slug ] ?? array();
					$module_type = $module_info['type'] ?? 'spoke';

					if ( 'hub' === $module_type ) {
						if ( ! isset( $hub_tests[ $module ] ) ) {
							$hub_tests[ $module ] = array();
						}
						$hub_tests[ $module ][ $test_id ] = $test_result;
					} else {
						if ( ! isset( $spoke_tests[ $module ] ) ) {
							$spoke_tests[ $module ] = array();
						}
						$spoke_tests[ $module ][ $test_id ] = $test_result;
					}
				}
			}
		}

		return array(
			'core'   => $core_tests,
			'hubs'   => $hub_tests,
			'spokes' => $spoke_tests,
		);
	}

	/**
	 * Test overall environment compatibility.
	 *
	 * @return array
	 */
	public static function test_environment_compatibility(): array {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_Environment_Checker' ) ) {
			return WPSHADOW_Health_Renderer::build_result(
				__( 'Environment checker not available', 'plugin-wpshadow' ),
				'recommended',
				esc_html__( 'Environment checker class is not loaded.', 'plugin-wpshadow' ),
				'wpshadow_environment_compatibility'
			);
		}

		$is_compatible   = \WPShadow\WPSHADOW_Environment_Checker::is_environment_compatible();
		$has_constraints = \WPShadow\WPSHADOW_Environment_Checker::has_resource_constraints();

		if ( $is_compatible && ! $has_constraints ) {
			return WPSHADOW_Health_Renderer::build_result(
				__( 'Environment is fully compatible', 'plugin-wpshadow' ),
				'good',
				esc_html__( 'Your server environment meets all requirements and has sufficient resources for optimal performance.', 'plugin-wpshadow' ),
				'wpshadow_environment_compatibility'
			);
		}

		if ( $is_compatible && $has_constraints ) {
			return WPSHADOW_Health_Renderer::build_result(
				__( 'Environment has resource constraints', 'plugin-wpshadow' ),
				'recommended',
				esc_html__( 'Your server environment meets minimum requirements but has resource constraints. Heavy operations will be batched automatically.', 'plugin-wpshadow' ),
				'wpshadow_environment_compatibility',
				'',
				'orange'
			);
		}

		return WPSHADOW_Health_Renderer::build_result(
			__( 'Environment is incompatible', 'plugin-wpshadow' ),
			'critical',
			esc_html__( 'Your server environment does not meet minimum requirements. Heavy operations have been disabled to prevent errors.', 'plugin-wpshadow' ),
			'wpshadow_environment_compatibility',
			'',
			'red'
		);
	}

	/**
	 * Test memory limit status.
	 *
	 * @return array
	 */
	public static function test_memory_limit(): array {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_Environment_Checker' ) ) {
			return WPSHADOW_Health_Renderer::build_result(
				__( 'Memory limit check unavailable', 'plugin-wpshadow' ),
				'recommended',
				esc_html__( 'Environment checker class is not loaded.', 'plugin-wpshadow' ),
				'wpshadow_memory_limit'
			);
		}

		$status = \WPShadow\WPSHADOW_Environment_Checker::get_memory_limit_status();

		if ( 'good' === $status['level'] ) {
			return WPSHADOW_Health_Renderer::build_result(
				sprintf(
					/* translators: %s: Memory limit */
					__( 'Memory limit is optimal (%s)', 'plugin-wpshadow' ),
					$status['current']
				),
				'good',
				esc_html( $status['message'] ),
				'wpshadow_memory_limit'
			);
		}

		if ( 'warning' === $status['level'] ) {
			return WPSHADOW_Health_Renderer::build_result(
				sprintf(
					/* translators: %s: Memory limit */
					__( 'Memory limit is adequate (%s)', 'plugin-wpshadow' ),
					$status['current']
				),
				'recommended',
				esc_html( $status['message'] ),
				'wpshadow_memory_limit',
				'',
				'orange'
			);
		}

		return WPSHADOW_Health_Renderer::build_result(
			sprintf(
				/* translators: %s: Memory limit */
				__( 'Memory limit is insufficient (%s)', 'plugin-wpshadow' ),
				$status['current']
			),
			'critical',
			esc_html( $status['message'] ),
			'wpshadow_memory_limit',
			'',
			'red'
		);
	}

	/**
	 * Test execution time limit status.
	 *
	 * @return array
	 */
	public static function test_execution_time(): array {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_Environment_Checker' ) ) {
			return WPSHADOW_Health_Renderer::build_result(
				__( 'Execution time check unavailable', 'plugin-wpshadow' ),
				'recommended',
				esc_html__( 'Environment checker class is not loaded.', 'plugin-wpshadow' ),
				'wpshadow_execution_time'
			);
		}

		$status = \WPShadow\WPSHADOW_Environment_Checker::get_execution_time_status();

		if ( 'good' === $status['level'] ) {
			$time_display = 0 === $status['current']
				? __( 'unlimited', 'plugin-wpshadow' )
				: $status['current'] . 's';

			return WPSHADOW_Health_Renderer::build_result(
				sprintf(
					/* translators: %s: Execution time */
					__( 'Execution time is optimal (%s)', 'plugin-wpshadow' ),
					$time_display
				),
				'good',
				esc_html( $status['message'] ),
				'wpshadow_execution_time'
			);
		}

		if ( 'warning' === $status['level'] ) {
			return WPSHADOW_Health_Renderer::build_result(
				sprintf(
					/* translators: %s: Execution time */
					__( 'Execution time is adequate (%s)', 'plugin-wpshadow' ),
					$status['current'] . 's'
				),
				'recommended',
				esc_html( $status['message'] ),
				'wpshadow_execution_time',
				'',
				'orange'
			);
		}

		return WPSHADOW_Health_Renderer::build_result(
			sprintf(
				/* translators: %s: Execution time */
				__( 'Execution time is insufficient (%s)', 'plugin-wpshadow' ),
				$status['current'] . 's'
			),
			'critical',
			esc_html( $status['message'] ),
			'wpshadow_execution_time',
			'',
			'red'
		);
	}

	/**
	 * Test required PHP extensions.
	 *
	 * @return array
	 */
	public static function test_required_extensions(): array {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_Environment_Checker' ) ) {
			return WPSHADOW_Health_Renderer::build_result(
				__( 'Extensions check unavailable', 'plugin-wpshadow' ),
				'recommended',
				esc_html__( 'Environment checker class is not loaded.', 'plugin-wpshadow' ),
				'wpshadow_required_extensions'
			);
		}

		$status = \WPShadow\WPSHADOW_Environment_Checker::get_extensions_status();

		if ( $status['all_required_loaded'] && empty( $status['recommended_missing'] ) ) {
			return WPSHADOW_Health_Renderer::build_result(
				__( 'All required and recommended extensions loaded', 'plugin-wpshadow' ),
				'good',
				esc_html( $status['message'] ),
				'wpshadow_required_extensions'
			);
		}

		if ( $status['all_required_loaded'] && ! empty( $status['recommended_missing'] ) ) {
			return WPSHADOW_Health_Renderer::build_result(
				__( 'All required extensions loaded', 'plugin-wpshadow' ),
				'recommended',
				esc_html( $status['message'] ),
				'wpshadow_required_extensions',
				'',
				'orange'
			);
		}

		return WPSHADOW_Health_Renderer::build_result(
			__( 'Required extensions missing', 'plugin-wpshadow' ),
			'critical',
			esc_html( $status['message'] ),
			'wpshadow_required_extensions',
			'',
			'red'
		);
	}

	/**
	 * Test current resource usage.
	 *
	 * @return array
	 */
	public static function test_resource_usage(): array {
		if ( ! class_exists( '\\WPShadow\\WPSHADOW_Server_Limits' ) ) {
			return WPSHADOW_Health_Renderer::build_result(
				__( 'Resource usage check unavailable', 'plugin-wpshadow' ),
				'recommended',
				esc_html__( 'Server limits class is not loaded.', 'plugin-wpshadow' ),
				'wpshadow_resource_usage'
			);
		}

		$status = \WPShadow\WPSHADOW_Server_Limits::get_resource_status();
		$memory = $status['memory'];
		$time   = $status['time'];

		$description = sprintf(
			/* translators: 1: Memory usage percentage, 2: Time usage percentage */
			esc_html__( 'Current resource usage: Memory %1$s%%, Execution time %2$s%%', 'plugin-wpshadow' ),
			number_format( $memory['usage_percentage'], 1 ),
			number_format( $time['usage_percentage'], 1 )
		);

		if ( 'good' === $status['level'] ) {
			return WPSHADOW_Health_Renderer::build_result(
				__( 'Resource usage is optimal', 'plugin-wpshadow' ),
				'good',
				$description,
				'wpshadow_resource_usage'
			);
		}

		if ( 'warning' === $status['level'] ) {
			return WPSHADOW_Health_Renderer::build_result(
				__( 'Resource usage is moderate', 'plugin-wpshadow' ),
				'recommended',
				$description . ' ' . esc_html__( 'Operations will be batched automatically.', 'plugin-wpshadow' ),
				'wpshadow_resource_usage',
				'',
				'orange'
			);
		}

		return WPSHADOW_Health_Renderer::build_result(
			__( 'Resource usage is high', 'plugin-wpshadow' ),
			'critical',
			$description . ' ' . esc_html__( 'Heavy operations should be avoided.', 'plugin-wpshadow' ),
			'wpshadow_resource_usage',
			'',
			'red'
		);
	}
}

/**
 * Example: How modules should register their health checks
 *
 * Modules should register health checks during initialization using the
 * 'wpshadow_register_health_checks' action hook.
 *
 * @example
 * ```php
 * add_action( 'wpshadow_register_health_checks', function() {
 *     \WPShadow\WPSHADOW_Site_Health::register_module_checks(
 *         'media-wpshadow',
 *         array(
 *             'wpshadow_imagick_available' => array(
 *                 'label' => __( 'ImageMagick availability', 'media-wpshadow' ),
 *                 'test'  => array( 'MediaHub\Health', 'test_imagick' ),
 *             ),
 *             'wpshadow_gd_available' => array(
 *                 'label' => __( 'GD library availability', 'media-wpshadow' ),
 *                 'test'  => array( 'MediaHub\Health', 'test_gd' ),
 *             ),
 *         )
 *     );
 * });
 * ```
 *
 * Health check test methods should return an array with the following structure:
 * ```php
 * array(
 *     'label'       => 'Check name',
 *     'status'      => 'good|recommended|critical',
 *     'badge'       => array( 'label' => 'Status', 'color' => 'green|orange|red' ),
 *     'description' => 'Detailed message about the health check result',
 *     'test'        => 'test_id',
 * )
 * ```
 */
