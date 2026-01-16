<?php
/**
 * Feature: Interactivity API & DNS Prefetch Cleanup
 *
 * Disable unnecessary Interactivity API and Block Bindings scripts on pages that don't use them.
 * Also removes DNS prefetch for s.w.org (emoji CDN) since emojis should be disabled separately.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_Interactivity_Cleanup
 *
 * Remove Interactivity API and DNS prefetch bloat.
 */
final class WPSHADOW_Feature_Interactivity_Cleanup extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'interactivity-cleanup',
				'name'               => __( 'Interactivity API & DNS Cleanup', 'plugin-wpshadow' ),
				'description'        => __( 'Prevents Interactivity API and Block Bindings scripts from loading on pages that do not use them, and cleans unnecessary DNS prefetch entries, reducing requests and script execution. Keeps required scripts where features depend on them so functionality stays intact. Helps lower page weight, reduce potential security surface area, and improve speed scores with minimal setup for sites that rely mostly on traditional interactions.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-performance',
				'category'           => 'performance',
				'priority'           => 20,
			)
		);
		
		if ( method_exists( $this, 'register_sub_features' ) ) {
			$this->register_sub_features(
				array(
					'disable_interactivity_api' => __( 'Disable Interactivity API', 'plugin-wpshadow' ),
					'disable_block_bindings'    => __( 'Disable Block Bindings', 'plugin-wpshadow' ),
					'remove_dns_prefetch'       => __( 'Remove s.w.org DNS Prefetch', 'plugin-wpshadow' ),
					'conditional_loading'       => __( 'Conditional Script Loading', 'plugin-wpshadow' ),
				)
			);
			if ( method_exists( $this, 'set_default_sub_features' ) ) {
				$this->set_default_sub_features(
					array(
						'disable_interactivity_api' => true,
						'disable_block_bindings'    => true,
						'remove_dns_prefetch'       => true,
						'conditional_loading'       => true,
					)
				);
			}
		}
		
		$this->log_activity( 'feature_initialized', 'Interactivity Cleanup feature initialized', 'info' );
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

		// Remove Interactivity API on frontend if not needed.
		if ( get_option( 'wpshadow_interactivity-cleanup_disable_interactivity_api', true ) || get_option( 'wpshadow_interactivity-cleanup_disable_block_bindings', true ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'disable_interactivity_api' ), 100 );
		}

		// Remove DNS prefetch.
		if ( get_option( 'wpshadow_interactivity-cleanup_remove_dns_prefetch', true ) ) {
			add_filter( 'wp_resource_hints', array( $this, 'remove_dns_prefetch' ), 10, 2 );
		}
		
		// Add Site Health tests.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Disable Interactivity API and Block Bindings on frontend when not needed.
	 *
	 * @return void
	 */
	public function disable_interactivity_api(): void {
		if ( is_admin() ) {
			return;
		}

		// Check if any blocks using interactivity are on this page.
		$has_interactive_blocks = $this->has_interactive_blocks();

		if ( ! $has_interactive_blocks ) {
			// Remove Interactivity API script.
			wp_dequeue_script( 'wp-interactivity' );
			wp_dequeue_script( 'wp-interactivity-data' );
		}

		// Remove Block Bindings if no blocks need them.
		if ( ! $this->has_block_bindings() ) {
			wp_dequeue_script( 'wp-block-bindings' );
		}
	}

	/**
	 * Remove DNS prefetch for s.w.org (emoji CDN).
	 *
	 * @param array    $urls           URLs to preconnect/prefetch.
	 * @param string   $relation_type  Type of relation (dns-prefetch, preconnect, prefetch, prerender).
	 * @return array Filtered URLs.
	 */
	public function remove_dns_prefetch( array $urls, string $relation_type ): array {
		if ( 'dns-prefetch' === $relation_type ) {
			// Remove s.w.org DNS prefetch (emoji-related).
			$urls = array_filter(
				$urls,
				function ( $url ) {
					return ! str_contains( $url, 's.w.org' );
				}
			);
		}

		return $urls;
	}

	/**
	 * Check if current page has interactive blocks.
	 *
	 * @return bool True if page has interactive blocks.
	 */
	private function has_interactive_blocks(): bool {
		global $post;

		if ( ! isset( $post ) || ! $post instanceof \WP_Post ) {
			return false;
		}

		// Check post content for interactive blocks.
		if ( ! has_blocks( $post->ID ) ) {
			return false;
		}

		$blocks = parse_blocks( $post->post_content );

		return $this->check_blocks_recursive( $blocks );
	}

	/**
	 * Recursively check blocks for interactive ones.
	 *
	 * @param array $blocks Blocks to check.
	 * @return bool True if any interactive blocks found.
	 */
	private function check_blocks_recursive( array $blocks ): bool {
		foreach ( $blocks as $block ) {
			// List of interactive block types.
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

			if ( in_array( $block['blockName'], $interactive_blocks, true ) ) {
				return true;
			}

			// Check inner blocks recursively.
			if ( ! empty( $block['innerBlocks'] ) && $this->check_blocks_recursive( $block['innerBlocks'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if page has block bindings.
	 *
	 * @return bool True if page has block bindings.
	 */
	private function has_block_bindings(): bool {
		global $post;

		if ( ! isset( $post ) || ! $post instanceof \WP_Post ) {
			return false;
		}

		if ( ! has_blocks( $post->ID ) ) {
			return false;
		}

		// Check for blocks with bindings in content.
		return (bool) preg_match( '/metadata":\s*{[^}]*"bindings"/', $post->post_content );
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array $tests Array of Site Health tests.
	 * @return array Modified tests array.
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['interactivity_cleanup'] = array(
			'label' => __( 'Interactivity API Cleanup', 'plugin-wpshadow' ),
			'test'  => array( $this, 'test_interactivity_cleanup' ),
		);

		return $tests;
	}

	/**
	 * Site Health test for interactivity cleanup.
	 *
	 * @return array Test result.
	 */
	public function test_interactivity_cleanup(): array {
		$enabled_features = 0;

		if ( get_option( 'wpshadow_interactivity-cleanup_disable_interactivity_api', true ) ) {
			$enabled_features++;
		}
		if ( get_option( 'wpshadow_interactivity-cleanup_disable_block_bindings', true ) ) {
			$enabled_features++;
		}
		if ( get_option( 'wpshadow_interactivity-cleanup_remove_dns_prefetch', true ) ) {
			$enabled_features++;
		}

		$status = $enabled_features >= 2 ? 'good' : 'recommended';
		$label  = $enabled_features >= 2 ?
			__( 'Interactivity cleanup is active', 'plugin-wpshadow' ) :
			__( 'Interactivity cleanup could be improved', 'plugin-wpshadow' );

		return array(
			'label'       => $label,
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Performance', 'plugin-wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: %d: number of enabled cleanup features */
					__( '%d interactivity cleanup features are enabled, reducing unnecessary scripts.', 'plugin-wpshadow' ),
					$enabled_features
				)
			),
			'actions'     => '',
			'test'        => 'interactivity_cleanup',
		);
	}
}
