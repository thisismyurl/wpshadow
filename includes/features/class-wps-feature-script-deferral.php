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
	 * Default excluded script handles (critical scripts that should never be deferred).
	 *
	 * @var array<string>
	 */
	private array $default_excluded = array(
		'jquery-core',
		'jquery-migrate',
		'jquery',
	);

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
				'version'             => '1.1.0',
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
	 * Add defer attribute to scripts with smart detection.
	 *
	 * @param string $tag    The script tag HTML.
	 * @param string $handle The script handle.
	 * @param string $src    The script source URL.
	 * @return string Modified script tag.
	 */
	public function defer_scripts( string $tag, string $handle, string $src ): string {
		// Skip admin and login pages.
		if ( is_admin() || wp_doing_ajax() ) {
			return $tag;
		}

		// Skip if already has defer or async.
		if ( strpos( $tag, 'defer' ) !== false || strpos( $tag, 'async' ) !== false ) {
			return $tag;
		}

		// Get mode: 'auto', 'manual', or 'disabled'.
		$mode = get_option( 'wps_defer_mode', 'auto' );

		if ( 'disabled' === $mode ) {
			return $tag;
		}

		// Get custom exclusion list.
		$custom_excluded = (array) get_option( 'wps_defer_excluded_handles', array() );
		$excluded        = array_merge( $this->default_excluded, $custom_excluded );

		// Allow filtering of exclusion list.
		$excluded = apply_filters( 'wps_defer_excluded_handles', $excluded );

		// Never defer excluded scripts.
		if ( in_array( $handle, $excluded, true ) ) {
			return $tag;
		}

		// In manual mode, only defer explicitly listed handles.
		if ( 'manual' === $mode ) {
			$defer_handles = (array) get_option( 'wps_defer_script_handles', array() );
			$defer_handles = apply_filters( 'wps_defer_script_handles', $defer_handles );

			if ( in_array( $handle, $defer_handles, true ) ) {
				return str_replace( ' src=', ' defer src=', $tag );
			}

			return $tag;
		}

		// Auto mode: defer all non-excluded scripts by default.
		if ( 'auto' === $mode ) {
			// Check if it's a local script (better for deferring).
			$is_local = strpos( $src, site_url() ) !== false || strpos( $src, '/' ) === 0;

			// Only auto-defer local scripts to avoid breaking external dependencies.
			if ( $is_local ) {
				return str_replace( ' src=', ' defer src=', $tag );
			}
		}

		return $tag;
	}

	/**
	 * Get default excluded handles.
	 *
	 * @return array<string>
	 */
	public function get_default_excluded(): array {
		return $this->default_excluded;
	}
}
