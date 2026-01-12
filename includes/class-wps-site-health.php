<?php
/**
 * Site Health integration for WPS Suite.
 *
 * @package wp_support_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/class-wps-health-renderer.php';

/**
 * Site Health integration class.
 */
class WPS_Site_Health {

	/**
	 * Initialize Site Health integration.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_filter( 'site_status_tests', array( __CLASS__, 'add_tests' ) );
		add_filter( 'debug_information', array( __CLASS__, 'add_debug_info' ) );
	}

	/**
	 * Register WPS health tests.
	 *
	 * @param array $tests Site Health tests array.
	 * @return array
	 */
	public static function add_tests( array $tests ): array {
		// Check if Vault module is enabled.
		$modules       = WPS_Module_Registry::get_catalog_with_status();
		$vault_enabled = ! empty( $modules['vault']['enabled'] );

		// Only register Vault-specific tests if Vault is enabled.
		if ( $vault_enabled ) {
			$tests['direct']['WPS_vault_directory'] = array(
				'label' => __( 'Vault directory status', 'plugin-wp-support-thisismyurl' ),
				'test'  => array( __CLASS__, 'test_vault_directory' ),
			);

			$tests['direct']['WPS_encryption_config'] = array(
				'label' => __( 'Encryption configuration', 'plugin-wp-support-thisismyurl' ),
				'test'  => array( __CLASS__, 'test_encryption_config' ),
			);

			$tests['direct']['WPS_vault_permissions'] = array(
				'label' => __( 'Vault write permissions', 'plugin-wp-support-thisismyurl' ),
				'test'  => array( __CLASS__, 'test_vault_permissions' ),
			);
		}

		// These are general tests that should always be shown.
		$tests['direct']['WPS_openssl_extension'] = array(
			'label' => __( 'OpenSSL extension', 'plugin-wp-support-thisismyurl' ),
			'test'  => array( __CLASS__, 'test_openssl_extension' ),
		);

		$tests['direct']['WPS_php_version'] = array(
			'label' => __( 'PHP version compliance', 'plugin-wp-support-thisismyurl' ),
			'test'  => array( __CLASS__, 'test_php_version' ),
		);

		$tests['direct']['WPS_wordpress_version'] = array(
			'label' => __( 'WordPress version compliance', 'plugin-wp-support-thisismyurl' ),
			'test'  => array( __CLASS__, 'test_wordpress_version' ),
		);

		$tests['direct']['WPS_module_status'] = array(
			'label' => __( 'Module status', 'plugin-wp-support-thisismyurl' ),
			'test'  => array( __CLASS__, 'test_module_status' ),
		);

		// Environment and server limit tests.
		$tests['direct']['WPS_environment_compatibility'] = array(
			'label' => __( 'Environment compatibility', 'plugin-wp-support-thisismyurl' ),
			'test'  => array( __CLASS__, 'test_environment_compatibility' ),
		);

		$tests['direct']['WPS_memory_limit'] = array(
			'label' => __( 'Memory limit status', 'plugin-wp-support-thisismyurl' ),
			'test'  => array( __CLASS__, 'test_memory_limit' ),
		);

		$tests['direct']['WPS_execution_time'] = array(
			'label' => __( 'Execution time limit', 'plugin-wp-support-thisismyurl' ),
			'test'  => array( __CLASS__, 'test_execution_time' ),
		);

		$tests['direct']['WPS_required_extensions'] = array(
			'label' => __( 'Required PHP extensions', 'plugin-wp-support-thisismyurl' ),
			'test'  => array( __CLASS__, 'test_required_extensions' ),
		);

		$tests['direct']['WPS_resource_usage'] = array(
			'label' => __( 'Current resource usage', 'plugin-wp-support-thisismyurl' ),
			'test'  => array( __CLASS__, 'test_resource_usage' ),
		);

		return $tests;
	}

