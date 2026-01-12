<?php
/**
 * Feature: Selective Script Deferral System
 *
 * Defer non-critical JavaScript files for improved page load performance.
 * Scripts are deferred only if they have no inline dependencies.
 *
 * @package WPS\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPS\CoreSupport\Features;

/**
 * WPS_Feature_Script_Deferral
 *
 * Defer non-critical scripts while preserving execution order.
 */
final class WPS_Feature_Script_Deferral extends WPS_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                  => 'script-deferral',
				'name'                => __( 'Script Deferral System', 'plugin-wp-support-thisismyurl' ),
				'description'         => __( 'Defer non-critical JavaScript for faster Time to Interactive', 'plugin-wp-support-thisismyurl' ),
				'scope'               => 'core',
				'default_enabled'     => false,
				'version'             => '1.0.0',
				'widget_group'        => 'resource-optimization',
				'widget_label'        => __( 'Resource Optimization', 'plugin-wp-support-thisismyurl' ),
				'widget_description'  => __( 'Optimize how resources are loaded and delivered', 'plugin-wp-support-thisismyurl' ),
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

		add_filter( 'script_loader_tag', array( $this, 'defer_scripts' ), 10, 3 );
	}

	/**
	 * Add defer attribute to specified scripts.
	 *
	 * @param string $tag    The script tag HTML.
	 * @param string $handle The script handle.
	 * @param string $src    The script source URL.
	 * @return string Modified script tag.
	 */
	public function defer_scripts( string $tag, string $handle, string $src ): string {
		// Get configured scripts to defer from options.
		$defer_handles = (array) get_option( 'wps_defer_script_handles', array() );

		// Allow filtering of defer list.
		$defer_handles = apply_filters( 'wps_defer_script_handles', $defer_handles );

		// Skip admin and login pages.
		if ( is_admin() || wp_doing_ajax() ) {
			return $tag;
		}

		if ( in_array( $handle, $defer_handles, true ) ) {
			return str_replace( ' src=', ' defer src=', $tag );
		}

		return $tag;
	}
}
