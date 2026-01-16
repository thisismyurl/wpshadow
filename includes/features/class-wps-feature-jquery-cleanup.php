<?php
/**
 * Feature: jQuery Migrate Removal
 *
 * Remove jQuery Migrate script which provides backward compatibility for very old jQuery code.
 * Modern WordPress sites don't need this script, and removing it saves bandwidth and processing time.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

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
				'description'        => __( 'Removes the jQuery Migrate compatibility script on the front end for modern sites that do not rely on legacy jQuery functions, reducing one extra download and script execution. Keeps it available in admin and can be re-enabled if a plugin truly needs it. Helps improve performance scores and lowers conflict risk from outdated code paths.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'widget_label'       => __( 'Performance & Security', 'plugin-wpshadow' ),
				'widget_description' => __( 'Remove bloat and unnecessary scripts that impact security and page speed', 'plugin-wpshadow' ),
			)
		);
		
		if ( method_exists( $this, 'register_sub_features' ) ) {
			$this->register_sub_features(
				array(
					'remove_migrate_frontend' => __( 'Remove jQuery Migrate on Frontend', 'plugin-wpshadow' ),
					'keep_admin'             => __( 'Keep jQuery Migrate in Admin', 'plugin-wpshadow' ),
					'log_removals'           => __( 'Log jQuery Migrate Removals', 'plugin-wpshadow' ),
				)
			);
			if ( method_exists( $this, 'set_default_sub_features' ) ) {
				$this->set_default_sub_features(
					array(
						'remove_migrate_frontend' => true,
						'keep_admin'             => true,
						'log_removals'           => false,
					)
				);
			}
		}
		
		$this->log_activity( 'feature_initialized', 'jQuery Cleanup feature initialized', 'info' );
	}

	/**
	 * Indicate this feature has a details page.
	 *
	 * @return bool
	 */
	public function has_details_page(): bool {
		return true;
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
		
		// Add Site Health tests.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
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
