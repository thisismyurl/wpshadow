<?php declare(strict_types=1);
/**
 * Feature: DNS Prefetch & Resource Hints Management
 *
 * Control which DNS prefetch and resource hint links are added to <head>.
 * Remove unnecessary hints and add strategic hints for external resources.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.73001
 */

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages DNS prefetch and resource hints.
 */
final class WPSHADOW_Feature_Resource_Hints extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'resource-hints',
				'name'               => __( 'Pre-Connect to External Services', 'wpshadow' ),
				'description'        => __( 'Speed up external services by nudging browsers to connect early and trimming unneeded hints.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-performance',
				'category'           => 'performance',
				'priority'           => 15,
				'sub_features'       => array(
					'dns_prefetch'    => array(
						'name'            => __( 'DNS Prefetch', 'wpshadow' ),
						'description'     => __( 'Allow DNS prefetch hints to remain for external hosts.', 'wpshadow' ),
						'default_enabled' => true,
					),
					'preconnect'      => array(
						'name'            => __( 'Preconnect Resources', 'wpshadow' ),
						'description'     => __( 'Allow preconnect hints for faster handshakes.', 'wpshadow' ),
						'default_enabled' => true,
					),
					'preload_fonts'   => array(
						'name'            => __( 'Preload Web Fonts', 'wpshadow' ),
						'description'     => __( 'Enable optional font preloads you configure.', 'wpshadow' ),
						'default_enabled' => false,
					),
					'preload_scripts' => array(
						'name'            => __( 'Preload Critical Scripts', 'wpshadow' ),
						'description'     => __( 'Enable optional script preloads you configure.', 'wpshadow' ),
						'default_enabled' => false,
					),
					'remove_s_w_org'  => array(
						'name'            => __( 'Remove WordPress.org DNS Prefetch', 'wpshadow' ),
						'description'     => __( 'Drop the default s.w.org prefetch hint.', 'wpshadow' ),
						'default_enabled' => true,
					),
				),
			)
		);

		// Default custom hint settings.
		$this->register_default_settings(
			array(
				'custom_resource_hints' => array(),
				'preload_resources'     => array(),
			)
		);
	}

	public function has_details_page(): bool {
		return true;
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
	 * Filter resource hints for dns-prefetch/preconnect.
	 *
	 * @param array  $urls          URLs to process.
	 * @param string $relation_type Relation type.
	 * @return array Filtered URLs.
	 */
	public function filter_resource_hints( array $urls, string $relation_type ): array {
		// Remove the default s.w.org DNS prefetch when requested.
		if ( 'dns-prefetch' === $relation_type && $this->is_sub_feature_enabled( 'remove_s_w_org', true ) ) {
			$urls = array_diff( $urls, array( 'https://s.w.org', '//s.w.org', 'http://s.w.org' ) );
		}

		// Early exits if relation type not enabled.
		if ( 'dns-prefetch' === $relation_type && ! $this->is_sub_feature_enabled( 'dns_prefetch', true ) ) {
			return array();
		}
		if ( 'preconnect' === $relation_type && ! $this->is_sub_feature_enabled( 'preconnect', true ) ) {
			return array();
		}

		// Append custom hints if provided.
		$custom_hints = (array) $this->get_setting( 'custom_resource_hints', array() );
		if ( ! empty( $custom_hints ) && in_array( $relation_type, array( 'dns-prefetch', 'preconnect' ), true ) ) {
			$urls = array_merge( $urls, array_values( $custom_hints ) );
			$urls = array_unique( $urls );
		}

		return $urls;
	}

	/**
	 * Output preload link tags based on configured resources.
	 */
	public function add_preload_headers(): void {
		$preloads = (array) $this->get_setting( 'preload_resources', array() );

		foreach ( $preloads as $resource ) {
			if ( ! is_array( $resource ) || empty( $resource['url'] ) || empty( $resource['type'] ) ) {
				continue;
			}

			$type       = sanitize_key( $resource['type'] );
			$is_font    = ( 'font' === $type );
			$is_script  = ( 'script' === $type );
			$is_style   = ( 'style' === $type );

			if ( ( $is_font && ! $this->is_sub_feature_enabled( 'preload_fonts', false ) ) || ( $is_script && ! $this->is_sub_feature_enabled( 'preload_scripts', false ) ) ) {
				continue;
			}

			$url = esc_url( $resource['url'] );
			if ( empty( $url ) ) {
				continue;
			}

			$attributes = sprintf( 'rel="preload" href="%s" as="%s"', $url, esc_attr( $type ) );

			if ( $is_font ) {
				$mime_type   = isset( $resource['mime_type'] ) ? sanitize_text_field( (string) $resource['mime_type'] ) : 'font/woff2';
				$attributes .= sprintf( ' type="%s" crossorigin', esc_attr( $mime_type ) );
			}

			if ( $is_style && ! empty( $resource['media'] ) ) {
				$attributes .= sprintf( ' media="%s"', esc_attr( (string) $resource['media'] ) );
			}

			echo '<link ' . $attributes . ' />' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
			'label' => __( 'Resource Hints', 'wpshadow' ),
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
				'label'       => __( 'Resource Hints', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'wpshadow' ),
					'color' => 'gray',
				),
				'description' => sprintf( '<p>%s</p>', __( 'Resource hints are disabled. Enabling them can improve perceived performance for external assets.', 'wpshadow' ) ),
				'actions'     => '',
				'test'        => 'WPSHADOW_resource_hints',
			);
		}

		return array(
			'label'       => __( 'Resource hints are managed', 'wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Performance', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf( '<p>%s</p>', __( 'Resource hints are being managed to remove unnecessary defaults and apply your custom entries.', 'wpshadow' ) ),
			'actions'     => '',
			'test'        => 'WPSHADOW_resource_hints',
		);
	}
}
