<?php
/**
 * Feature: CSS Class Cleanup (Post/Nav/Body)
 *
 * Strip excessive WordPress-generated CSS classes from posts, navigation
 * menus, and body tags to reduce HTML bloat.
 *
 * @package WPShadow\Features
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_CSS_Class_Cleanup
 *
 * Filters CSS classes for cleaner HTML.
 */
final class WPSHADOW_Feature_CSS_Class_Cleanup extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'css-class-cleanup',
				'name'               => __( 'CSS Class Cleanup (Post/Nav/Body)', 'wpshadow' ),
				'description'        => __( 'Clean up your HTML - remove unnecessary CSS classes and make your site code cleaner.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'cleanup',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-editor-removeformatting',
				'category'           => 'performance',
				'priority'           => 20,
				'sub_features'       => array(
					'clean_post_classes'   => __( 'Clean Post Classes', 'wpshadow' ),
					'clean_nav_classes'    => __( 'Clean Navigation Classes', 'wpshadow' ),
					'remove_nav_ids'       => __( 'Remove Navigation IDs', 'wpshadow' ),
					'clean_body_classes'   => __( 'Clean Body Classes', 'wpshadow' ),
					'remove_block_classes' => __( 'Remove Block-Related Classes', 'wpshadow' ),
				),
			)
		);

		$this->seed_default_sub_feature_options();
		$this->log_activity( 'feature_initialized', 'CSS Class Cleanup feature initialized', 'info' );
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
	 * Only exposes Site Health; child features perform cleanup.
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
	 * @param array $tests Array of Site Health tests.
	 * @return array Modified tests array.
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['css_class_cleanup'] = array(
			'label' => __( 'CSS Class Cleanup', 'wpshadow' ),
			'test'  => array( $this, 'test_css_class_cleanup' ),
		);

		return $tests;
	}

	/**
	 * Site Health test for CSS class cleanup.
	 *
	 * @return array Test result.
	 */
	public function test_css_class_cleanup(): array {
		$enabled_features = 0;
		
		if ( get_option( 'wpshadow_css-class-cleanup_clean_post_classes', true ) ) {
			$enabled_features++;
		}
		if ( get_option( 'wpshadow_css-class-cleanup_clean_nav_classes', true ) ) {
			$enabled_features++;
		}
		if ( get_option( 'wpshadow_css-class-cleanup_remove_nav_ids', true ) ) {
			$enabled_features++;
		}
		if ( get_option( 'wpshadow_css-class-cleanup_clean_body_classes', true ) ) {
			$enabled_features++;
		}
		if ( get_option( 'wpshadow_css-class-cleanup_remove_block_classes', true ) ) {
			$enabled_features++;
		}

		$status = $enabled_features >= 3 ? 'good' : 'recommended';
		$label  = $enabled_features >= 3 ?
			__( 'CSS class cleanup is active', 'wpshadow' ) :
			__( 'CSS class cleanup could be improved', 'wpshadow' );

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
					__( '%d CSS class cleanup features are enabled, reducing HTML bloat.', 'wpshadow' ),
					$enabled_features
				)
			),
			'actions'     => '',
			'test'        => 'css_class_cleanup',
		);
	}

	/**
	 * Seed default sub-feature options when absent.
	 *
	 * @return void
	 */
	private function seed_default_sub_feature_options(): void {
		$defaults = array(
			'clean_post_classes'   => true,
			'clean_nav_classes'    => true,
			'remove_nav_ids'       => true,
			'clean_body_classes'   => true,
			'remove_block_classes' => true,
		);

		foreach ( $defaults as $key => $default_value ) {
			$option_name   = 'wpshadow_css-class-cleanup_' . $key;
			$current_value = get_option( $option_name, null );

			if ( null === $current_value ) {
				update_option( $option_name, $default_value, false );
			}
		}
	}
}
