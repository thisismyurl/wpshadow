<?php
/**
 * Feature: Interactivity API & DNS Prefetch Cleanup
 *
 * Disable unnecessary Interactivity API and Block Bindings scripts on pages that don't use them.
 * Also removes DNS prefetch for s.w.org (emoji CDN) since emojis should be disabled separately.
 *
 * @package WPS\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

/**
 * WPS_Feature_Interactivity_Cleanup
 *
 * Remove Interactivity API and DNS prefetch bloat.
 */
final class WPS_Feature_Interactivity_Cleanup extends WPS_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'interactivity-cleanup',
				'name'               => __( 'Interactivity API & DNS Cleanup', 'plugin-wp-support-thisismyurl' ),
				'description'        => __( 'Disable Interactivity API and Block Bindings scripts on pages that don\'t need them; remove unnecessary DNS prefetch', 'plugin-wp-support-thisismyurl' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'widget_label'       => __( 'Performance & Security', 'plugin-wp-support-thisismyurl' ),
				'widget_description' => __( 'Remove bloat and unnecessary scripts that impact security and page speed', 'plugin-wp-support-thisismyurl' ),
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

		// Remove Interactivity API on frontend if not needed.
		add_action( 'wp_enqueue_scripts', array( $this, 'disable_interactivity_api' ), 100 );

		// Remove DNS prefetch.
		add_filter( 'wp_resource_hints', array( $this, 'remove_dns_prefetch' ), 10, 2 );
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
}
