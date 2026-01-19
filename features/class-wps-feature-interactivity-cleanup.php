<?php declare(strict_types=1);
/**
 * Feature: Interactivity API & DNS Prefetch Cleanup
 *
 * Disable Interactivity API, Block Bindings, and emoji DNS prefetch on pages that do not use them.
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
 * Remove Interactivity API and DNS prefetch bloat.
 */
final class WPSHADOW_Feature_Interactivity_Cleanup extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'interactivity-cleanup',
				'name'               => __( 'Interactivity API & DNS Cleanup', 'wpshadow' ),
				'description'        => __( "Stop loading interaction code you don't use - speed up pages with smart cleanup.", 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-performance',
				'category'           => 'performance',
				'priority'           => 20,
				'sub_features'       => array(
					'disable_interactivity_api' => array(
						'name'            => __( 'Disable Interactivity API', 'wpshadow' ),
						'description'     => __( 'Dequeue Interactivity API scripts on pages that do not use interactive blocks.', 'wpshadow' ),
						'default_enabled' => true,
					),
					'disable_block_bindings'    => array(
						'name'            => __( 'Disable Block Bindings', 'wpshadow' ),
						'description'     => __( 'Skip block bindings script on pages that do not need block bindings.', 'wpshadow' ),
						'default_enabled' => true,
					),
					'remove_dns_prefetch'       => array(
						'name'            => __( 'Remove s.w.org DNS Prefetch', 'wpshadow' ),
						'description'     => __( 'Remove emoji CDN DNS prefetch to avoid unnecessary connections.', 'wpshadow' ),
						'default_enabled' => true,
					),
					'conditional_loading'       => array(
						'name'            => __( 'Conditional Script Loading', 'wpshadow' ),
						'description'     => __( 'Only dequeue scripts when no interactive or binding blocks are present.', 'wpshadow' ),
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

		if ( $this->is_sub_feature_enabled( 'disable_interactivity_api', true ) || $this->is_sub_feature_enabled( 'disable_block_bindings', true ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'handle_interactivity_cleanup' ), 100 );
		}

		if ( $this->is_sub_feature_enabled( 'remove_dns_prefetch', true ) ) {
			add_filter( 'wp_resource_hints', array( $this, 'remove_dns_prefetch' ), 10, 2 );
		}

		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Disable Interactivity API and block bindings when not needed.
	 */
	public function handle_interactivity_cleanup(): void {
		if ( is_admin() ) {
			return;
		}

		$conditional_loading = $this->is_sub_feature_enabled( 'conditional_loading', true );

		if ( $this->is_sub_feature_enabled( 'disable_interactivity_api', true ) ) {
			$should_remove = true;

			if ( $conditional_loading ) {
				$should_remove = ! $this->has_interactive_blocks();
			}

			if ( $should_remove ) {
				wp_dequeue_script( 'wp-interactivity' );
				wp_dequeue_script( 'wp-interactivity-data' );
			}
		}

		if ( $this->is_sub_feature_enabled( 'disable_block_bindings', true ) ) {
			$should_remove_bindings = true;

			if ( $conditional_loading ) {
				$should_remove_bindings = ! $this->has_block_bindings();
			}

			if ( $should_remove_bindings ) {
				wp_dequeue_script( 'wp-block-bindings' );
			}
		}
	}

	/**
	 * Remove s.w.org DNS prefetch (emoji-related).
	 *
	 * @param array<int, string> $urls          URLs to preconnect/prefetch.
	 * @param string             $relation_type Type of relation (dns-prefetch, preconnect, prefetch, prerender).
	 * @return array<int, string>
	 */
	public function remove_dns_prefetch( array $urls, string $relation_type ): array {
		if ( 'dns-prefetch' === $relation_type ) {
			$urls = array_values(
				array_filter(
					$urls,
					static function ( $url ): bool {
						return ! ( is_string( $url ) && str_contains( $url, 's.w.org' ) );
					}
				)
			);
		}

		return $urls;
	}

	/**
	 * Check if current page has interactive blocks.
	 */
	private function has_interactive_blocks(): bool {
		global $post;

		if ( ! isset( $post ) || ! $post instanceof \WP_Post ) {
			return false;
		}

		if ( ! has_blocks( $post->ID ) ) {
			return false;
		}

		$blocks = parse_blocks( $post->post_content );

		return $this->check_blocks_recursive( $blocks );
	}

	/**
	 * Recursively check blocks for interactive ones.
	 *
	 * @param array<int, array<string, mixed>> $blocks Blocks to check.
	 */
	private function check_blocks_recursive( array $blocks ): bool {
		foreach ( $blocks as $block ) {
			$block_name = $block['blockName'] ?? '';

			if ( is_string( $block_name ) ) {
				$interactive_blocks = array(
					'core/query',
					'core/buttons',
					'core/navigation',
					'core/file',
					'core/calendar',
					'core/rss',
					'core/search',
					'core/comments-form',
				);

				if ( in_array( $block_name, $interactive_blocks, true ) ) {
					return true;
				}
			}

			$inner_blocks = $block['innerBlocks'] ?? array();
			if ( is_array( $inner_blocks ) && $this->check_blocks_recursive( $inner_blocks ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if page has block bindings.
	 */
	private function has_block_bindings(): bool {
		global $post;

		if ( ! isset( $post ) || ! $post instanceof \WP_Post ) {
			return false;
		}

		if ( ! has_blocks( $post->ID ) ) {
			return false;
		}

		return (bool) preg_match( '/metadata":\s*{[^}]*"bindings"/', $post->post_content );
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array<string, mixed> $tests Array of Site Health tests.
	 * @return array<string, mixed>
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['interactivity_cleanup'] = array(
			'label' => __( 'Interactivity API Cleanup', 'wpshadow' ),
			'test'  => array( $this, 'test_interactivity_cleanup' ),
		);

		return $tests;
	}

	/**
	 * Site Health test for interactivity cleanup.
	 *
	 * @return array<string, mixed>
	 */
	public function test_interactivity_cleanup(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Interactivity API Cleanup', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'wpshadow' ),
					'color' => 'gray',
				),
				'description' => sprintf( '<p>%s</p>', __( 'Interactivity cleanup is not enabled. Disabling unused scripts can reduce page weight.', 'wpshadow' ) ),
				'actions'     => '',
				'test'        => 'interactivity_cleanup',
			);
		}

		$enabled = 0;
		$enabled += $this->is_sub_feature_enabled( 'disable_interactivity_api', true ) ? 1 : 0;
		$enabled += $this->is_sub_feature_enabled( 'disable_block_bindings', true ) ? 1 : 0;
		$enabled += $this->is_sub_feature_enabled( 'remove_dns_prefetch', true ) ? 1 : 0;
		$enabled += $this->is_sub_feature_enabled( 'conditional_loading', true ) ? 1 : 0;

		$status = $enabled >= 3 ? 'good' : 'recommended';

		return array(
			'label'       => __( 'Interactivity API Cleanup', 'wpshadow' ),
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Performance', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: %d: number of enabled cleanup features */
					__( '%d interactivity cleanup features are enabled, reducing unnecessary scripts.', 'wpshadow' ),
					$enabled
				)
			),
			'actions'     => '',
			'test'        => 'interactivity_cleanup',
		);
	}
}
