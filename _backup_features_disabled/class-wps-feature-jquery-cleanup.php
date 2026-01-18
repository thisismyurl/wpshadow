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
				'name'               => __( 'jQuery Migrate Removal', 'wpshadow' ),
				'description'        => __( 'Speed up sites that don\'t need old jQuery code - we remove the compatibility script.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-editor-code',
				'category'           => 'performance',
				'priority'           => 20,
				'sub_features'       => array(
					'remove_migrate_frontend' => __( 'Remove jQuery Migrate on Frontend', 'wpshadow' ),
					'keep_admin'             => __( 'Keep jQuery Migrate in Admin', 'wpshadow' ),
					'log_removals'           => __( 'Log jQuery Migrate Removals', 'wpshadow' ),
				),
			)
		);

		$this->seed_default_sub_feature_options();
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

		// Add Site Health tests.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array $tests Site Health tests.
	 * @return array Modified tests array.
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['jquery_cleanup'] = array(
			'label' => __( 'jQuery Migrate Cleanup', 'wpshadow' ),
			'test'  => array( $this, 'test_jquery_cleanup' ),
		);

		return $tests;
	}

	/**
	 * Site Health test for jQuery cleanup.
	 *
	 * @return array Test result.
	 */
	public function test_jquery_cleanup(): array {
		$enabled_features = 0;

		if ( get_option( 'wpshadow_jquery-cleanup_remove_migrate_frontend', true ) ) {
			$enabled_features++;
		}
		if ( get_option( 'wpshadow_jquery-cleanup_keep_admin', true ) ) {
			$enabled_features++;
		}
		if ( get_option( 'wpshadow_jquery-cleanup_log_removals', false ) ) {
			$enabled_features++;
		}

		$status = $enabled_features >= 2 ? 'good' : 'recommended';
		$label  = $enabled_features >= 2 ?
			__( 'jQuery Migrate cleanup is active', 'wpshadow' ) :
			__( 'jQuery Migrate cleanup could be improved', 'wpshadow' );

		return array(
			'label'       => $label,
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Performance', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: %d: number of enabled cleanup features */
					__( '%d jQuery cleanup options are enabled.', 'wpshadow' ),
					$enabled_features
				)
			),
			'actions'     => '',
			'test'        => 'jquery_cleanup',
		);
	}

	/**
	 * Seed default sub-feature options when absent.
	 *
	 * @return void
	 */
	private function seed_default_sub_feature_options(): void {
		$defaults = array(
			'remove_migrate_frontend' => true,
			'keep_admin'             => true,
			'log_removals'           => false,
		);

		foreach ( $defaults as $key => $default_value ) {
			$option_name   = 'wpshadow_jquery-cleanup_' . $key;
			$current_value = get_option( $option_name, null );

			if ( null === $current_value ) {
				update_option( $option_name, $default_value, false );
			}
		}
	}
}
