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
				'aliases'         => array( 'remove emoji', 'wp head', 'version hiding', 'security hardening', 'xmlrpc disable', 'rsd link', 'generator tag', 'head optimization', 'remove feeds', 'rest api', 'oembed', 'shortlink' ),
				'sub_features'    => array(
					'remove_emoji'          => __( 'Remove emoji code (makes site faster)', 'wpshadow' ),
					'remove_generator'      => __( 'Hide WordPress version (better security)', 'wpshadow' ),
					'remove_shortlink'      => __( 'Remove short link tags', 'wpshadow' ),
					'remove_rsd'            => __( 'Remove old blog tool links', 'wpshadow' ),
					'remove_wlw'            => __( 'Remove Windows blog editor link', 'wpshadow' ),
					'remove_rest_link'      => __( 'Remove connection link (may affect apps)', 'wpshadow' ),
					'remove_oembed'         => __( 'Remove embed discovery code', 'wpshadow' ),
					'remove_feeds'          => __( 'Remove feed links (may affect RSS readers)', 'wpshadow' ),
					'remove_comments_style' => __( 'Remove comment styling code', 'wpshadow' ),
					'disable_xmlrpc'        => __( 'Turn off old remote access (better security)', 'wpshadow' ),
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

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'wpshadow head-cleanup', array( $this, 'handle_cli_command' ) );
		}
	}

	/**
	 * Apply cleanup actions based on enabled sub-features.
	 *
	 * @return void
	 */
	public function apply_head_cleanup(): void {
		$removed = array();

		if ( $this->is_sub_feature_enabled( 'remove_emoji', true ) ) {
			remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
			remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
			remove_action( 'wp_print_styles', 'print_emoji_styles' );
			remove_action( 'admin_print_styles', 'print_emoji_styles' );
			$removed[] = 'emoji';
		}

		if ( $this->is_sub_feature_enabled( 'remove_generator', true ) ) {
			remove_action( 'wp_head', 'wp_generator' );
			add_filter( 'the_generator', '__return_false' );
			$removed[] = 'generator';
		}

		if ( $this->is_sub_feature_enabled( 'remove_shortlink', true ) ) {
			remove_action( 'wp_head', 'wp_shortlink_wp_head' );
			$removed[] = 'shortlink';
		}

		if ( $this->is_sub_feature_enabled( 'remove_rsd', true ) ) {
			remove_action( 'wp_head', 'rsd_link' );
			$removed[] = 'rsd';
		}

		if ( $this->is_sub_feature_enabled( 'remove_wlw', true ) ) {
			remove_action( 'wp_head', 'wlwmanifest_link' );
			$removed[] = 'wlw';
		}

		if ( $this->is_sub_feature_enabled( 'remove_rest_link', false ) ) {
			remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
			$removed[] = 'rest_link';
		}

		if ( $this->is_sub_feature_enabled( 'remove_oembed', true ) ) {
			remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
			remove_action( 'wp_head', 'wp_oembed_add_host_js' );
			$removed[] = 'oembed';
		}

		if ( $this->is_sub_feature_enabled( 'remove_feeds', false ) ) {
			remove_action( 'wp_head', 'feed_links_extra', 3 );
			remove_action( 'wp_head', 'feed_links', 2 );
			$removed[] = 'feeds';
		}

		if ( $this->is_sub_feature_enabled( 'remove_comments_style', true ) ) {
			global $wp_widget_factory;
			if ( isset( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'] ) ) {
				remove_action( 'wp_head', array( $wp_widget_factory->widgets['WP_Widget_Recent_Comments'], 'recent_comments_style' ) );
			}
			$removed[] = 'comments_style';
		}

		if ( $this->is_sub_feature_enabled( 'disable_xmlrpc', true ) ) {
			add_filter( 'xmlrpc_enabled', '__return_false' );
			$removed[] = 'xmlrpc';
		}

		if ( ! empty( $removed ) ) {
			do_action( 'wpshadow_head_cleanup_applied', array_unique( $removed ) );
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

	/**
	 * Handle WP-CLI command for head cleanup.
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args (unused).
	 *
	 * @return void
	 */
	public function handle_cli_command( array $args, array $assoc_args ): void {
		$action = $args[0] ?? 'status';

		if ( 'status' !== $action ) {
			\WP_CLI::error( __( 'Unknown subcommand. Try: wp wpshadow head-cleanup status', 'wpshadow' ) );
			return;
		}

		\WP_CLI::log( __( 'Head Cleanup status:', 'wpshadow' ) );
		\WP_CLI::log( sprintf( '  %s: %s', __( 'Feature enabled', 'wpshadow' ), $this->is_enabled() ? 'yes' : 'no' ) );

		$subs = array(
			'remove_emoji',
			'remove_generator',
			'remove_shortlink',
			'remove_rsd',
			'remove_wlw',
			'remove_rest_link',
			'remove_oembed',
			'remove_feeds',
			'remove_comments_style',
			'disable_xmlrpc',
		);

		foreach ( $subs as $sub ) {
			$enabled = $this->is_sub_feature_enabled( $sub, false );
			\WP_CLI::log( sprintf( '  - %s: %s', $sub, $enabled ? 'on' : 'off' ) );
		}

		\WP_CLI::success( __( 'Head cleanup inspected.', 'wpshadow' ) );
	}
}
