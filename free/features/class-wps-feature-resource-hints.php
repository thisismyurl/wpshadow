<?php
/**
 * Feature: DNS Prefetch & Resource Hints Management
 *
 * Control which DNS prefetch and resource hint links are added to <head>.
 * Remove unnecessary hints and add strategic hints for external resources.
 *
 * @package WPShadow\Features
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_Resource_Hints
 *
 * Manages DNS prefetch and resource hints.
 */
final class WPSHADOW_Feature_Resource_Hints extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'resource-hints',
				'name'               => __( 'Pre-Connect to External Services', 'plugin-wpshadow' ),
				'description'        => __( 'Speed up external services - tell browsers to start connecting early.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-performance',
				'category'           => 'performance',
				'priority'           => 15,
			)
		);
		
		if ( method_exists( $this, 'register_sub_features' ) ) {
			$this->register_sub_features(
				array(
					'dns_prefetch'      => __( 'DNS Prefetch', 'plugin-wpshadow' ),
					'preconnect'        => __( 'Preconnect Resources', 'plugin-wpshadow' ),
					'preload_fonts'     => __( 'Preload Web Fonts', 'plugin-wpshadow' ),
					'preload_scripts'   => __( 'Preload Critical Scripts', 'plugin-wpshadow' ),
					'remove_s_w_org'    => __( 'Remove WordPress.org DNS Prefetch', 'plugin-wpshadow' ),
				)
			);
			if ( method_exists( $this, 'set_default_sub_features' ) ) {
				$this->set_default_sub_features(
					array(
						'dns_prefetch'      => true,
						'preconnect'        => true,
						'preload_fonts'     => false,
						'preload_scripts'   => false,
						'remove_s_w_org'    => true,
					)
				);
			}
		}
		
		$this->log_activity( 'feature_initialized', 'Resource Hints feature initialized', 'info' );
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

		add_filter( 'wp_resource_hints', array( $this, 'filter_resource_hints' ), 10, 2 );
		add_action( 'wp_head', array( $this, 'add_preload_headers' ), 2 );
		
		// Add Site Health tests.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
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

		$options = (array) $this->get_setting( 'wpshadow_resource_hints_options', $this->get_default_options( ) );

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
		$custom_hints = (array) $this->get_setting( 'wpshadow_custom_resource_hints', array( ) );
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
	protected function get_default_options(): array {
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
		$preload_resources = (array) $this->get_setting( 'wpshadow_preload_resources', array( ) );

		// Allow filtering.
		$preload_resources = apply_filters( 'wpshadow_preload_resources', $preload_resources );

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

	/**
	 * Register Site Health test.
	 *
	 * @param array<string, mixed> $tests Site Health tests.
	 * @return array<string, mixed>
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['WPSHADOW_resource_hints'] = array(
			'label' => __( 'Resource Hints', 'plugin-wpshadow' ),
			'test'  => array( $this, 'test_resource_hints' ),
		);
		return $tests;
	}

	/**
	 * Site Health test for resource hints.
	 *
	 * @return array<string, mixed>
	 */
	public function test_resource_hints(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Resource Hints', 'plugin-wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'plugin-wpshadow' ),
					'color' => 'gray',
				),
				'description' => sprintf( '<p>%s</p>', __( 'Resource Hints are not enabled. Enabling resource hints can improve page load times by pre-connecting to external services.', 'plugin-wpshadow' ) ),
				'actions'     => '',
				'test'        => 'WPSHADOW_resource_hints',
			);
		}

		// Count enabled sub-features.
		$enabled_features = 0;
		if ( get_option( 'wpshadow_resource-hints_dns_prefetch', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_resource-hints_preconnect', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_resource-hints_preload_fonts', false ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_resource-hints_preload_scripts', false ) ) {
			++$enabled_features;
		}

		// Count custom hints.
		$custom_hints = (array) $this->get_setting( 'wpshadow_custom_resource_hints', array() );
		$custom_count = count( $custom_hints );

		return array(
			'label'       => __( 'Resource Hints', 'plugin-wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Performance', 'plugin-wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				/* translators: 1: number of enabled features, 2: number of custom hints */
				sprintf(
					__( 'Resource Hints are active with %1$d optimization features enabled and %2$d custom hints configured.', 'plugin-wpshadow' ),
					$enabled_features,
					$custom_count
				)
			),
			'actions'     => '',
			'test'        => 'WPSHADOW_resource_hints',
		);
	}
}