	/**
	 * Test vault directory status.
	 *
	 * @return array
	 */
	public static function test_vault_directory(): array {
		$upload_dir    = wp_upload_dir();
		$vault_dirname = get_option( 'WPS_vault_dirname' );

		if ( empty( $vault_dirname ) ) {
			return WPS_Health_Renderer::build_result(
				__( 'Vault directory not configured', 'plugin-wp-support-thisismyurl' ),
				'recommended',
				esc_html__( 'The vault directory has not been created yet. It will be created automatically on first use.', 'plugin-wp-support-thisismyurl' ),
				'WPS_vault_directory',
				'',
				'orange'
			);
		}

		$vault_path = $upload_dir['basedir'] . '/' . $vault_dirname;

		if ( ! file_exists( $vault_path ) ) {
			return WPS_Health_Renderer::build_result(
				__( 'Vault directory missing', 'plugin-wp-support-thisismyurl' ),
				'critical',
				sprintf(
					/* translators: %s: vault path */
					esc_html__( 'The vault directory was configured but does not exist at: %s', 'plugin-wp-support-thisismyurl' ),
					'<code>' . esc_html( $vault_path ) . '</code>'
				),
				'WPS_vault_directory',
				'',
				'red'
			);
		}

		return WPS_Health_Renderer::build_result(
			__( 'Vault directory configured', 'plugin-wp-support-thisismyurl' ),
			'good',
			sprintf(
				/* translators: %s: vault path */
				esc_html__( 'The vault directory exists at: %s', 'plugin-wp-support-thisismyurl' ),
				'<code>' . esc_html( $vault_path ) . '</code>'
			),
			'WPS_vault_directory'
		);
	}

