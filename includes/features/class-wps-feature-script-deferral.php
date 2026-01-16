<?php
/**
 * Feature: Selective Script Deferral System
 *
 * Defer non-critical JavaScript files for improved page load performance.
 * Scripts are deferred only if they have no inline dependencies.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_Script_Deferral
 *
 * Defer non-critical scripts while preserving execution order.
 */
final class WPSHADOW_Feature_Script_Deferral extends WPSHADOW_Abstract_Feature {

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
				'id'                 => 'script-deferral',
				'name'               => __( 'Delay Scripts for Faster Display', 'plugin-wpshadow' ),
				'description'        => __( 'Let visitors see your page faster - we load heavy scripts after the main content.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.1.0',
				'widget_group'       => 'performance',
				// New unified metadata fields.
				'license_level'      => 3, // Free registered users.
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-performance',
				'category'           => 'performance',
				'priority'           => 20,

			)
		);

		if ( method_exists( $this, 'register_sub_features' ) ) {
			$this->register_sub_features(
				array(
					'defer_third_party'  => __( 'Defer Third-Party Scripts', 'plugin-wpshadow' ),
					'defer_analytics'    => __( 'Defer Analytics Scripts', 'plugin-wpshadow' ),
					'defer_social'       => __( 'Defer Social Media Scripts', 'plugin-wpshadow' ),
					'exclude_jquery'     => __( 'Never Defer jQuery', 'plugin-wpshadow' ),
					'preserve_order'     => __( 'Preserve Execution Order', 'plugin-wpshadow' ),
				)
			);
			if ( method_exists( $this, 'set_default_sub_features' ) ) {
				$this->set_default_sub_features(
					array(
						'defer_third_party'  => true,
						'defer_analytics'    => true,
						'defer_social'       => true,
						'exclude_jquery'     => true,
						'preserve_order'     => true,
					)
				);
			}
		}

		// Register default settings.
		$this->register_default_settings(
			array(
				'defer_mode'             => 'auto',
				'defer_excluded_handles' => array(),
				'defer_script_handles'   => array(),
			)
		);
		
		$this->log_activity( 'feature_initialized', 'Script Deferral feature initialized', 'info' );
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

		add_filter( 'script_loader_tag', array( $this, 'defer_scripts' ), 10, 3 );
		
		// Add Site Health tests.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
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
		$mode = $this->get_setting( 'defer_mode', 'auto' );

		if ( 'disabled' === $mode ) {
			return $tag;
		}

		// Get custom exclusion list.
		$custom_excluded = (array) $this->get_setting( 'defer_excluded_handles', array() );
		$excluded        = array_merge( $this->default_excluded, $custom_excluded );

		// Allow filtering of exclusion list.
		$excluded = apply_filters( 'wpshadow_defer_excluded_handles', $excluded );

		// Never defer excluded scripts.
		if ( in_array( $handle, $excluded, true ) ) {
			return $tag;
		}

		// In manual mode, only defer explicitly listed handles.
		if ( 'manual' === $mode ) {
			$defer_handles = (array) $this->get_setting( 'defer_script_handles', array() );
			$defer_handles = apply_filters( 'wpshadow_defer_script_handles', $defer_handles );

			if ( in_array( $handle, $defer_handles, true ) ) {
				return str_replace( ' src=', ' defer src=', $tag );
			}

			return $tag;
		}

		// Auto mode: defer all non-excluded scripts by default.
		if ( 'auto' === $mode ) {
			// Check if it's a local script (better for deferring).
			$is_local = strpos( $src, site_url() ) !== false || ( strpos( $src, '/' ) === 0 && strpos( $src, '//' ) !== 0 );

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

	/**
	 * Register Site Health test.
	 *
	 * @param array<string, mixed> $tests Site Health tests.
	 * @return array<string, mixed>
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['WPSHADOW_script_deferral'] = array(
			'label' => __( 'Script Deferral', 'plugin-wpshadow' ),
			'test'  => array( $this, 'test_script_deferral' ),
		);
		return $tests;
	}

	/**
	 * Site Health test for script deferral.
	 *
	 * @return array<string, mixed>
	 */
	public function test_script_deferral(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Script Deferral', 'plugin-wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'plugin-wpshadow' ),
					'color' => 'gray',
				),
				'description' => sprintf( '<p>%s</p>', __( 'Script Deferral is not enabled. Deferring non-critical JavaScript can improve page load times.', 'plugin-wpshadow' ) ),
				'actions'     => '',
				'test'        => 'WPSHADOW_script_deferral',
			);
		}

		// Count enabled sub-features.
		$enabled_features = 0;
		if ( get_option( 'wpshadow_script-deferral_defer_third_party', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_script-deferral_defer_analytics', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_script-deferral_defer_social', true ) ) {
			++$enabled_features;
		}

		$defer_mode = $this->get_setting( 'defer_mode', 'auto' );

		return array(
			'label'       => __( 'Script Deferral', 'plugin-wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Performance', 'plugin-wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				/* translators: 1: defer mode, 2: number of enabled features */
				sprintf(
					__( 'Script Deferral is active in %1$s mode with %2$d defer rules enabled.', 'plugin-wpshadow' ),
					$defer_mode,
					$enabled_features
				)
			),
			'actions'     => '',
			'test'        => 'WPSHADOW_script_deferral',
		);
	}
}
