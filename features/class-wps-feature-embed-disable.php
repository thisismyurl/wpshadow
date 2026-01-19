<?php declare(strict_types=1);
/**
 * Feature: Embed Script Disabling & Optimization
 *
 * Disable WordPress embed functionality (wp-embed.js) for sites that
 * don't allow embedding or don't need oEmbed functionality.
 *
 * @package    WPShadow\CoreSupport
 * @subpackage Features
 * @since      1.2601.73001
 */

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Disables WordPress embed functionality.
 */
final class WPSHADOW_Feature_Embed_Disable extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'embed-disable',
				'name'               => __( 'Embed Script Disabling & Optimization', 'wpshadow' ),
				'description'        => __( "Stop loading embed code you don't use - speed up pages and protect privacy.", 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-format-video',
				'category'           => 'performance',
				'priority'           => 20,
				'sub_features'       => array(
					'disable_embed_script' => array(
						'name'            => __( 'Disable wp-embed.js Script', 'wpshadow' ),
						'description'     => __( 'Deregister the wp-embed.js script on the frontend.', 'wpshadow' ),
						'default_enabled' => true,
					),
					'remove_oembed_links'  => array(
						'name'            => __( 'Remove oEmbed Discovery Links', 'wpshadow' ),
						'description'     => __( 'Strip oEmbed discovery links and host JS from <head>.', 'wpshadow' ),
						'default_enabled' => true,
					),
					'disable_rest_oembed'  => array(
						'name'            => __( 'Disable REST API oEmbed Endpoint', 'wpshadow' ),
						'description'     => __( 'Remove the REST oEmbed endpoint to prevent discovery.', 'wpshadow' ),
						'default_enabled' => false,
					),
					'remove_embed_rewrite' => array(
						'name'            => __( 'Remove Embed Rewrite Rules', 'wpshadow' ),
						'description'     => __( 'Remove embed rewrite rules and query vars.', 'wpshadow' ),
						'default_enabled' => true,
					),
				),
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

		add_action( 'init', array( $this, 'apply_embed_cleanup' ) );
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Apply embed cleanups based on enabled sub-features.
	 */
	public function apply_embed_cleanup(): void {
		// Disable embed script on frontend.
		if ( $this->is_sub_feature_enabled( 'disable_embed_script', true ) ) {
			if ( ! is_admin() ) {
				wp_deregister_script( 'wp-embed' );
			}
		}

		// Remove oEmbed discovery links and host JS.
		if ( $this->is_sub_feature_enabled( 'remove_oembed_links', true ) ) {
			remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
			remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		}

		// Disable REST API oEmbed endpoint and discovery.
		if ( $this->is_sub_feature_enabled( 'disable_rest_oembed', false ) ) {
			add_filter(
				'rest_endpoints',
				static function ( $endpoints ) {
					if ( isset( $endpoints['/oembed/1.0/embed'] ) ) {
						unset( $endpoints['/oembed/1.0/embed'] );
					}
					return $endpoints;
				}
			);
			add_filter( 'embed_oembed_discover', '__return_false' );
			remove_action( 'rest_api_init', 'wp_oembed_register_route' );
			remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
		}

		// Remove embed rewrite rules and query var.
		if ( $this->is_sub_feature_enabled( 'remove_embed_rewrite', true ) ) {
			add_filter(
				'rewrite_rules_array',
				static function ( array $rules ): array {
					foreach ( $rules as $rule => $rewrite ) {
						if ( strpos( $rule, 'embed/' ) !== false || strpos( $rewrite, 'embed=true' ) !== false ) {
							unset( $rules[ $rule ] );
						}
					}
					return $rules;
				}
			);
			add_filter(
				'query_vars',
				static function ( array $vars ): array {
					return array_values( array_diff( $vars, array( 'embed' ) ) );
				}
			);
		}
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array<string, mixed> $tests Site Health tests.
	 * @return array<string, mixed>
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['WPSHADOW_embed_disable'] = array(
			'label' => __( 'Embed Optimization', 'wpshadow' ),
			'test'  => array( $this, 'test_embed_disable' ),
		);
		return $tests;
	}

	/**
	 * Site Health test for embed disable.
	 *
	 * @return array<string, mixed>
	 */
	public function test_embed_disable(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Embed Optimization', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'wpshadow' ),
					'color' => 'gray',
				),
				'description' => sprintf( '<p>%s</p>', __( 'Embed optimization is not enabled. Disabling unused embed functionality can reduce script bloat and improve page load times.', 'wpshadow' ) ),
				'actions'     => '',
				'test'        => 'WPSHADOW_embed_disable',
			);
		}

		$enabled = 0;
		$enabled += $this->is_sub_feature_enabled( 'disable_embed_script', true ) ? 1 : 0;
		$enabled += $this->is_sub_feature_enabled( 'remove_oembed_links', true ) ? 1 : 0;
		$enabled += $this->is_sub_feature_enabled( 'disable_rest_oembed', false ) ? 1 : 0;
		$enabled += $this->is_sub_feature_enabled( 'remove_embed_rewrite', true ) ? 1 : 0;

		return array(
			'label'       => __( 'Embed Optimization', 'wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Performance', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: %d: number of enabled embed optimization features */
					__( 'Embed optimization is active with %d optimization features enabled.', 'wpshadow' ),
					$enabled
				)
			),
			'actions'     => '',
			'test'        => 'WPSHADOW_embed_disable',
		);
	}
}
