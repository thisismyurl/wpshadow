<?php
/**
 * Feature: DNS Prefetch & Resource Hints Management
 *
 * Control which DNS prefetch and resource hint links are added to <head>.
 * Remove unnecessary hints and add strategic hints for external resources.
 *
 * @package WPS\CoreSupport\Features
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPS\CoreSupport\Features;


/**
 * WPS_Feature_Resource_Hints
 *
 * Manages DNS prefetch and resource hints.
 */
final class WPS_Feature_Resource_Hints extends WPS_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                  => 'resource-hints',
				'name'                => __( 'DNS Prefetch & Resource Hints Management', 'plugin-wp-support-thisismyurl' ),
				'description'         => __( 'Control DNS prefetch and resource hints to improve external resource loading', 'plugin-wp-support-thisismyurl' ),
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

		add_filter( 'wp_resource_hints', array( $this, 'filter_resource_hints' ), 10, 2 );
	}

	/**
	 * Filter resource hints.
	 *
	 * @param array  $urls           URLs to process.
	 * @param string $relation_type  The relation type (dns-prefetch, preconnect, etc.).
	 * @return array Filtered URLs.
	 */
	public function filter_resource_hints( array $urls, string $relation_type ): array {
		if ( 'dns-prefetch' !== $relation_type ) {
			return $urls;
		}

		$options = (array) get_option( 'wps_resource_hints_options', $this->get_default_options() );

		// Remove WordPress.org DNS prefetch.
		if ( $options['remove_s_w_org'] ?? false ) {
			$urls = array_diff(
				$urls,
				array(
					'https://s.w.org',
					'//s.w.org',
					'http://s.w.org',
				)
			);
		}

		// Add custom hints.
		$custom_hints = (array) get_option( 'wps_custom_resource_hints', array() );
		if ( ! empty( $custom_hints ) ) {
			$urls = array_merge( $urls, array_values( $custom_hints ) );
			$urls = array_unique( $urls );
		}

		return $urls;
	}

	/**
	 * Get default options.
	 *
	 * @return array Default options.
	 */
	private function get_default_options(): array {
		return array(
			'remove_s_w_org' => true,
		);
	}
}

