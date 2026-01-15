<?php
/**
 * Feature: jQuery Migrate Removal
 *
 * Remove jQuery Migrate script which provides backward compatibility for very old jQuery code.
 * Modern WordPress sites don't need this script, and removing it saves bandwidth and processing time.
 *
 * @package WPS\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

/**
 * WPSHADOW_Feature_jQuery_Cleanup
 *
 * Remove jQuery Migrate script for modern sites.
 */
final class WPSHADOW_Feature_jQuery_Cleanup extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'jquery-cleanup',
				'name'               => __( 'jQuery Migrate Removal', 'plugin-wpshadow' ),
				'description'        => __( 'Remove a script for old code that modern sites don\'t need', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'widget_label'       => __( 'Performance & Security', 'plugin-wpshadow' ),
				'widget_description' => __( 'Remove bloat and unnecessary scripts that impact security and page speed', 'plugin-wpshadow' ),
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

		add_action( 'wp_default_scripts', array( $this, 'remove_jquery_migrate' ) );
	}

	/**
	 * Remove jQuery Migrate dependency from jQuery core.
	 *
	 * @param \WP_Scripts $scripts The WP_Scripts object.
	 * @return void
	 */
	public function remove_jquery_migrate( \WP_Scripts $scripts ): void {
		if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
			$script = $scripts->registered['jquery'];
			if ( $script->deps ) {
				// Remove jquery-migrate from dependencies.
				$script->deps = array_diff( (array) $script->deps, array( 'jquery-migrate' ) );
			}
		}
	}
}
