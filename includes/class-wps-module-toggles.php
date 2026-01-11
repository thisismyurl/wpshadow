<?php
/**
 * Module Toggles - Feature flags for module enablement without deactivation.
 *
 * Allows enabling/disabling module features independently of plugin activation.
 *
 * @package wp_support_SUPPORT
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
class WPS_Module_Toggles {

	/**
	 * Module dependency graph.
	 * key: module slug, value: required parent modules.
	 */
	private const MODULE_DEPENDENCIES = array(
		'media-support-thisismyurl' => array(),
		'image-support-thisismyurl' => array( 'media-support-thisismyurl' ),
		'vault-support-thisismyurl' => array( 'media-support-thisismyurl' ),
	);

	/**
	 * Initialize toggles.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_filter( 'WPS_module_enabled', array( __CLASS__, 'check_module_enabled' ), 10, 2 );
	}

	/**
	 * Register settings for module toggles.
	 *
	 * @return void
	 */
	public static function register_settings(): void {
		register_setting(
			'wp_support_modules',
			'WPS_module_toggles',
			array(
				'type'              => 'object',
				'sanitize_callback' => array( __CLASS__, 'sanitize_toggles' ),
				'show_in_rest'      => false,
			)
		);

		add_settings_section(
			'WPS_module_toggles_section',
			__( 'Module Features', 'plugin-wp-support-thisismyurl' ),
			array( __CLASS__, 'render_section' ),
			'wp_support_modules'
		);

		// Add fields for each module.
		foreach ( self::MODULE_DEPENDENCIES as $module => $dependencies ) {
			add_settings_field(
				'WPS_toggle_' . $module,
				esc_html( self::get_module_label( $module ) ),
				array( __CLASS__, 'render_toggle_field' ),
				'wp_support_modules',
				'WPS_module_toggles_section',
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
		<p><?php esc_html_e( 'Enable or disable module features. Disabling a feature will not deactivate the plugin.', 'plugin-wp-support-thisismyurl' ); ?></p>
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
			echo wp_kses_post( sprintf( '<em aria-label="%s">%s</em>', esc_attr__( 'Plugin not installed', 'plugin-wp-support-thisismyurl' ), esc_html__( 'Plugin not installed', 'plugin-wp-support-thisismyurl' ) ) );
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
					esc_attr__( 'Requires dependencies', 'plugin-wp-support-thisismyurl' ),
					/* translators: Module names */
					sprintf( __( 'Requires: %s', 'plugin-wp-support-thisismyurl' ), esc_html( $dep_labels ) )
				)
			);
			return;
		}

		$field_name = 'WPS_module_toggles[' . esc_attr( $module ) . ']';
		$field_id   = 'WPS_toggle_' . esc_attr( $module );
		?>
		<fieldset>
			<legend class="screen-reader-text"><?php echo esc_html( self::get_module_label( $module ) ); ?></legend>
			<input type="checkbox" name="<?php echo esc_attr( $field_name ); ?>" id="<?php echo esc_attr( $field_id ); ?>" value="1" <?php checked( $enabled, 1 ); ?> aria-describedby="<?php echo esc_attr( $field_id . '_desc' ); ?>" />
			<label for="<?php echo esc_attr( $field_id ); ?>">
				<?php esc_html_e( 'Enabled', 'plugin-wp-support-thisismyurl' ); ?>
			</label>
			<p class="description" id="<?php echo esc_attr( $field_id . '_desc' ); ?>">
				<?php esc_html_e( 'Disabling this will turn off module features without deactivating the plugin.', 'plugin-wp-support-thisismyurl' ); ?>
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
		$toggles = get_option( 'WPS_module_toggles', array() );

		if ( ! is_array( $toggles ) ) {
			$toggles = array();
		}

		// Fill missing toggles with defaults (all enabled if plugin is installed).
		foreach ( array_keys( self::MODULE_DEPENDENCIES ) as $module ) {
			if ( ! isset( $toggles[ $module ] ) ) {
				$toggles[ $module ] = self::is_plugin_installed( $module ) ? 1 : 0;
			}
		}

		return $toggles;
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
			'media-support-thisismyurl' => __( 'Media', 'plugin-wp-support-thisismyurl' ),
			'image-support-thisismyurl' => __( 'Image', 'plugin-wp-support-thisismyurl' ),
			'vault-support-thisismyurl' => __( 'Vault', 'plugin-wp-support-thisismyurl' ),
		);

		return $labels[ $module ] ?? ucwords( str_replace( '-', ' ', $module ) );
	}
}