	/**
	 * Test encryption configuration.
	 *
	 * @return array
	 */
	public static function test_encryption_config(): array {
		$is_production = 'production' === wp_get_environment_type();

		if ( defined( 'WPS_VAULT_KEY' ) && WPS_VAULT_KEY ) {
			return WPS_Health_Renderer::build_result(
				__( 'Encryption key configured in wp-config.php', 'plugin-wp-support-thisismyurl' ),
				'good',
				esc_html__( 'Encryption key is properly defined in wp-config.php.', 'plugin-wp-support-thisismyurl' ),
				'WPS_encryption_config'
			);
		}

		$stored_key = get_option( 'WPS_vault_encryption_key' );

		if ( ! empty( $stored_key ) && $is_production ) {
			return WPS_Health_Renderer::build_result(
				__( 'Encryption key should be in wp-config.php', 'plugin-wp-support-thisismyurl' ),
				'critical',
				sprintf(
					'%s<br><br>%s',
					esc_html__( 'For production sites, encryption keys must be defined in wp-config.php, not stored in the database.', 'plugin-wp-support-thisismyurl' ),
					sprintf(
						/* translators: %s: example code */
						esc_html__( 'Add this line to your wp-config.php: %s', 'plugin-wp-support-thisismyurl' ),
						'<code>define( "WPS_VAULT_KEY", "' . esc_html( $stored_key ) . '" );</code>'
					)
				),
				'WPS_encryption_config',
				'',
				'red'
			);
		}

		if ( ! empty( $stored_key ) ) {
			return WPS_Health_Renderer::build_result(
				__( 'Encryption key in options (development mode)', 'plugin-wp-support-thisismyurl' ),
				'recommended',
				esc_html__( 'Encryption key is stored in the database. This is acceptable for development but should be moved to wp-config.php for production.', 'plugin-wp-support-thisismyurl' ),
				'WPS_encryption_config',
				'',
				'orange'
			);
		}

		return WPS_Health_Renderer::build_result(
			__( 'Encryption key not configured', 'plugin-wp-support-thisismyurl' ),
			'recommended',
			esc_html__( 'No encryption key is configured. An encryption key will be generated automatically when needed.', 'plugin-wp-support-thisismyurl' ),
			'WPS_encryption_config',
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
			return WPS_Health_Renderer::build_result(
				__( 'OpenSSL extension is available', 'plugin-wp-support-thisismyurl' ),
				'good',
				esc_html__( 'The OpenSSL PHP extension is loaded and encryption features are available.', 'plugin-wp-support-thisismyurl' ),
				'WPS_openssl_extension'
			);
		}

		return WPS_Health_Renderer::build_result(
			__( 'OpenSSL extension is not available', 'plugin-wp-support-thisismyurl' ),
			'critical',
			esc_html__( 'The OpenSSL PHP extension is required for encryption features. Contact your hosting provider to enable it.', 'plugin-wp-support-thisismyurl' ),
			'WPS_openssl_extension',
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
		if ( version_compare( PHP_VERSION, wp_support_MIN_PHP, '>=' ) ) {
			return WPS_Health_Renderer::build_result(
				sprintf(
					/* translators: %s: PHP version */
					__( 'PHP version %s meets requirements', 'plugin-wp-support-thisismyurl' ),
					PHP_VERSION
				),
				'good',
				sprintf(
					/* translators: 1: current PHP version, 2: minimum required version */
					esc_html__( 'Your PHP version (%1$s) meets the minimum requirement of %2$s.', 'plugin-wp-support-thisismyurl' ),
					PHP_VERSION,
					wp_support_MIN_PHP
				),
				'WPS_php_version'
			);
		}

		return WPS_Health_Renderer::build_result(
			__( 'PHP version below minimum requirement', 'plugin-wp-support-thisismyurl' ),
			'critical',
			sprintf(
				/* translators: 1: current PHP version, 2: minimum required version */
				esc_html__( 'Your PHP version (%1$s) is below the minimum requirement of %2$s. Contact your hosting provider to upgrade PHP.', 'plugin-wp-support-thisismyurl' ),
				PHP_VERSION,
				wp_support_MIN_PHP
			),
			'WPS_php_version',
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

		if ( version_compare( $wp_version, wp_support_MIN_WP, '>=' ) ) {
			return WPS_Health_Renderer::build_result(
				sprintf(
					/* translators: %s: WordPress version */
					__( 'WordPress version %s meets requirements', 'plugin-wp-support-thisismyurl' ),
					$wp_version
				),
				'good',
				sprintf(
					/* translators: 1: current WordPress version, 2: minimum required version */
					esc_html__( 'Your WordPress version (%1$s) meets the minimum requirement of %2$s.', 'plugin-wp-support-thisismyurl' ),
					$wp_version,
					wp_support_MIN_WP
				),
				'WPS_wordpress_version'
			);
		}

		return WPS_Health_Renderer::build_result(
			__( 'WordPress version below minimum requirement', 'plugin-wp-support-thisismyurl' ),
			'critical',
			sprintf(
				/* translators: 1: current WordPress version, 2: minimum required version */
				esc_html__( 'Your WordPress version (%1$s) is below the minimum requirement of %2$s. Please update WordPress.', 'plugin-wp-support-thisismyurl' ),
				$wp_version,
				wp_support_MIN_WP
			),
			'WPS_wordpress_version',
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
		$vault_dirname = get_option( 'WPS_vault_dirname' );

		if ( empty( $vault_dirname ) ) {
			return WPS_Health_Renderer::build_result(
				__( 'Vault not configured yet', 'plugin-wp-support-thisismyurl' ),
				'recommended',
				esc_html__( 'Vault directory will be created with appropriate permissions when first needed.', 'plugin-wp-support-thisismyurl' ),
				'WPS_vault_permissions',
				'',
				'gray'
			);
		}

		$vault_path = $upload_dir['basedir'] . '/' . $vault_dirname;

		if ( ! file_exists( $vault_path ) ) {
			return WPS_Health_Renderer::build_result(
				__( 'Vault directory does not exist', 'plugin-wp-support-thisismyurl' ),
				'critical',
				esc_html__( 'The vault directory was expected but does not exist. It will be recreated on next use.', 'plugin-wp-support-thisismyurl' ),
				'WPS_vault_permissions',
				'',
				'red'
			);
		}

		if ( ! wp_is_writable( $vault_path ) ) {
			return WPS_Health_Renderer::build_result(
				__( 'Vault directory is not writable', 'plugin-wp-support-thisismyurl' ),
				'critical',
				sprintf(
					/* translators: %s: vault path */
					esc_html__( 'The vault directory exists but is not writable: %s. Check directory permissions.', 'plugin-wp-support-thisismyurl' ),
					'<code>' . esc_html( $vault_path ) . '</code>'
				),
				'WPS_vault_permissions',
				'',
				'red'
			);
		}

		return WPS_Health_Renderer::build_result(
			__( 'Vault directory has correct permissions', 'plugin-wp-support-thisismyurl' ),
			'good',
			esc_html__( 'The vault directory is writable and ready for use.', 'plugin-wp-support-thisismyurl' ),
			'WPS_vault_permissions'
		);
	}

	/**
	 * Test module status.
	 *
	 * @return array
	 */
	public static function test_module_status(): array {
		$modules      = WPS_Module_Registry::get_catalog_with_status();
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
			return WPS_Health_Renderer::build_result(
				__( 'No modules registered', 'plugin-wp-support-thisismyurl' ),
				'recommended',
				esc_html__( 'No WPS Suite modules have been registered yet. This is normal if no additional modules are installed.', 'plugin-wp-support-thisismyurl' ),
				'WPS_module_status',
				'',
				'gray'
			);
		}

		return WPS_Health_Renderer::build_result(
			sprintf(
				/* translators: 1: number of active modules, 2: total modules */
				__( '%1$d of %2$d modules active', 'plugin-wp-support-thisismyurl' ),
				$active_count,
				$module_count
			),
			'good',
			sprintf(
				/* translators: 1: hub count, 2: spoke count */
				esc_html__( 'WPS Suite has %1$d hubs and %2$d spokes registered.', 'plugin-wp-support-thisismyurl' ),
				$hub_count,
				$spoke_count
			),
			'WPS_module_status'
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
		$vault_dirname = get_option( 'WPS_vault_dirname', __( 'Not configured', 'plugin-wp-support-thisismyurl' ) );
		$vault_path    = ! empty( $vault_dirname ) && 'Not configured' !== $vault_dirname
			? $upload_dir['basedir'] . '/' . $vault_dirname
			: __( 'Not configured', 'plugin-wp-support-thisismyurl' );

		$modules     = WPS_Module_Registry::get_catalog_with_status();
		$module_list = array();
		foreach ( $modules as $slug => $module ) {
			// Only show enabled modules in the site report.
			if ( empty( $module['enabled'] ) ) {
				continue;
			}
			$module_list[] = sprintf( '%s v%s', $module['name'] ?? $slug, $module['version'] ?? '?' );
		}

		$encryption_key_source = defined( 'WPS_VAULT_KEY' ) && WPS_VAULT_KEY
			? __( 'wp-config.php', 'plugin-wp-support-thisismyurl' )
			: ( get_option( 'WPS_vault_encryption_key' ) ? __( 'Options table', 'plugin-wp-support-thisismyurl' ) : __( 'Not configured', 'plugin-wp-support-thisismyurl' ) );

		$info['wps-suite'] = array(
			'label'  => __( 'WPS Suite', 'plugin-wp-support-thisismyurl' ),
			'fields' => array(
				'core_version'          => array(
					'label' => __( 'Core version', 'plugin-wp-support-thisismyurl' ),
					'value' => wp_support_VERSION,
				),
				'suite_id'              => array(
					'label' => __( 'Suite ID', 'plugin-wp-support-thisismyurl' ),
					'value' => WPS_SUITE_ID,
				),
				'text_domain'           => array(
					'label' => __( 'Text domain', 'plugin-wp-support-thisismyurl' ),
					'value' => wp_support_TEXT_DOMAIN,
				),
				'vault_dirname'         => array(
					'label' => __( 'Vault directory name', 'plugin-wp-support-thisismyurl' ),
					'value' => $vault_dirname,
				),
				'vault_path'            => array(
					'label' => __( 'Vault path', 'plugin-wp-support-thisismyurl' ),
					'value' => $vault_path,
				),
				'encryption_key_source' => array(
					'label' => __( 'Encryption key source', 'plugin-wp-support-thisismyurl' ),
					'value' => $encryption_key_source,
				),
				'openssl_loaded'        => array(
					'label' => __( 'OpenSSL extension', 'plugin-wp-support-thisismyurl' ),
					'value' => extension_loaded( 'openssl' ) ? __( 'Loaded', 'plugin-wp-support-thisismyurl' ) : __( 'Not loaded', 'plugin-wp-support-thisismyurl' ),
				),
				'registered_modules'    => array(
					'label' => __( 'Registered modules', 'plugin-wp-support-thisismyurl' ),
					'value' => ! empty( $module_list ) ? implode( "\n", $module_list ) : __( 'None', 'plugin-wp-support-thisismyurl' ),
				),
			),
		);

		// Add environment information.
		if ( class_exists( '\\WPS\\CoreSupport\\WPS_Environment_Checker' ) ) {
			$env_status = WPS_Environment_Checker::get_environment_status();

			$info['wps-environment'] = array(
				'label'  => __( 'WPS Environment', 'plugin-wp-support-thisismyurl' ),
				'fields' => array(
					'compatibility_status'      => array(
						'label' => __( 'Environment compatibility', 'plugin-wp-support-thisismyurl' ),
						'value' => $env_status['is_compatible'] ? __( 'Compatible', 'plugin-wp-support-thisismyurl' ) : __( 'Incompatible', 'plugin-wp-support-thisismyurl' ),
					),
					'resource_constraints'      => array(
						'label' => __( 'Resource constraints', 'plugin-wp-support-thisismyurl' ),
						'value' => $env_status['has_constraints'] ? __( 'Yes', 'plugin-wp-support-thisismyurl' ) : __( 'No', 'plugin-wp-support-thisismyurl' ),
					),
					'memory_limit'              => array(
						'label' => __( 'Memory limit', 'plugin-wp-support-thisismyurl' ),
						'value' => $env_status['memory_limit']['current'] . ' (' . $env_status['memory_limit']['level'] . ')',
					),
					'execution_time'            => array(
						'label' => __( 'Max execution time', 'plugin-wp-support-thisismyurl' ),
						'value' => 0 === $env_status['execution_time']['current']
							? __( 'Unlimited', 'plugin-wp-support-thisismyurl' )
							: $env_status['execution_time']['current'] . 's (' . $env_status['execution_time']['level'] . ')',
					),
					'upload_max_filesize'       => array(
						'label' => __( 'Upload max filesize', 'plugin-wp-support-thisismyurl' ),
						'value' => $env_status['upload_limit']['upload_max_filesize'],
					),
					'post_max_size'             => array(
						'label' => __( 'Post max size', 'plugin-wp-support-thisismyurl' ),
						'value' => $env_status['upload_limit']['post_max_size'],
					),
					'required_extensions'       => array(
						'label' => __( 'Required extensions', 'plugin-wp-support-thisismyurl' ),
						'value' => $env_status['extensions']['all_required_loaded']
							? __( 'All loaded', 'plugin-wp-support-thisismyurl' )
							: __( 'Missing: ', 'plugin-wp-support-thisismyurl' ) . implode( ', ', $env_status['extensions']['required_missing'] ),
					),
					'recommended_extensions'    => array(
						'label' => __( 'Recommended extensions', 'plugin-wp-support-thisismyurl' ),
						'value' => empty( $env_status['extensions']['recommended_missing'] )
							? __( 'All loaded', 'plugin-wp-support-thisismyurl' )
							: __( 'Missing: ', 'plugin-wp-support-thisismyurl' ) . implode( ', ', $env_status['extensions']['recommended_missing'] ),
					),
					'diagnostic_logging'        => array(
						'label' => __( 'Diagnostic logging', 'plugin-wp-support-thisismyurl' ),
						'value' => get_option( 'wps_diagnostic_logging_enabled', false ) ? __( 'Enabled', 'plugin-wp-support-thisismyurl' ) : __( 'Disabled', 'plugin-wp-support-thisismyurl' ),
					),
					'heavy_tasks_disabled'      => array(
						'label' => __( 'Heavy tasks disabled', 'plugin-wp-support-thisismyurl' ),
						'value' => WPS_Environment_Checker::should_disable_heavy_tasks() ? __( 'Yes', 'plugin-wp-support-thisismyurl' ) : __( 'No', 'plugin-wp-support-thisismyurl' ),
					),
					'batching_enabled'          => array(
						'label' => __( 'Task batching', 'plugin-wp-support-thisismyurl' ),
						'value' => WPS_Environment_Checker::should_batch_tasks() ? __( 'Enabled', 'plugin-wp-support-thisismyurl' ) : __( 'Not required', 'plugin-wp-support-thisismyurl' ),
					),
				),
			);
		}

		// Add resource usage information.
		if ( class_exists( '\\WPS\\CoreSupport\\WPS_Server_Limits' ) ) {
			$resource_status = WPS_Server_Limits::get_resource_status();

			$info['wps-resources'] = array(
				'label'  => __( 'WPS Resource Usage', 'plugin-wp-support-thisismyurl' ),
				'fields' => array(
					'memory_usage'          => array(
						'label' => __( 'Current memory usage', 'plugin-wp-support-thisismyurl' ),
						'value' => WPS_Environment_Checker::format_bytes( $resource_status['memory']['current_usage'] ) . ' / ' . $resource_status['memory']['limit'],
					),
					'memory_percentage'     => array(
						'label' => __( 'Memory usage percentage', 'plugin-wp-support-thisismyurl' ),
						'value' => number_format( $resource_status['memory']['usage_percentage'], 2 ) . '%',
					),
					'peak_memory'           => array(
						'label' => __( 'Peak memory usage', 'plugin-wp-support-thisismyurl' ),
						'value' => WPS_Environment_Checker::format_bytes( $resource_status['memory']['peak_usage'] ),
					),
					'time_elapsed'          => array(
						'label' => __( 'Time elapsed', 'plugin-wp-support-thisismyurl' ),
						'value' => number_format( $resource_status['time']['elapsed'], 2 ) . 's',
					),
					'time_percentage'       => array(
						'label' => __( 'Time usage percentage', 'plugin-wp-support-thisismyurl' ),
						'value' => number_format( $resource_status['time']['usage_percentage'], 2 ) . '%',
					),
					'resource_level'        => array(
						'label' => __( 'Overall resource level', 'plugin-wp-support-thisismyurl' ),
						'value' => ucfirst( $resource_status['level'] ),
					),
					'batch_size'            => array(
						'label' => __( 'Recommended batch size', 'plugin-wp-support-thisismyurl' ),
						'value' => (string) WPS_Server_Limits::get_batch_size(),
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
		$tests = array(
			'vault_directory'   => array(
				'label'  => __( 'Vault Directory Status', 'plugin-wp-support-thisismyurl' ),
				'test'   => 'test_vault_directory',
				'module' => 'vault',
			),
			'encryption_config' => array(
				'label'  => __( 'Encryption Configuration', 'plugin-wp-support-thisismyurl' ),
				'test'   => 'test_encryption_config',
				'module' => 'vault',
			),
			'openssl_extension' => array(
				'label'  => __( 'OpenSSL Extension', 'plugin-wp-support-thisismyurl' ),
				'test'   => 'test_openssl_extension',
				'module' => 'core',
			),
			'php_version'       => array(
				'label'  => __( 'PHP Version Compliance', 'plugin-wp-support-thisismyurl' ),
				'test'   => 'test_php_version',
				'module' => 'core',
			),
			'wordpress_version' => array(
				'label'  => __( 'WordPress Version Compliance', 'plugin-wp-support-thisismyurl' ),
				'test'   => 'test_wordpress_version',
				'module' => 'core',
			),
			'vault_permissions' => array(
				'label'  => __( 'Vault Write Permissions', 'plugin-wp-support-thisismyurl' ),
				'test'   => 'test_vault_permissions',
				'module' => 'vault',
			),
			'module_status'     => array(
				'label'  => __( 'Module Status', 'plugin-wp-support-thisismyurl' ),
				'test'   => 'test_module_status',
				'module' => 'core',
			),
		);

		$results        = array();
		$critical_count = 0;
		$warning_count  = 0;
		$good_count     = 0;

		foreach ( $tests as $test_id => $test_data ) {
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
		$catalog         = \WPS\CoreSupport\WPS_Module_Registry::get_catalog_with_status();
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
	 * Test overall environment compatibility.
	 *
	 * @return array
	 */
	public static function test_environment_compatibility(): array {
		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Environment_Checker' ) ) {
			return WPS_Health_Renderer::build_result(
				__( 'Environment checker not available', 'plugin-wp-support-thisismyurl' ),
				'recommended',
				esc_html__( 'Environment checker class is not loaded.', 'plugin-wp-support-thisismyurl' ),
				'WPS_environment_compatibility'
			);
		}

		$is_compatible = WPS_Environment_Checker::is_environment_compatible();
		$has_constraints = WPS_Environment_Checker::has_resource_constraints();

		if ( $is_compatible && ! $has_constraints ) {
			return WPS_Health_Renderer::build_result(
				__( 'Environment is fully compatible', 'plugin-wp-support-thisismyurl' ),
				'good',
				esc_html__( 'Your server environment meets all requirements and has sufficient resources for optimal performance.', 'plugin-wp-support-thisismyurl' ),
				'WPS_environment_compatibility'
			);
		}

		if ( $is_compatible && $has_constraints ) {
			return WPS_Health_Renderer::build_result(
				__( 'Environment has resource constraints', 'plugin-wp-support-thisismyurl' ),
				'recommended',
				esc_html__( 'Your server environment meets minimum requirements but has resource constraints. Heavy operations will be batched automatically.', 'plugin-wp-support-thisismyurl' ),
				'WPS_environment_compatibility',
				'',
				'orange'
			);
		}

		return WPS_Health_Renderer::build_result(
			__( 'Environment is incompatible', 'plugin-wp-support-thisismyurl' ),
			'critical',
			esc_html__( 'Your server environment does not meet minimum requirements. Heavy operations have been disabled to prevent errors.', 'plugin-wp-support-thisismyurl' ),
			'WPS_environment_compatibility',
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
		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Environment_Checker' ) ) {
			return WPS_Health_Renderer::build_result(
				__( 'Memory limit check unavailable', 'plugin-wp-support-thisismyurl' ),
				'recommended',
				esc_html__( 'Environment checker class is not loaded.', 'plugin-wp-support-thisismyurl' ),
				'WPS_memory_limit'
			);
		}

		$status = WPS_Environment_Checker::get_memory_limit_status();

		if ( 'good' === $status['level'] ) {
			return WPS_Health_Renderer::build_result(
				sprintf(
					/* translators: %s: Memory limit */
					__( 'Memory limit is optimal (%s)', 'plugin-wp-support-thisismyurl' ),
					$status['current']
				),
				'good',
				esc_html( $status['message'] ),
				'WPS_memory_limit'
			);
		}

		if ( 'warning' === $status['level'] ) {
			return WPS_Health_Renderer::build_result(
				sprintf(
					/* translators: %s: Memory limit */
					__( 'Memory limit is adequate (%s)', 'plugin-wp-support-thisismyurl' ),
					$status['current']
				),
				'recommended',
				esc_html( $status['message'] ),
				'WPS_memory_limit',
				'',
				'orange'
			);
		}

		return WPS_Health_Renderer::build_result(
			sprintf(
				/* translators: %s: Memory limit */
				__( 'Memory limit is insufficient (%s)', 'plugin-wp-support-thisismyurl' ),
				$status['current']
			),
			'critical',
			esc_html( $status['message'] ),
			'WPS_memory_limit',
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
		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Environment_Checker' ) ) {
			return WPS_Health_Renderer::build_result(
				__( 'Execution time check unavailable', 'plugin-wp-support-thisismyurl' ),
				'recommended',
				esc_html__( 'Environment checker class is not loaded.', 'plugin-wp-support-thisismyurl' ),
				'WPS_execution_time'
			);
		}

		$status = WPS_Environment_Checker::get_execution_time_status();

		if ( 'good' === $status['level'] ) {
			$time_display = 0 === $status['current']
				? __( 'unlimited', 'plugin-wp-support-thisismyurl' )
				: $status['current'] . 's';

			return WPS_Health_Renderer::build_result(
				sprintf(
					/* translators: %s: Execution time */
					__( 'Execution time is optimal (%s)', 'plugin-wp-support-thisismyurl' ),
					$time_display
				),
				'good',
				esc_html( $status['message'] ),
				'WPS_execution_time'
			);
		}

		if ( 'warning' === $status['level'] ) {
			return WPS_Health_Renderer::build_result(
				sprintf(
					/* translators: %s: Execution time */
					__( 'Execution time is adequate (%s)', 'plugin-wp-support-thisismyurl' ),
					$status['current'] . 's'
				),
				'recommended',
				esc_html( $status['message'] ),
				'WPS_execution_time',
				'',
				'orange'
			);
		}

		return WPS_Health_Renderer::build_result(
			sprintf(
				/* translators: %s: Execution time */
				__( 'Execution time is insufficient (%s)', 'plugin-wp-support-thisismyurl' ),
				$status['current'] . 's'
			),
			'critical',
			esc_html( $status['message'] ),
			'WPS_execution_time',
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
		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Environment_Checker' ) ) {
			return WPS_Health_Renderer::build_result(
				__( 'Extensions check unavailable', 'plugin-wp-support-thisismyurl' ),
				'recommended',
				esc_html__( 'Environment checker class is not loaded.', 'plugin-wp-support-thisismyurl' ),
				'WPS_required_extensions'
			);
		}

		$status = WPS_Environment_Checker::get_extensions_status();

		if ( $status['all_required_loaded'] && empty( $status['recommended_missing'] ) ) {
			return WPS_Health_Renderer::build_result(
				__( 'All required and recommended extensions loaded', 'plugin-wp-support-thisismyurl' ),
				'good',
				esc_html( $status['message'] ),
				'WPS_required_extensions'
			);
		}

		if ( $status['all_required_loaded'] && ! empty( $status['recommended_missing'] ) ) {
			return WPS_Health_Renderer::build_result(
				__( 'All required extensions loaded', 'plugin-wp-support-thisismyurl' ),
				'recommended',
				esc_html( $status['message'] ),
				'WPS_required_extensions',
				'',
				'orange'
			);
		}

		return WPS_Health_Renderer::build_result(
			__( 'Required extensions missing', 'plugin-wp-support-thisismyurl' ),
			'critical',
			esc_html( $status['message'] ),
			'WPS_required_extensions',
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
		if ( ! class_exists( '\\WPS\\CoreSupport\\WPS_Server_Limits' ) ) {
			return WPS_Health_Renderer::build_result(
				__( 'Resource usage check unavailable', 'plugin-wp-support-thisismyurl' ),
				'recommended',
				esc_html__( 'Server limits class is not loaded.', 'plugin-wp-support-thisismyurl' ),
				'WPS_resource_usage'
			);
		}

		$status = WPS_Server_Limits::get_resource_status();
		$memory = $status['memory'];
		$time = $status['time'];

		$description = sprintf(
			/* translators: 1: Memory usage percentage, 2: Time usage percentage */
			esc_html__( 'Current resource usage: Memory %1$s%%, Execution time %2$s%%', 'plugin-wp-support-thisismyurl' ),
			number_format( $memory['usage_percentage'], 1 ),
			number_format( $time['usage_percentage'], 1 )
		);

		if ( 'good' === $status['level'] ) {
			return WPS_Health_Renderer::build_result(
				__( 'Resource usage is optimal', 'plugin-wp-support-thisismyurl' ),
				'good',
				$description,
				'WPS_resource_usage'
			);
		}

		if ( 'warning' === $status['level'] ) {
			return WPS_Health_Renderer::build_result(
				__( 'Resource usage is moderate', 'plugin-wp-support-thisismyurl' ),
				'recommended',
				$description . ' ' . esc_html__( 'Operations will be batched automatically.', 'plugin-wp-support-thisismyurl' ),
				'WPS_resource_usage',
				'',
				'orange'
			);
		}

		return WPS_Health_Renderer::build_result(
			__( 'Resource usage is high', 'plugin-wp-support-thisismyurl' ),
			'critical',
			$description . ' ' . esc_html__( 'Heavy operations should be avoided.', 'plugin-wp-support-thisismyurl' ),
			'WPS_resource_usage',
			'',
			'red'
		);
	}
}
