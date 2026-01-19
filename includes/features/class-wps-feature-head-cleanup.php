<?php
/**
 * Feature: WordPress Head Cleanup & Security Hardening
 *
 * Remove unnecessary meta tags, links, and scripts from <head> that expose
 * version info, add bloat, or provide no value for modern sites.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_Head_Cleanup
 *
 * Comprehensive cleanup of WordPress head section with multiple sub-features.
 */
final class WPSHADOW_Feature_Head_Cleanup extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'              => 'head-cleanup',
				'name'            => __( 'Remove Unnecessary Page Code', 'wpshadow' ),
				'description'     => __( 'Clean up your page headers - remove clutter that slows you down and reveals too much about your site.', 'wpshadow' ),
				'scope'           => 'core',
				'default_enabled' => true,
				'version'         => '1.0.0',
				'widget_group'    => 'performance',
				'sub_features'    => array(
					'remove_emoji'          => __( 'Remove Emoji Scripts (Improves performance)', 'wpshadow' ),
					'remove_generator'      => __( 'Remove WP Generator Meta Tag (Security)', 'wpshadow' ),
					'remove_shortlink'      => __( 'Remove Shortlink Tag', 'wpshadow' ),
					'remove_rsd'            => __( 'Remove RSD Link (Really Simple Discovery)', 'wpshadow' ),
					'remove_wlw'            => __( 'Remove Windows Live Writer Manifest', 'wpshadow' ),
					'remove_rest_link'      => __( 'Remove REST API Link (May break REST clients)', 'wpshadow' ),
					'remove_oembed'         => __( 'Remove oEmbed Discovery Links', 'wpshadow' ),
					'remove_feeds'          => __( 'Remove Feed Links (May break RSS readers)', 'wpshadow' ),
					'remove_comments_style' => __( 'Remove Recent Comments Inline Styles', 'wpshadow' ),
					'disable_xmlrpc'        => __( 'Disable XML-RPC (Security)', 'wpshadow' ),
				),
			)
		);

		$this->register_default_settings(
			array(
				'remove_emoji'          => true,
				'remove_generator'      => true,
				'remove_shortlink'      => true,
				'remove_rsd'            => true,
				'remove_wlw'            => true,
				'remove_rest_link'      => false,
				'remove_oembed'         => true,
				'remove_feeds'          => false,
				'remove_comments_style' => true,
				'disable_xmlrpc'        => true,
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

		add_action( 'init', array( $this, 'apply_head_cleanup' ) );
		add_filter( 'site_status_tests', array( $this, 'register_site_health_tests' ) );
	}

	/**
	 * Apply cleanup actions based on enabled sub-features.
	 *
	 * @return void
	 */
	public function apply_head_cleanup(): void {
		if ( $this->is_sub_feature_enabled( 'remove_emoji', true ) ) {
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			remove_action( 'admin_print_styles', 'print_emoji_styles' );
		}

		if ( $this->is_sub_feature_enabled( 'remove_generator', true ) ) {
			remove_action( 'wp_head', 'wp_generator' );
			add_filter( 'the_generator', '__return_false' );
		}

		if ( $this->is_sub_feature_enabled( 'remove_shortlink', true ) ) {
			remove_action( 'wp_head', 'wp_shortlink_wp_head' );
		}

		if ( $this->is_sub_feature_enabled( 'remove_rsd', true ) ) {
			remove_action( 'wp_head', 'rsd_link' );
		}

		if ( $this->is_sub_feature_enabled( 'remove_wlw', true ) ) {
			remove_action( 'wp_head', 'wlwmanifest_link' );
		}

		if ( $this->is_sub_feature_enabled( 'remove_rest_link', false ) ) {
			remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
		}

		if ( $this->is_sub_feature_enabled( 'remove_oembed', true ) ) {
			remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
			remove_action( 'wp_head', 'wp_oembed_add_host_js' );
		}

		if ( $this->is_sub_feature_enabled( 'remove_feeds', false ) ) {
			remove_action( 'wp_head', 'feed_links_extra', 3 );
			remove_action( 'wp_head', 'feed_links', 2 );
		}

		if ( $this->is_sub_feature_enabled( 'remove_comments_style', true ) ) {
			global $wp_widget_factory;
			if ( isset( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'] ) ) {
				remove_action( 'wp_head', array( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style' ) );
			}
		}

		if ( $this->is_sub_feature_enabled( 'disable_xmlrpc', true ) ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
		}
	}

	/**
	 * Register Site Health tests.
	 *
	 * @param array $tests Site Health tests.
	 * @return array Modified tests.
	 */
	public function register_site_health_tests( array $tests ): array {
		$tests['direct']['head_cleanup_emoji'] = array(
			'label' => __( 'Emoji Scripts', 'wpshadow' ),
			'test'  => array( $this, 'test_emoji_removal' ),
		);

		$tests['direct']['head_cleanup_xmlrpc'] = array(
			'label' => __( 'XML-RPC Security', 'wpshadow' ),
			'test'  => array( $this, 'test_xmlrpc_disabled' ),
		);

		$tests['direct']['head_cleanup_oembed'] = array(
			'label' => __( 'oEmbed Links', 'wpshadow' ),
			'test'  => array( $this, 'test_oembed_removal' ),
		);

		return $tests;
	}

	/**
	 * Test if emoji scripts are removed.
	 *
	 * @return array Test results.
	 */
	public function test_emoji_removal(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Emoji Scripts', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => __( 'Head cleanup is disabled.', 'wpshadow' ),
				'test'        => 'head_cleanup_emoji',
			);
		}

		$is_removed = $this->is_sub_feature_enabled( 'remove_emoji', true );

		if ( $is_removed ) {
			return array(
				'label'       => __( 'Emoji scripts are disabled', 'wpshadow' ),
				'status'      => 'good',
				'badge'       => array(
					'label' => __( 'Performance', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => __( 'WordPress emoji detection scripts and styles have been removed, improving page load performance.', 'wpshadow' ),
				'test'        => 'head_cleanup_emoji',
			);
		}

		return array(
			'label'       => __( 'Emoji scripts are still loading', 'wpshadow' ),
			'status'      => 'recommended',
			'badge'       => array(
				'label' => __( 'Performance', 'wpshadow' ),
				'color' => 'orange',
			),
			'description' => __( 'WordPress is loading emoji detection scripts on every page. Modern browsers support emojis natively, so these scripts are unnecessary.', 'wpshadow' ),
			'test'        => 'head_cleanup_emoji',
		);
	}

	/**
	 * Test if XML-RPC is disabled.
	 *
	 * @return array Test results.
	 */
	public function test_xmlrpc_disabled(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'XML-RPC', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Security', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => __( 'Head cleanup is disabled.', 'wpshadow' ),
				'test'        => 'head_cleanup_xmlrpc',
			);
		}

		$is_disabled = $this->is_sub_feature_enabled( 'disable_xmlrpc', true );

		if ( $is_disabled ) {
			return array(
				'label'       => __( 'XML-RPC is disabled', 'wpshadow' ),
				'status'      => 'good',
				'badge'       => array(
					'label' => __( 'Security', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => __( 'XML-RPC has been disabled, closing a common attack vector for brute-force and DDoS attacks.', 'wpshadow' ),
				'test'        => 'head_cleanup_xmlrpc',
			);
		}

		return array(
			'label'       => __( 'XML-RPC is enabled', 'wpshadow' ),
			'status'      => 'recommended',
			'badge'       => array(
				'label' => __( 'Security', 'wpshadow' ),
				'color' => 'orange',
			),
			'description' => __( 'XML-RPC is frequently targeted by attackers for brute-force login attempts. Consider disabling it unless you need remote publishing.', 'wpshadow' ),
			'test'        => 'head_cleanup_xmlrpc',
		);
	}

	/**
	 * Test if oEmbed links are removed.
	 *
	 * @return array Test results.
	 */
	public function test_oembed_removal(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'oEmbed Links', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => __( 'Head cleanup is disabled.', 'wpshadow' ),
				'test'        => 'head_cleanup_oembed',
			);
		}

		$is_removed = $this->is_sub_feature_enabled( 'remove_oembed', true );

		if ( $is_removed ) {
			return array(
				'label'       => __( 'oEmbed discovery links removed', 'wpshadow' ),
				'status'      => 'good',
				'badge'       => array(
					'label' => __( 'Performance', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => __( 'oEmbed discovery links and scripts have been removed, reducing page weight.', 'wpshadow' ),
				'test'        => 'head_cleanup_oembed',
			);
		}

		return array(
			'label'       => __( 'oEmbed discovery links present', 'wpshadow' ),
			'status'      => 'recommended',
			'badge'       => array(
				'label' => __( 'Performance', 'wpshadow' ),
				'color' => 'orange',
			),
			'description' => __( 'WordPress is adding oEmbed discovery links to your page head. These can be safely removed if external sites do not need to embed your content.', 'wpshadow' ),
			'test'        => 'head_cleanup_oembed',
		);
	}
}
