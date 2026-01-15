<?php
/**
 * Module Toggles - Feature flags for module enablement without deactivation.
 *
 * Allows enabling/disabling module features independently of plugin activation.
 *
 * @package wpshadow_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Module Toggles Class
 *
 * Manages feature flags for each module with dependency graph validation.
 */
class WPSHADOW_Module_Toggles {

	/**
	 * Module dependency graph.
	 * key: module slug, value: required parent modules.
	 */
	private const MODULE_DEPENDENCIES = array(
		'media-wpshadow' => array(),
		'image-wpshadow' => array( 'media-wpshadow' ),
		'vault-wpshadow' => array( 'media-wpshadow' ),
	);

	/**
	 * Initialize toggles.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_filter( 'wpshadow_module_enabled', array( __CLASS__, 'check_module_enabled' ), 10, 2 );
	}

	/**
	 * Register settings for module toggles.
	 *
	 * @return void
	 */
	public static function register_settings(): void {
		register_setting(
			'wpshadow_modules',
			'wpshadow_module_toggles',
			array(
				'type'              => 'object',
				'sanitize_callback' => array( __CLASS__, 'sanitize_toggles' ),
				'show_in_rest'      => false,
			)
		);

		add_settings_section(
			'wpshadow_module_toggles_section',
			__( 'Module Features', 'plugin-wpshadow' ),
			array( __CLASS__, 'render_section' ),
			'wpshadow_modules'
		);

		// Add fields for each module.
		foreach ( self::MODULE_DEPENDENCIES as $module => $dependencies ) {
			add_settings_field(
				'wpshadow_toggle_' . $module,
				esc_html( self::get_module_label( $module ) ),
				array( __CLASS__, 'render_toggle_field' ),
				'wpshadow_modules',
				'wpshadow_module_toggles_section',
				array( 'module' => $module )
			);
		}
	}

	/**
	 * Render settings section.
	 *
	 * @return void
	 */
	public static function render_section(): void {
		?>
		<p><?php esc_html_e( 'Enable or disable module features. Disabling a feature will not deactivate the plugin.', 'plugin-wpshadow' ); ?></p>
		<?php
	}

