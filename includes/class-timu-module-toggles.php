<?php
/**
 * Module Toggles - Feature flags for module enablement without deactivation.
 *
 * Allows enabling/disabling module features independently of plugin activation.
 *
 * @package TIMU_CORE_SUPPORT
 */

declare(strict_types=1);

namespace TIMU\CoreSupport;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Module Toggles Class
 *
 * Manages feature flags for each module with dependency graph validation.
 */
class TIMU_Module_Toggles {

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
		add_filter( 'timu_module_enabled', array( __CLASS__, 'check_module_enabled' ), 10, 2 );
	}

	/**
	 * Register settings for module toggles.
	 *
	 * @return void
	 */
	public static function register_settings(): void {
		register_setting(
			'timu_core_modules',
			'timu_module_toggles',
			array(
				'type'              => 'object',
				'sanitize_callback' => array( __CLASS__, 'sanitize_toggles' ),
				'show_in_rest'      => false,
			)
		);

		add_settings_section(
			'timu_module_toggles_section',
			__( 'Module Features', 'core-support-thisismyurl' ),
			array( __CLASS__, 'render_section' ),
			'timu_core_modules'
		);

		// Add fields for each module.
		foreach ( self::MODULE_DEPENDENCIES as $module => $dependencies ) {
			add_settings_field(
				'timu_toggle_' . $module,
				esc_html( self::get_module_label( $module ) ),
				array( __CLASS__, 'render_toggle_field' ),
				'timu_core_modules',
				'timu_module_toggles_section',
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
		<p><?php esc_html_e( 'Enable or disable module features. Disabling a feature will not deactivate the plugin.', 'core-support-thisismyurl' ); ?></p>
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
			echo wp_kses_post( sprintf( '<em>%s</em>', __( 'Plugin not installed', 'core-support-thisismyurl' ) ) );
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
			echo wp_kses_post(
				sprintf(
					/* translators: Module names */
					__( 'Requires: %s', 'core-support-thisismyurl' ),
					esc_html( implode( ', ', array_map( array( __CLASS__, 'get_module_label' ), $deps ) ) )
				)
			);
			return;
		}

		$field_name = 'timu_module_toggles[' . esc_attr( $module ) . ']';
		?>
		<input type="checkbox" name="<?php echo esc_attr( $field_name ); ?>" value="1" <?php checked( $enabled, 1 ); ?> />
		<label>
			<?php esc_html_e( 'Enabled', 'core-support-thisismyurl' ); ?>
		</label>
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
		$toggles = get_option( 'timu_module_toggles', array() );

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
			'media-support-thisismyurl' => __( 'Media Support', 'core-support-thisismyurl' ),
			'image-support-thisismyurl' => __( 'Image Support', 'core-support-thisismyurl' ),
			'vault-support-thisismyurl' => __( 'Vault Support', 'core-support-thisismyurl' ),
		);

		return $labels[ $module ] ?? ucwords( str_replace( '-', ' ', $module ) );
	}
}
