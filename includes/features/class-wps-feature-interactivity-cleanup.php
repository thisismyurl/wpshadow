<?php declare(strict_types=1);
/**
 * Feature: Interactivity API & DNS Cleanup
 *
 * Disable unnecessary Interactivity API and Block Bindings on pages that don't use them.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75001
 */

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


final class WPSHADOW_Feature_Interactivity_Cleanup extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct( array(
			'id'          => 'interactivity-cleanup',
			'name'        => __( 'Remove Modern Block Code', 'wpshadow' ),
			'description' => __( 'Remove new interactive features code if you don\'t use modern blocks. Makes your site faster.', 'wpshadow' ),
			'aliases'     => array( 'interactivity api', 'block bindings', 'modern blocks', 'interactive blocks', 'wordpress 6.5', 'gutenberg features', 'block editor scripts', 'wp interactivity', 'dynamic blocks', 'interactive features', 'block api', 'modern wordpress' ),
			'sub_features' => array(
				'disable_interactivity_api' => array(
					'name'               => __( 'Disable Interactivity API', 'wpshadow' ),
					'description_short'  => __( 'Remove WordPress Interactivity API code', 'wpshadow' ),
					'description_long'   => __( 'Removes the WordPress Interactivity API script (wp-interactivity) that was introduced in WordPress 6.5. This API enables interactive blocks that respond to user actions. If you don\'t use interactive blocks, this code is unused overhead. Disabling it saves bandwidth and improves performance on sites that only use basic or static block content.', 'wpshadow' ),
					'description_wizard' => __( 'New interactive block features added in WordPress 6.5. Remove this if you don\'t use interactive blocks, saving bandwidth and improving performance.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'disable_block_bindings'    => array(
					'name'               => __( 'Disable Block Bindings', 'wpshadow' ),
					'description_short'  => __( 'Remove block bindings connection code', 'wpshadow' ),
					'description_long'   => __( 'Disables block bindings, a WordPress feature that allows blocks to dynamically connect to data sources. This is an advanced feature used by developers to build dynamic content blocks. Most sites don\'t use this feature. Disabling removes unnecessary code that loads even when not used.', 'wpshadow' ),
					'description_wizard' => __( 'Advanced block feature for dynamic content connections. Safe to disable unless you specifically use block bindings in your theme or content.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'remove_dns_prefetch'       => array(
					'name'               => __( 'Remove DNS Prefetch to WordPress.org', 'wpshadow' ),
					'description_short'  => __( 'Stop connecting early to WordPress.org', 'wpshadow' ),
					'description_long'   => __( 'Removes DNS prefetch hints for s.w.org, which WordPress automatically adds. DNS prefetch tells browsers to start connecting to WordPress.org in advance, assuming your site will load resources from there. This creates unnecessary connections to external domains. Disabling it improves performance and privacy by reducing external dependencies.', 'wpshadow' ),
					'description_wizard' => __( 'WordPress tries to pre-connect to its own servers for resources you might not use. Remove this connection hint to improve performance and privacy.', 'wpshadow' ),
					'default_enabled'    => true,
				),
			),
		) );

		$this->register_default_settings( array(
			'disable_interactivity_api' => true,
			'disable_block_bindings'    => true,
			'remove_dns_prefetch'       => true,
		) );
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( $this->is_sub_feature_enabled( 'disable_interactivity_api', true ) || $this->is_sub_feature_enabled( 'disable_block_bindings', true ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'disable_interactivity_api' ), 100 );
		}

		if ( $this->is_sub_feature_enabled( 'remove_dns_prefetch', true ) ) {
			add_filter( 'wp_resource_hints', array( $this, 'remove_dns_prefetch' ), 10, 2 );
		}

		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'wpshadow interactivity-cleanup', array( $this, 'handle_cli_command' ) );
		}
	}

	/**
	 * Disable Interactivity API and Block Bindings.
	 */
	public function disable_interactivity_api(): void {
		if ( is_admin() ) {
			return;
		}

		global $post;
		$has_interactive = false;

		if ( isset( $post ) && $post instanceof \WP_Post && has_blocks( $post->ID ) ) {
			// Check for interactive blocks
			$interactive_blocks = array(
				'core/query', 'core/buttons', 'core/navigation', 'core/file',
				'core/calendar', 'core/rss', 'core/search', 'core/comments-form',
			);

			$blocks = parse_blocks( $post->post_content );
			$has_interactive = $this->check_blocks_recursive( $blocks, $interactive_blocks );
		}

		if ( ! $has_interactive && $this->is_sub_feature_enabled( 'disable_interactivity_api', true ) ) {
			wp_dequeue_script( 'wp-interactivity' );
			wp_dequeue_script( 'wp-interactivity-data' );
			do_action( 'wpshadow_interactivity_cleanup_disabled_api' );
		}

		if ( ! preg_match( '/metadata":\s*{[^}]*"bindings"/', $post->post_content ?? '' ) && $this->is_sub_feature_enabled( 'disable_block_bindings', true ) ) {
			wp_dequeue_script( 'wp-block-bindings' );
			do_action( 'wpshadow_interactivity_cleanup_disabled_bindings' );
		}
	}

	/**
	 * Check blocks recursively for interactive types.
	 */
	private function check_blocks_recursive( array $blocks, array $types ): bool {
		foreach ( $blocks as $block ) {
			if ( in_array( $block['blockName'] ?? '', $types, true ) ) {
				return true;
			}
			if ( ! empty( $block['innerBlocks'] ) && $this->check_blocks_recursive( $block['innerBlocks'], $types ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Remove DNS prefetch.
	 */
	public function remove_dns_prefetch( array $urls, string $relation_type ): array {
		if ( 'dns-prefetch' !== $relation_type ) {
			return $urls;
		}

		$filtered = array_filter(
			$urls,
			function ( $url ) {
				return ! ( is_string( $url ) && str_contains( $url, 's.w.org' ) );
			}
		);

		do_action( 'wpshadow_interactivity_cleanup_dns_prefetch', $filtered );

		return $filtered;
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['interactivity_cleanup'] = array(
			'label'  => __( 'Interactivity API Cleanup', 'wpshadow' ),
			'test'   => array( $this, 'test_interactivity' ),
		);

		return $tests;
	}

	public function test_interactivity(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Interactivity API Cleanup', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
				'description' => __( 'Enable Interactivity API cleanup for performance.', 'wpshadow' ),
				'actions'     => '',
				'test'        => 'interactivity_cleanup',
			);
		}

		$enabled_count = 0;
		$subs = array( 'disable_interactivity_api', 'disable_block_bindings', 'remove_dns_prefetch', 'conditional_loading' );
		foreach ( $subs as $sub ) {
			if ( $this->is_sub_feature_enabled( $sub, false ) ) {
				$enabled_count++;
			}
		}

		return array(
			'label'       => __( 'Interactivity API Cleanup', 'wpshadow' ),
			'status'      => $enabled_count >= 2 ? 'good' : 'recommended',
			'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
			'description' => sprintf(
				__( '%d of 4 cleanup features enabled.', 'wpshadow' ),
				$enabled_count
			),
			'actions'     => '',
			'test'        => 'interactivity_cleanup',
		);
	}

	/**
	 * Handle WP-CLI command for interactivity cleanup.
	 *
	 * @param array $args       Positional args.
	 * @param array $assoc_args Named args (unused).
	 *
	 * @return void
	 */
	public function handle_cli_command( array $args, array $assoc_args ): void {
		$action = $args[0] ?? 'status';

		if ( 'status' !== $action ) {
			\WP_CLI::error( __( 'Unknown subcommand. Try: wp wpshadow interactivity-cleanup status', 'wpshadow' ) );
			return;
		}

		\WP_CLI::log( __( 'Interactivity Cleanup status:', 'wpshadow' ) );
		\WP_CLI::log( sprintf( '  %s: %s', __( 'Feature enabled', 'wpshadow' ), $this->is_enabled() ? 'yes' : 'no' ) );

		$subs = array(
			'disable_interactivity_api',
			'disable_block_bindings',
			'remove_dns_prefetch',
		);

		foreach ( $subs as $sub ) {
			$enabled = $this->is_sub_feature_enabled( $sub, false );
			\WP_CLI::log( sprintf( '  - %s: %s', $sub, $enabled ? 'on' : 'off' ) );
		}

		\WP_CLI::success( __( 'Interactivity cleanup inspected.', 'wpshadow' ) );
	}
}
