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

final class WPSHADOW_Feature_Interactivity_Cleanup extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct( array(
			'id'          => 'interactivity-cleanup',
			'name'        => __( 'Interactivity API & DNS Cleanup', 'wpshadow' ),
			'description' => __( 'Disable unused Interactivity API and Block Bindings.', 'wpshadow' ),
			'sub_features' => array(
				'disable_interactivity_api' => __( 'Disable Interactivity API', 'wpshadow' ),
				'disable_block_bindings'    => __( 'Disable Block Bindings', 'wpshadow' ),
				'remove_dns_prefetch'       => __( 'Remove s.w.org DNS', 'wpshadow' ),
				'conditional_loading'       => __( 'Conditional Loading', 'wpshadow' ),
			),
		) );

		$this->register_default_settings( array(
			'disable_interactivity_api' => true,
			'disable_block_bindings'    => true,
			'remove_dns_prefetch'       => true,
			'conditional_loading'       => true,
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
		}

		if ( ! preg_match( '/metadata":\s*{[^}]*"bindings"/', $post->post_content ?? '' ) && $this->is_sub_feature_enabled( 'disable_block_bindings', true ) ) {
			wp_dequeue_script( 'wp-block-bindings' );
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

		return array_filter(
			$urls,
			function ( $url ) {
				return ! ( is_string( $url ) && str_contains( $url, 's.w.org' ) );
			}
		);
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
}
