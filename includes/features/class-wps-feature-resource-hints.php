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

namespace WPS\CoreSupport;

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
				'id'                 => 'resource-hints',
				'name'               => __( 'DNS Prefetch & Resource Hints Management', 'plugin-wp-support-thisismyurl' ),
				'description'        => __( 'Tell browsers to prepare for external services, so they load faster', 'plugin-wp-support-thisismyurl' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'widget_label'       => __( 'Resource Optimization', 'plugin-wp-support-thisismyurl' ),
				'widget_description' => __( 'Optimize how resources are loaded and delivered', 'plugin-wp-support-thisismyurl' ),
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
		add_action( 'wp_head', array( $this, 'add_preload_headers' ), 2 );
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

		$options = (array) $this->get_setting( 'wps_resource_hints_options', $this->get_default_options( ) );

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
		$custom_hints = (array) $this->get_setting( 'wps_custom_resource_hints', array( ) );
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

	/**
	 * Add preload headers for critical resources.
	 *
	 * @return void
	 */
	public function add_preload_headers(): void {
		$preload_resources = (array) $this->get_setting( 'wps_preload_resources', array( ) );

		// Allow filtering.
		$preload_resources = apply_filters( 'wps_preload_resources', $preload_resources );

		foreach ( $preload_resources as $resource ) {
			if ( ! is_array( $resource ) || empty( $resource['url'] ) || empty( $resource['type'] ) ) {
				continue;
			}

			$url  = esc_url( $resource['url'] );
			$type = sanitize_key( $resource['type'] );

			// Build preload tag based on resource type.
			$attributes = sprintf( 'rel="preload" href="%s" as="%s"', $url, $type );

			// Add type attribute for fonts.
			if ( 'font' === $type ) {
				$mime_type   = $resource['mime_type'] ?? 'font/woff2';
				$attributes .= sprintf( ' type="%s" crossorigin', esc_attr( $mime_type ) );
			}

			// Add media attribute for styles if specified.
			if ( 'style' === $type && ! empty( $resource['media'] ) ) {
				$attributes .= sprintf( ' media="%s"', esc_attr( $resource['media'] ) );
			}

			echo '<link ' . $attributes . '>' . "\n";
		}
	}
}
