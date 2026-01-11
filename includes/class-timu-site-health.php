<?php
/**
 * Site Health integration for TIMU Suite.
 *
 * @package wp_support_SUPPORT
 */

declare(strict_types=1);

namespace TIMU\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site Health integration class.
 */
class TIMU_Site_Health {

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
	 * Register TIMU health tests.
	 *
	 * @param array $tests Site Health tests array.
	 * @return array
	 */
	public static function add_tests( array $tests ): array {
		$tests['direct']['timu_vault_directory']    = array(
			'label' => __( 'Vault directory status', 'plugin-wp-support-thisismyurl' ),
			'test'  => array( __CLASS__, 'test_vault_directory' ),
		);

		$tests['direct']['timu_encryption_config']  = array(
			'label' => __( 'Encryption configuration', 'plugin-wp-support-thisismyurl' ),
			'test'  => array( __CLASS__, 'test_encryption_config' ),
		);

		$tests['direct']['timu_openssl_extension']  = array(
			'label' => __( 'OpenSSL extension', 'plugin-wp-support-thisismyurl' ),
			'test'  => array( __CLASS__, 'test_openssl_extension' ),
		);

		$tests['direct']['timu_php_version']        = array(
			'label' => __( 'PHP version compliance', 'plugin-wp-support-thisismyurl' ),
			'test'  => array( __CLASS__, 'test_php_version' ),
		);

		$tests['direct']['timu_wordpress_version']  = array(
			'label' => __( 'WordPress version compliance', 'plugin-wp-support-thisismyurl' ),
			'test'  => array( __CLASS__, 'test_wordpress_version' ),
		);

		$tests['direct']['timu_vault_permissions']  = array(
			'label' => __( 'Vault write permissions', 'plugin-wp-support-thisismyurl' ),
			'test'  => array( __CLASS__, 'test_vault_permissions' ),
		);

		$tests['direct']['timu_module_status']      = array(
			'label' => __( 'Module status', 'plugin-wp-support-thisismyurl' ),
			'test'  => array( __CLASS__, 'test_module_status' ),
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
		$vault_dirname = get_option( 'timu_vault_dirname' );

		if ( empty( $vault_dirname ) ) {
			return array(
				'label'       => __( 'Vault directory not configured', 'plugin-wp-support-thisismyurl' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'TIMU Suite', 'plugin-wp-support-thisismyurl' ),
					'color' => 'orange',
				),
				'description' => sprintf(
					'<p>%s</p>',
					esc_html__( 'The vault directory has not been created yet. It will be created automatically on first use.', 'plugin-wp-support-thisismyurl' )
				),
				'actions'     => '',
				'test'        => 'timu_vault_directory',
			);
		}

		$vault_path = $upload_dir['basedir'] . '/' . $vault_dirname;

		if ( ! file_exists( $vault_path ) ) {
			return array(
				'label'       => __( 'Vault directory missing', 'plugin-wp-support-thisismyurl' ),
				'status'      => 'critical',
				'badge'       => array(
					'label' => __( 'TIMU Suite', 'plugin-wp-support-thisismyurl' ),
					'color' => 'red',
				),
				'description' => sprintf(
					'<p>%s</p>',
					sprintf(
						/* translators: %s: vault path */
						esc_html__( 'The vault directory was configured but does not exist at: %s', 'plugin-wp-support-thisismyurl' ),
						'<code>' . esc_html( $vault_path ) . '</code>'
					)
				),
				'actions'     => '',
				'test'        => 'timu_vault_directory',
			);
		}

		return array(
			'label'       => __( 'Vault directory configured', 'plugin-wp-support-thisismyurl' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'TIMU Suite', 'plugin-wp-support-thisismyurl' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: %s: vault path */
					esc_html__( 'The vault directory exists at: %s', 'plugin-wp-support-thisismyurl' ),
					'<code>' . esc_html( $vault_path ) . '</code>'
				)
			),
			'actions'     => '',
			'test'        => 'timu_vault_directory',
		);
	}

	/**
	 * Test encryption configuration.
	 *
	 * @return array
	 */
	public static function test_encryption_config(): array {
		$is_production = 'production' === wp_get_environment_type();

		if ( defined( 'TIMU_VAULT_KEY' ) && TIMU_VAULT_KEY ) {
			return array(
				'label'       => __( 'Encryption key configured in wp-config.php', 'plugin-wp-support-thisismyurl' ),
				'status'      => 'good',
				'badge'       => array(
					'label' => __( 'TIMU Suite', 'plugin-wp-support-thisismyurl' ),
					'color' => 'blue',
				),
				'description' => sprintf(
					'<p>%s</p>',
					esc_html__( 'Encryption key is properly defined in wp-config.php.', 'plugin-wp-support-thisismyurl' )
				),
				'actions'     => '',
				'test'        => 'timu_encryption_config',
			);
		}

		$stored_key = get_option( 'timu_vault_encryption_key' );

		if ( ! empty( $stored_key ) && $is_production ) {
			return array(
				'label'       => __( 'Encryption key should be in wp-config.php', 'plugin-wp-support-thisismyurl' ),
				'status'      => 'critical',
				'badge'       => array(
					'label' => __( 'TIMU Suite', 'plugin-wp-support-thisismyurl' ),
					'color' => 'red',
				),
				'description' => sprintf(
					'<p>%s</p><p>%s</p>',
					esc_html__( 'For production sites, encryption keys must be defined in wp-config.php, not stored in the database.', 'plugin-wp-support-thisismyurl' ),
					sprintf(
						/* translators: %s: example code */
						esc_html__( 'Add this line to your wp-config.php: %s', 'plugin-wp-support-thisismyurl' ),
						'<code>define( "TIMU_VAULT_KEY", "' . esc_html( $stored_key ) . '" );</code>'
					)
				),
				'actions'     => '',
				'test'        => 'timu_encryption_config',
			);
		}

		if ( ! empty( $stored_key ) ) {
			return array(
				'label'       => __( 'Encryption key in options (development mode)', 'plugin-wp-support-thisismyurl' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'TIMU Suite', 'plugin-wp-support-thisismyurl' ),
					'color' => 'orange',
				),
				'description' => sprintf(
					'<p>%s</p>',
					esc_html__( 'Encryption key is stored in the database. This is acceptable for development but should be moved to wp-config.php for production.', 'plugin-wp-support-thisismyurl' )
				),
				'actions'     => '',
				'test'        => 'timu_encryption_config',
			);
		}

		return array(
			'label'       => __( 'Encryption key not configured', 'plugin-wp-support-thisismyurl' ),
			'status'      => 'recommended',
			'badge'       => array(
				'label' => __( 'TIMU Suite', 'plugin-wp-support-thisismyurl' ),
				'color' => 'orange',
			),
			'description' => sprintf(
				'<p>%s</p>',
				esc_html__( 'No encryption key is configured. An encryption key will be generated automatically when needed.', 'plugin-wp-support-thisismyurl' )
			),
			'actions'     => '',
			'test'        => 'timu_encryption_config',
		);
	}

	/**
	 * Test OpenSSL extension.
	 *
	 * @return array
	 */
	public static function test_openssl_extension(): array {
		if ( extension_loaded( 'openssl' ) ) {
			return array(
				'label'       => __( 'OpenSSL extension is available', 'plugin-wp-support-thisismyurl' ),
				'status'      => 'good',
				'badge'       => array(
					'label' => __( 'TIMU Suite', 'plugin-wp-support-thisismyurl' ),
					'color' => 'blue',
				),
				'description' => sprintf(
					'<p>%s</p>',
					esc_html__( 'The OpenSSL PHP extension is loaded and encryption features are available.', 'plugin-wp-support-thisismyurl' )
				),
				'actions'     => '',
				'test'        => 'timu_openssl_extension',
			);
		}

		return array(
			'label'       => __( 'OpenSSL extension is not available', 'plugin-wp-support-thisismyurl' ),
			'status'      => 'critical',
			'badge'       => array(
				'label' => __( 'TIMU Suite', 'plugin-wp-support-thisismyurl' ),
				'color' => 'red',
			),
			'description' => sprintf(
				'<p>%s</p>',
				esc_html__( 'The OpenSSL PHP extension is required for encryption features. Contact your hosting provider to enable it.', 'plugin-wp-support-thisismyurl' )
			),
			'actions'     => '',
			'test'        => 'timu_openssl_extension',
		);
	}

	/**
	 * Test PHP version compliance.
	 *
	 * @return array
	 */
	public static function test_php_version(): array {
		if ( version_compare( PHP_VERSION, wp_support_MIN_PHP, '>=' ) ) {
			return array(
				'label'       => sprintf(
					/* translators: %s: PHP version */
					__( 'PHP version %s meets requirements', 'plugin-wp-support-thisismyurl' ),
					PHP_VERSION
				),
				'status'      => 'good',
				'badge'       => array(
					'label' => __( 'TIMU Suite', 'plugin-wp-support-thisismyurl' ),
					'color' => 'blue',
				),
				'description' => sprintf(
					'<p>%s</p>',
					sprintf(
						/* translators: 1: current PHP version, 2: minimum required version */
						esc_html__( 'Your PHP version (%1$s) meets the minimum requirement of %2$s.', 'plugin-wp-support-thisismyurl' ),
						PHP_VERSION,
						wp_support_MIN_PHP
					)
				),
				'actions'     => '',
				'test'        => 'timu_php_version',
			);
		}

		return array(
			'label'       => __( 'PHP version below minimum requirement', 'plugin-wp-support-thisismyurl' ),
			'status'      => 'critical',
			'badge'       => array(
				'label' => __( 'TIMU Suite', 'plugin-wp-support-thisismyurl' ),
				'color' => 'red',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: 1: current PHP version, 2: minimum required version */
					esc_html__( 'Your PHP version (%1$s) is below the minimum requirement of %2$s. Contact your hosting provider to upgrade PHP.', 'plugin-wp-support-thisismyurl' ),
					PHP_VERSION,
					wp_support_MIN_PHP
				)
			),
			'actions'     => '',
			'test'        => 'timu_php_version',
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
			return array(
				'label'       => sprintf(
					/* translators: %s: WordPress version */
					__( 'WordPress version %s meets requirements', 'plugin-wp-support-thisismyurl' ),
					$wp_version
				),
				'status'      => 'good',
				'badge'       => array(
					'label' => __( 'TIMU Suite', 'plugin-wp-support-thisismyurl' ),
					'color' => 'blue',
				),
				'description' => sprintf(
					'<p>%s</p>',
					sprintf(
						/* translators: 1: current WordPress version, 2: minimum required version */
						esc_html__( 'Your WordPress version (%1$s) meets the minimum requirement of %2$s.', 'plugin-wp-support-thisismyurl' ),
						$wp_version,
						wp_support_MIN_WP
					)
				),
				'actions'     => '',
				'test'        => 'timu_wordpress_version',
			);
		}

		return array(
			'label'       => __( 'WordPress version below minimum requirement', 'plugin-wp-support-thisismyurl' ),
			'status'      => 'critical',
			'badge'       => array(
				'label' => __( 'TIMU Suite', 'plugin-wp-support-thisismyurl' ),
				'color' => 'red',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: 1: current WordPress version, 2: minimum required version */
					esc_html__( 'Your WordPress version (%1$s) is below the minimum requirement of %2$s. Please update WordPress.', 'plugin-wp-support-thisismyurl' ),
					$wp_version,
					wp_support_MIN_WP
				)
			),
			'actions'     => '',
			'test'        => 'timu_wordpress_version',
		);
	}

	/**
	 * Test vault write permissions.
	 *
	 * @return array
	 */
	public static function test_vault_permissions(): array {
		$upload_dir    = wp_upload_dir();
		$vault_dirname = get_option( 'timu_vault_dirname' );

		if ( empty( $vault_dirname ) ) {
			return array(
				'label'       => __( 'Vault not configured yet', 'plugin-wp-support-thisismyurl' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'TIMU Suite', 'plugin-wp-support-thisismyurl' ),
					'color' => 'gray',
				),
				'description' => sprintf(
					'<p>%s</p>',
					esc_html__( 'Vault directory will be created with appropriate permissions when first needed.', 'plugin-wp-support-thisismyurl' )
				),
				'actions'     => '',
				'test'        => 'timu_vault_permissions',
			);
		}

		$vault_path = $upload_dir['basedir'] . '/' . $vault_dirname;

		if ( ! file_exists( $vault_path ) ) {
			return array(
				'label'       => __( 'Vault directory does not exist', 'plugin-wp-support-thisismyurl' ),
				'status'      => 'critical',
				'badge'       => array(
					'label' => __( 'TIMU Suite', 'plugin-wp-support-thisismyurl' ),
					'color' => 'red',
				),
				'description' => sprintf(
					'<p>%s</p>',
					esc_html__( 'The vault directory was expected but does not exist. It will be recreated on next use.', 'plugin-wp-support-thisismyurl' )
				),
				'actions'     => '',
				'test'        => 'timu_vault_permissions',
			);
		}

		if ( ! wp_is_writable( $vault_path ) ) {
			return array(
				'label'       => __( 'Vault directory is not writable', 'plugin-wp-support-thisismyurl' ),
				'status'      => 'critical',
				'badge'       => array(
					'label' => __( 'TIMU Suite', 'plugin-wp-support-thisismyurl' ),
					'color' => 'red',
				),
				'description' => sprintf(
					'<p>%s</p>',
					sprintf(
						/* translators: %s: vault path */
						esc_html__( 'The vault directory exists but is not writable: %s. Check directory permissions.', 'plugin-wp-support-thisismyurl' ),
						'<code>' . esc_html( $vault_path ) . '</code>'
					)
				),
				'actions'     => '',
				'test'        => 'timu_vault_permissions',
			);
		}

		return array(
			'label'       => __( 'Vault directory has correct permissions', 'plugin-wp-support-thisismyurl' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'TIMU Suite', 'plugin-wp-support-thisismyurl' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				esc_html__( 'The vault directory is writable and ready for use.', 'plugin-wp-support-thisismyurl' )
			),
			'actions'     => '',
			'test'        => 'timu_vault_permissions',
		);
	}

	/**
	 * Test module status.
	 *
	 * @return array
	 */
	public static function test_module_status(): array {
		$modules        = TIMU_Module_Registry::get_all();
		$module_count   = count( $modules );
		$active_count   = 0;
		$hub_count      = 0;
		$spoke_count    = 0;

		foreach ( $modules as $module ) {
			if ( ! empty( $module['enabled'] ) ) {
				++$active_count;
			}
			if ( 'hub' === ( $module['type'] ?? '' ) ) {
				++$hub_count;
			} elseif ( 'spoke' === ( $module['type'] ?? '' ) ) {
				++$spoke_count;
			}
		}

		if ( 0 === $module_count ) {
			return array(
				'label'       => __( 'No modules registered', 'plugin-wp-support-thisismyurl' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'TIMU Suite', 'plugin-wp-support-thisismyurl' ),
					'color' => 'gray',
				),
				'description' => sprintf(
					'<p>%s</p>',
					esc_html__( 'No TIMU suite modules have been registered yet. This is normal if no additional modules are installed.', 'plugin-wp-support-thisismyurl' )
				),
				'actions'     => '',
				'test'        => 'timu_module_status',
			);
		}

		return array(
			'label'       => sprintf(
				/* translators: 1: number of active modules, 2: total modules */
				__( '%1$d of %2$d modules active', 'plugin-wp-support-thisismyurl' ),
				$active_count,
				$module_count
			),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'TIMU Suite', 'plugin-wp-support-thisismyurl' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: 1: hub count, 2: spoke count */
					esc_html__( 'TIMU suite has %1$d hubs and %2$d spokes registered.', 'plugin-wp-support-thisismyurl' ),
					$hub_count,
					$spoke_count
				)
			),
			'actions'     => '',
			'test'        => 'timu_module_status',
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
		$vault_dirname = get_option( 'timu_vault_dirname', __( 'Not configured', 'plugin-wp-support-thisismyurl' ) );
		$vault_path    = ! empty( $vault_dirname ) && 'Not configured' !== $vault_dirname
			? $upload_dir['basedir'] . '/' . $vault_dirname
			: __( 'Not configured', 'plugin-wp-support-thisismyurl' );

		$modules       = TIMU_Module_Registry::get_all();
		$module_list   = array();
		foreach ( $modules as $slug => $module ) {
			$status        = ! empty( $module['enabled'] ) ? __( 'Active', 'plugin-wp-support-thisismyurl' ) : __( 'Inactive', 'plugin-wp-support-thisismyurl' );
			$module_list[] = sprintf( '%s v%s (%s)', $module['name'] ?? $slug, $module['version'] ?? '?', $status );
		}

		$encryption_key_source = defined( 'TIMU_VAULT_KEY' ) && TIMU_VAULT_KEY
			? __( 'wp-config.php', 'plugin-wp-support-thisismyurl' )
			: ( get_option( 'timu_vault_encryption_key' ) ? __( 'Options table', 'plugin-wp-support-thisismyurl' ) : __( 'Not configured', 'plugin-wp-support-thisismyurl' ) );

		$info['timu-suite'] = array(
			'label'  => __( 'TIMU Suite', 'plugin-wp-support-thisismyurl' ),
			'fields' => array(
				'core_version'          => array(
					'label' => __( 'Core version', 'plugin-wp-support-thisismyurl' ),
					'value' => wp_support_VERSION,
				),
				'suite_id'              => array(
					'label' => __( 'Suite ID', 'plugin-wp-support-thisismyurl' ),
					'value' => TIMU_SUITE_ID,
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
			'vault_directory'     => array(
				'label'  => __( 'Vault Directory Status', 'plugin-wp-support-thisismyurl' ),
				'test'   => 'test_vault_directory',
				'module' => 'vault',
			),
			'encryption_config'   => array(
				'label'  => __( 'Encryption Configuration', 'plugin-wp-support-thisismyurl' ),
				'test'   => 'test_encryption_config',
				'module' => 'vault',
			),
			'openssl_extension'   => array(
				'label'  => __( 'OpenSSL Extension', 'plugin-wp-support-thisismyurl' ),
				'test'   => 'test_openssl_extension',
				'module' => 'core',
			),
			'php_version'         => array(
				'label'  => __( 'PHP Version Compliance', 'plugin-wp-support-thisismyurl' ),
				'test'   => 'test_php_version',
				'module' => 'core',
			),
			'wordpress_version'   => array(
				'label'  => __( 'WordPress Version Compliance', 'plugin-wp-support-thisismyurl' ),
				'test'   => 'test_wordpress_version',
				'module' => 'core',
			),
			'vault_permissions'   => array(
				'label'  => __( 'Vault Write Permissions', 'plugin-wp-support-thisismyurl' ),
				'test'   => 'test_vault_permissions',
				'module' => 'vault',
			),
			'module_status'       => array(
				'label'  => __( 'Module Status', 'plugin-wp-support-thisismyurl' ),
				'test'   => 'test_module_status',
				'module' => 'core',
			),
		);

		$results = array();
		$critical_count = 0;
		$warning_count = 0;
		$good_count = 0;

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
			'score'      => $score,
			'status'     => $status,
			'results'    => $results,
			'counts'     => array(
				'good'    => $good_count,
				'warning' => $warning_count,
				'critical' => $critical_count,
				'total'   => $total,
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
		$catalog = \TIMU\CoreSupport\TIMU_Module_Registry::get_catalog_with_status();
		$dependents_data = array();

		foreach ( $catalog as $mod ) {
			$mod_slug = $mod['slug'] ?? '';
			if ( empty( $mod['dependencies'] ) ) {
				continue;
			}

			// Check if current module is a dependency.
			if ( in_array( $module_id, (array) $mod['dependencies'], true ) ) {
				$dep_health = self::get_health_check_results( $mod_slug );
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
}
