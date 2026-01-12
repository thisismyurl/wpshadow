<?php
/**
 * Feature: WordPress Head Cleanup & Security Hardening
 *
 * Remove unnecessary meta tags, links, and scripts from <head> that expose
 * version info, add bloat, or provide no value for modern sites.
 *
 * @package WPS\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPS\CoreSupport\Features;

/**
 * WPS_Feature_Head_Cleanup
 *
 * Comprehensive cleanup of WordPress head section.
 */
final class WPS_Feature_Head_Cleanup extends WPS_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                  => 'head-cleanup',
				'name'                => __( 'Head Cleanup & Security Hardening', 'plugin-wp-support-thisismyurl' ),
				'description'         => __( 'Remove unnecessary meta tags, links, and version info from page head', 'plugin-wp-support-thisismyurl' ),
				'scope'               => 'core',
				'default_enabled'     => true,
				'version'             => '1.0.0',
				'widget_group'        => 'performance-security',
				'widget_label'        => __( 'Performance & Security', 'plugin-wp-support-thisismyurl' ),
				'widget_description'  => __( 'Remove bloat and unnecessary scripts that impact security and page speed', 'plugin-wp-support-thisismyurl' ),
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

		add_action( 'init', array( $this, 'cleanup_head_elements' ) );
	}

	/**
	 * Remove unnecessary head elements.
	 *
	 * @return void
	 */
	public function cleanup_head_elements(): void {
		// Get options for granular control.
		$cleanup_options = (array) get_option( 'wps_head_cleanup_options', $this->get_default_options() );

		// Emojis.
		if ( $cleanup_options['remove_emoji'] ?? false ) {
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			remove_action( 'admin_print_styles', 'print_emoji_styles' );
		}

		// Meta tags and generators.
		if ( $cleanup_options['remove_generator'] ?? false ) {
			remove_action( 'wp_head', 'wp_generator' );
			add_filter( 'the_generator', '__return_false' );
		}

		if ( $cleanup_options['remove_shortlink'] ?? false ) {
			remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		}

		// Discovery links.
		if ( $cleanup_options['remove_rsd'] ?? false ) {
			remove_action( 'wp_head', 'rsd_link' );
		}

		if ( $cleanup_options['remove_wlw'] ?? false ) {
			remove_action( 'wp_head', 'wlwmanifest_link' );
		}

		if ( $cleanup_options['remove_rest_link'] ?? false ) {
			remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
		}

		// Embeds.
		if ( $cleanup_options['remove_oembed'] ?? false ) {
			remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
			remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		}

		// Feeds.
		if ( $cleanup_options['remove_feeds'] ?? false ) {
			remove_action( 'wp_head', 'feed_links_extra', 3 );
			remove_action( 'wp_head', 'feed_links', 2 );
		}

		// Recent comments style.
		if ( $cleanup_options['remove_comments_style'] ?? false ) {
			global $wp_widget_factory;
			if ( isset( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'] ) ) {
				remove_action(
					'wp_head',
					array( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style' )
				);
			}
		}

		// Security.
		if ( $cleanup_options['disable_xmlrpc'] ?? false ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
		}
	}

	/**
	 * Get default cleanup options.
	 *
	 * @return array Default options.
	 */
	private function get_default_options(): array {
		return array(
			'remove_emoji'            => true,
			'remove_generator'        => true,
			'remove_shortlink'        => true,
			'remove_rsd'              => true,
			'remove_wlw'              => true,
			'remove_rest_link'        => false, // Keep by default (might break REST clients).
			'remove_oembed'           => true,
			'remove_feeds'            => false, // Keep by default (might be needed).
			'remove_comments_style'   => true,
			'disable_xmlrpc'          => true,
		);
	}
}

