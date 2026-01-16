<?php
/**
 * Feature: Embed Script Disabling & Optimization
 *
 * Disable WordPress embed functionality (wp-embed.js) for sites that
 * don't allow embedding or don't need oEmbed functionality.
 *
 * @package WPShadow\Features
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_Embed_Disable
 *
 * Disables WordPress embed functionality.
 */
final class WPSHADOW_Feature_Embed_Disable extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'embed-disable',
				'name'               => __( 'Embed Script Disabling & Optimization', 'plugin-wpshadow' ),
				'description'        => __( 'Stop loading embed code you don\'t use - speed up pages and protect privacy.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-format-video',
				'category'           => 'performance',
				'priority'           => 20,
			)
		);

		if ( method_exists( $this, 'register_sub_features' ) ) {
			$this->register_sub_features(
				array(
					'disable_embed_script' => __( 'Disable wp-embed.js Script', 'plugin-wpshadow' ),
					'remove_oembed_links'  => __( 'Remove oEmbed Discovery Links', 'plugin-wpshadow' ),
					'disable_rest_oembed'  => __( 'Disable REST API oEmbed Endpoint', 'plugin-wpshadow' ),
					'remove_embed_rewrite' => __( 'Remove Embed Rewrite Rules', 'plugin-wpshadow' ),
				)
			);
		}

		if ( method_exists( $this, 'set_default_sub_features' ) ) {
			$this->set_default_sub_features(
				array(
					'disable_embed_script' => true,
					'remove_oembed_links'  => true,
					'disable_rest_oembed'  => false,
					'remove_embed_rewrite' => true,
				)
			);
		}
		
		$this->log_activity( 'feature_initialized', 'Embed Disable feature initialized', 'info' );
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

		add_action( 'init', array( $this, 'disable_embeds' ) );
		
		// Add Site Health tests.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Disable embed functionality.
	 *
	 * @return void
	 */
	public function disable_embeds(): void {
		// Remove embed script on frontend if enabled.
		if ( get_option( 'wpshadow_embed-disable_disable_embed_script', true ) ) {
			if ( ! is_admin() ) {
				wp_deregister_script( 'wp-embed' );
			}
		}

		// Remove oEmbed discovery links if enabled.
		if ( get_option( 'wpshadow_embed-disable_remove_oembed_links', true ) ) {
			remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
			remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		}

		// Disable REST API oEmbed endpoint if enabled.
		if ( get_option( 'wpshadow_embed-disable_disable_rest_oembed', false ) ) {
			add_filter(
				'rest_endpoints',
				static function ( $endpoints ) {
					unset( $endpoints['/oembed/1.0/embed'] );
					return $endpoints;
				}
			);
		}
	}

	/**
	 * Get default options.
	 *
	 * @return array Default options.
	 */
	protected function get_default_options(): array {
		return array(
			'disable_embed_script' => true,
			'remove_oembed_links'  => true,
			'disable_rest_oembed'  => false, // Keep by default (might break some integrations).
		);
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array<string, mixed> $tests Site Health tests.
	 * @return array<string, mixed>
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['WPSHADOW_embed_disable'] = array(
			'label' => __( 'Embed Optimization', 'plugin-wpshadow' ),
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
				'label'       => __( 'Embed Optimization', 'plugin-wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'plugin-wpshadow' ),
					'color' => 'gray',
				),
				'description' => sprintf( '<p>%s</p>', __( 'Embed optimization is not enabled. Disabling unused embed functionality can reduce script bloat and improve page load times.', 'plugin-wpshadow' ) ),
				'actions'     => '',
				'test'        => 'WPSHADOW_embed_disable',
			);
		}

		// Count enabled sub-features.
		$enabled_features = 0;
		if ( get_option( 'wpshadow_embed-disable_disable_embed_script', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_embed-disable_remove_oembed_links', true ) ) {
			++$enabled_features;
		}
		if ( get_option( 'wpshadow_embed-disable_disable_rest_oembed', false ) ) {
			++$enabled_features;
		}

		return array(
			'label'       => __( 'Embed Optimization', 'plugin-wpshadow' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Performance', 'plugin-wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				/* translators: %d: number of enabled embed optimization features */
				sprintf(
					__( 'Embed optimization is active with %d optimization features enabled.', 'plugin-wpshadow' ),
					$enabled_features
				)
			),
			'actions'     => '',
			'test'        => 'WPSHADOW_embed_disable',
		);
	}
}