	/**
	 * Render toggle field.
	 *
	 * @param array $args Field arguments.
	 * @return void
	 */
	public static function render_toggle_field( array $args ): void {
		$module    = $args['module'] ?? '';
		$toggles   = self::get_toggles();
		$enabled   = $toggles[ $module ] ?? false;
		$deps      = self::MODULE_DEPENDENCIES[ $module ] ?? array();
		$installed = self::is_plugin_installed( $module );

		if ( ! $installed ) {
			echo wp_kses_post( sprintf( '<em aria-label="%s">%s</em>', esc_attr__( 'Plugin not installed', 'plugin-wpshadow' ), esc_html__( 'Plugin not installed', 'plugin-wpshadow' ) ) );
			return;
		}

		// Check if dependencies are satisfied.
		$deps_satisfied = true;
		foreach ( $deps as $dep ) {
			if ( ! ( $toggles[ $dep ] ?? false ) ) {
				$deps_satisfied = false;
				break;
			}
		}

		if ( ! $deps_satisfied ) {
			$dep_labels = implode( ', ', array_map( array( __CLASS__, 'get_module_label' ), $deps ) );
			echo wp_kses_post(
				sprintf(
					'<em aria-label="%s" role="status">%s</em>',
					esc_attr__( 'Requires dependencies', 'plugin-wpshadow' ),
					/* translators: Module names */
					sprintf( __( 'Requires: %s', 'plugin-wpshadow' ), esc_html( $dep_labels ) )
				)
			);
			return;
		}

		$field_name = 'wpshadow_module_toggles[' . esc_attr( $module ) . ']';
		$field_id   = 'wpshadow_toggle_' . esc_attr( $module );
		?>
		<fieldset>
			<legend class="screen-reader-text"><?php echo esc_html( self::get_module_label( $module ) ); ?></legend>
			<input type="checkbox" name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $field_id ); ?>" value="1" <?php checked( $enabled, 1 ); ?> aria-describedby="<?php echo esc_attr( $field_id . '_desc' ); ?>" />
			<label for="<?php echo esc_attr( $field_id ); ?>">
				<?php esc_html_e( 'Enabled', 'plugin-wpshadow' ); ?>
			</label>
			<p class="description" id="<?php echo esc_attr( $field_id . '_desc' ); ?>">
				<?php esc_html_e( 'Disabling this will turn off module features without deactivating the plugin.', 'plugin-wpshadow' ); ?>
			</p>
		</fieldset>
		<?php
	}

	/**
	 * Sanitize toggle values.
	 *
	 * @param mixed $input Raw input.
	 * @return array Sanitized toggles.
	 */
	public static function sanitize_toggles( $input ): array {
		if ( ! is_array( $input ) ) {
			return array();
		}

		$sanitized = array();

		foreach ( array_keys( self::MODULE_DEPENDENCIES ) as $module ) {
			$sanitized[ $module ] = ! empty( $input[ $module ] ) ? 1 : 0;
		}

		// Validate dependencies.
		$sanitized = self::validate_dependencies( $sanitized );

		return $sanitized;
	}

	/**
	 * Validate dependencies and disable child modules if parent is disabled.
	 *
	 * @param array $toggles Module toggles.
	 * @return array Validated toggles.
	 */
	private static function validate_dependencies( array $toggles ): array {
		foreach ( array_keys( self::MODULE_DEPENDENCIES ) as $module ) {
			$deps = self::MODULE_DEPENDENCIES[ $module ] ?? array();

			// Check if all dependencies are enabled.
			foreach ( $deps as $dep ) {
				if ( empty( $toggles[ $dep ] ) ) {
					// Disable this module if dependency is disabled.
					$toggles[ $module ] = 0;
					break;
				}
			}
		}

		return $toggles;
	}

	/**
	 * Check if a module is enabled.
	 *
	 * @param bool   $enabled Module enabled status (default).
	 * @param string $module  Module slug.
	 * @return bool True if module is enabled.
	 */
	public static function check_module_enabled( bool $enabled, string $module ): bool {
		$toggles = self::get_toggles();
		return ! empty( $toggles[ $module ] );
	}

	/**
	 * Get all module toggles.
	 *
	 * @return array Module toggles.
	 */
	public static function get_toggles(): array {
		$toggles = get_option( 'wpshadow_module_toggles', array() );

		if ( ! is_array( $toggles ) ) {
			$toggles = array();
		}

		// Fill missing toggles with defaults (all enabled if plugin is installed).
		// Bundled modules ship inside Core, so default to enabled when no toggle is stored.
		foreach ( array_keys( self::MODULE_DEPENDENCIES ) as $module ) {
			if ( ! isset( $toggles[ $module ] ) ) {
				$toggles[ $module ] = 1;
			}
		}

		return $toggles;
	}

	/**
	 * Get all modules that depend on the given module.
	 *
	 * @param string $parent_module Parent module slug.
	 * @return array Array of dependent module slugs.
	 */
	public static function get_dependents( string $parent_module ): array {
		$dependents = array();
		foreach ( self::MODULE_DEPENDENCIES as $module => $deps ) {
			if ( in_array( $parent_module, $deps, true ) ) {
				$dependents[] = $module;
			}
		}
		return $dependents;
	}

	/**
	 * Cascade deactivate dependents and remember for restoration.
	 *
	 * @param string $parent_module Parent module being deactivated.
	 * @return array Array of modules that were auto-deactivated.
	 */
	public static function cascade_deactivate( string $parent_module ): array {
		$dependents  = self::get_dependents( $parent_module );
		$toggles     = self::get_toggles();
		$deactivated = array();

		foreach ( $dependents as $dep ) {
			if ( ! empty( $toggles[ $dep ] ) ) {
				$toggles[ $dep ] = 0;
				$deactivated[]   = $dep;
			}
		}

		if ( ! empty( $deactivated ) ) {
			update_option( 'wpshadow_module_toggles', $toggles, false );
			self::remember_auto_deactivated( $parent_module, $deactivated );
		}

		return $deactivated;
	}

	/**
	 * Remember which modules were auto-deactivated for later restoration.
	 *
	 * @param string $parent_module Parent module slug.
	 * @param array  $deactivated Array of deactivated module slugs.
	 * @return void
	 */
	private static function remember_auto_deactivated( string $parent_module, array $deactivated ): void {
		$memory = get_option( 'wpshadow_auto_deactivated', array() );
		if ( ! is_array( $memory ) ) {
			$memory = array();
		}
		$memory[ $parent_module ] = $deactivated;
		update_option( 'wpshadow_auto_deactivated', $memory, false );
	}

	/**
	 * Get remembered auto-deactivated modules for a parent.
	 *
	 * @param string $parent_module Parent module slug.
	 * @return array Array of module slugs that were auto-deactivated.
	 */
	public static function get_remembered_deactivated( string $parent_module ): array {
		$memory = get_option( 'wpshadow_auto_deactivated', array() );
		if ( ! is_array( $memory ) ) {
			return array();
		}
		return $memory[ $parent_module ] ?? array();
	}

	/**
	 * Clear remembered auto-deactivated modules for a parent.
	 *
	 * @param string $parent_module Parent module slug.
	 * @return void
	 */
	public static function clear_remembered( string $parent_module ): void {
		$memory = get_option( 'wpshadow_auto_deactivated', array() );
		if ( ! is_array( $memory ) ) {
			return;
		}
		unset( $memory[ $parent_module ] );
		update_option( 'wpshadow_auto_deactivated', $memory, false );
	}

	/**
	 * Check if plugin is installed.
	 *
	 * @param string $module Module slug.
	 * @return bool True if installed.
	 */
	private static function is_plugin_installed( string $module ): bool {
		$plugins = get_plugins();
		foreach ( $plugins as $plugin_file => $plugin_data ) {
			if ( strpos( $plugin_file, $module . '/' ) === 0 ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Get module label for display.
	 *
	 * @param string $module Module slug.
	 * @return string Module label.
	 */
	private static function get_module_label( string $module ): string {
		$labels = array(
			'media-wpshadow' => __( 'Media', 'plugin-wpshadow' ),
			'image-wpshadow' => __( 'Image', 'plugin-wpshadow' ),
			'vault-wpshadow' => __( 'Vault', 'plugin-wpshadow' ),
		);

		return $labels[ $module ] ?? ucwords( str_replace( '-', ' ', $module ) );
	}
}

