<?php
/**
 * Feature: Embed Script Disabling & Optimization
 *
 * Disable WordPress embed functionality (wp-embed.js) for sites that
 * don't allow embedding or don't need oEmbed functionality.
 *
 * @package WPS\CoreSupport\Features
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

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
				'description'        => __( 'Remove code for embedding features you probably don\'t use', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'widget_label'       => __( 'Performance & Security', 'plugin-wpshadow' ),
				'widget_description' => __( 'Remove bloat and unnecessary scripts that impact security and page speed', 'plugin-wpshadow' ),
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

		add_action( 'init', array( $this, 'disable_embeds' ) );
	}

	/**
	 * Disable embed functionality.
	 *
	 * @return void
	 */
	public function disable_embeds(): void {
		$options = (array) $this->get_setting( 'wpshadow_embed_disable_options', $this->get_default_options( ) );

		// Remove embed script on frontend.
		if ( $options['disable_embed_script'] ?? false ) {
			if ( ! is_admin() ) {
				wp_deregister_script( 'wp-embed' );
			}
		}

		// Remove oEmbed discovery links.
		if ( $options['remove_oembed_links'] ?? false ) {
			remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
			remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		}

		// Disable REST API oEmbed endpoint.
		if ( $options['disable_rest_oembed'] ?? false ) {
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
}
