<?php declare(strict_types=1);
/**
 * Feature: DNS Prefetch & Resource Hints Management
 *
 * Control DNS prefetch and resource hints in page head.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75001
 */

namespace WPShadow\CoreSupport;

final class WPSHADOW_Feature_Resource_Hints extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct( array(
			'id'          => 'resource-hints',
			'name'        => __( 'Pre-Connect to External Services', 'wpshadow' ),
			'description' => __( 'Speed up external services with DNS prefetch and preconnect.', 'wpshadow' ),
			'sub_features' => array(
				'dns_prefetch'      => __( 'DNS Prefetch', 'wpshadow' ),
				'preconnect'        => __( 'Preconnect', 'wpshadow' ),
				'preload_fonts'     => __( 'Preload Fonts', 'wpshadow' ),
				'preload_scripts'   => __( 'Preload Scripts', 'wpshadow' ),
				'remove_s_w_org'    => __( 'Remove WordPress.org DNS', 'wpshadow' ),
			),
		) );

		$this->register_default_settings( array(
			'dns_prefetch'      => true,
			'preconnect'        => true,
			'preload_fonts'     => false,
			'preload_scripts'   => false,
			'remove_s_w_org'    => true,
		) );
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		add_filter( 'wp_resource_hints', array( $this, 'filter_resource_hints' ), 10, 2 );
		add_action( 'wp_head', array( $this, 'add_preload_headers' ), 2 );
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Filter resource hints.
	 */
	public function filter_resource_hints( array $urls, string $relation_type ): array {
		if ( 'dns-prefetch' !== $relation_type ) {
			return $urls;
		}

		// Remove WordPress.org DNS prefetch
		if ( $this->is_sub_feature_enabled( 'remove_s_w_org', true ) ) {
			$urls = array_diff(
				$urls,
				array( 'https://s.w.org', '//s.w.org', 'http://s.w.org' )
			);
		}

		// Add custom hints
		$custom_hints = $this->get_setting( 'custom_hints', array() );
		if ( ! empty( $custom_hints ) ) {
			$urls = array_merge( $urls, (array) $custom_hints );
			$urls = array_unique( $urls );
		}

		return $urls;
	}

	/**
	 * Add preload headers.
	 */
	public function add_preload_headers(): void {
		$preload_resources = $this->get_setting( 'preload_resources', array() );

		$preload_resources = apply_filters( 'wpshadow_preload_resources', $preload_resources );

		if ( ! is_array( $preload_resources ) ) {
			return;
		}

		foreach ( $preload_resources as $resource ) {
			if ( ! is_array( $resource ) || empty( $resource['url'] ) || empty( $resource['type'] ) ) {
				continue;
			}

			$url  = esc_url( $resource['url'] );
			$type = sanitize_key( $resource['type'] );
			$attrs = sprintf( 'rel="preload" href="%s" as="%s"', $url, $type );

			if ( 'font' === $type ) {
				$mime_type = $resource['mime_type'] ?? 'font/woff2';
				$attrs .= sprintf( ' type="%s" crossorigin', esc_attr( $mime_type ) );
			}

			if ( 'style' === $type && ! empty( $resource['media'] ) ) {
				$attrs .= sprintf( ' media="%s"', esc_attr( $resource['media'] ) );
			}

			echo '<link ' . $attrs . '>' . "\n";
		}
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['resource_hints'] = array(
			'label'  => __( 'Resource Hints', 'wpshadow' ),
			'test'   => array( $this, 'test_hints' ),
		);

		return $tests;
	}

	public function test_hints(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Resource Hints', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
				'description' => __( 'Enable resource hints for performance.', 'wpshadow' ),
				'actions'     => '',
				'test'        => 'resource_hints',
			);
		}

		$enabled_count = 0;
		$subs = array( 'dns_prefetch', 'preconnect', 'preload_fonts', 'preload_scripts', 'remove_s_w_org' );
		foreach ( $subs as $sub ) {
			if ( $this->is_sub_feature_enabled( $sub, false ) ) {
				$enabled_count++;
			}
		}

		$custom_hints = $this->get_setting( 'custom_hints', array() );
		$custom_count = is_array( $custom_hints ) ? count( $custom_hints ) : 0;

		return array(
			'label'       => __( 'Resource Hints', 'wpshadow' ),
			'status'      => 'good',
			'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
			'description' => sprintf(
				__( '%d features enabled, %d custom hints.', 'wpshadow' ),
				$enabled_count,
				$custom_count
			),
			'actions'     => '',
			'test'        => 'resource_hints',
		);
	}
}
