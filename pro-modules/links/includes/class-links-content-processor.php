<?php
/**
 * Links Content Processor
 *
 * Processes post content and injects managed links, handling affiliate links intelligently.
 *
 * @package WPShadow
 * @subpackage Links
 */

declare(strict_types=1);

namespace WPShadow\Links;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Links_Content_Processor class.
 */
class Links_Content_Processor {
	/**
	 * Initialize content processor.
	 */
	public static function init(): void {
		add_filter( 'the_content', [ __CLASS__, 'process_content' ], 15 );
		add_filter( 'wp_footer', [ __CLASS__, 'add_affiliate_disclosure' ], 99 );
	}

	/**
	 * Process post content and inject managed links.
	 *
	 * @param string $content The post content.
	 * @return string
	 */
	public static function process_content( $content ): string {
		if ( is_admin() || is_feed() || ! is_singular() ) {
			return $content;
		}

		// Get all enabled links
		$links = self::get_enabled_links();
		if ( empty( $links ) ) {
			return $content;
		}

		// Store current post ID in transient for disclosure footer
		set_transient( 'wpshadow_current_post_has_affiliates', false, HOUR_IN_SECONDS );

		// Process each link
		foreach ( $links as $link ) {
			$content = self::inject_link( $content, $link );

			// Mark if this post has affiliates
			if ( '1' === $link['is_affiliate'] ) {
				set_transient( 'wpshadow_current_post_has_affiliates', true, HOUR_IN_SECONDS );
			}
		}

		return $content;
	}

	/**
	 * Get all enabled managed links.
	 *
	 * @return array
	 */
	private static function get_enabled_links(): array {
		// Check cache first
		$cache_key = 'wpshadow_links_cache';
		$cached     = wp_cache_get( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$args = [
			'post_type'      => 'wpshadow_link',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_query'     => [
				[
					'key'   => 'wpshadow_link_enabled',
					'value' => '1',
				],
			],
		];

		$query = new \WP_Query( $args );
		$links = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();

				$url           = get_post_meta( $post_id, 'wpshadow_link_url', true );
				$text          = get_post_meta( $post_id, 'wpshadow_link_text', true );
				$is_affiliate  = get_post_meta( $post_id, 'wpshadow_link_is_affiliate', true );
				$open_blank    = get_post_meta( $post_id, 'wpshadow_link_open_blank', true );
				$nofollow      = get_post_meta( $post_id, 'wpshadow_link_nofollow', true );
				$affiliate_text = get_post_meta( $post_id, 'wpshadow_link_affiliate_text', true );

				if ( ! empty( $url ) && ! empty( $text ) ) {
					$links[] = [
						'text'            => $text,
						'url'             => $url,
						'post_id'         => $post_id,
						'is_affiliate'    => $is_affiliate,
						'open_blank'      => $open_blank,
						'nofollow'        => $nofollow,
						'affiliate_text'  => $affiliate_text,
					];
				}
			}
			wp_reset_postdata();
		}

		// Cache for 1 hour
		wp_cache_set( $cache_key, $links, '', 3600 );

		return $links;
	}

	/**
	 * Inject a single managed link into content.
	 *
	 * @param string $content The content.
	 * @param array  $link    The link data.
	 * @return string
	 */
	private static function inject_link( $content, $link ): string {
		$search_text = $link['text'];
		$url         = $link['url'];
		$post_id     = $link['post_id'];

		// Don't replace if already a link
		$pattern = sprintf(
			'#<a[^>]*>%s</a>#i',
			preg_quote( $search_text, '#' )
		);
		if ( preg_match( $pattern, $content ) ) {
			return $content;
		}

		// Build link attributes
		$rel_attrs = [];
		if ( '1' === $link['nofollow'] ) {
			$rel_attrs[] = 'nofollow';
		}
		if ( '1' === $link['is_affiliate'] ) {
			$rel_attrs[] = 'sponsored';
		}

		$rel_attr = ! empty( $rel_attrs ) ? ' rel="' . implode( ' ', $rel_attrs ) . '"' : '';
		$target    = '1' === $link['open_blank'] ? ' target="_blank"' : '';

		$replacement = sprintf(
			'<a href="%s" class="wpshadow-managed-link" data-link-id="%d"%s%s>%s</a>',
			esc_attr( $url ),
			intval( $post_id ),
			$target,
			$rel_attr,
			esc_html( $search_text )
		);

		// Replace text with link (word boundaries)
		$pattern = '/\b' . preg_quote( $search_text, '/' ) . '\b/';
		$content = preg_replace_callback(
			$pattern,
			function() use ( $replacement ) {
				return $replacement;
			},
			$content,
			-1 // Replace all occurrences
		);

		return $content;
	}

	/**
	 * Add affiliate disclosure footer if page has affiliate links.
	 */
	public static function add_affiliate_disclosure(): void {
		if ( is_admin() || ! is_singular() ) {
			return;
		}

		if ( ! get_transient( 'wpshadow_current_post_has_affiliates' ) ) {
			return;
		}

		$disclosure = get_option( 'wpshadow_links_affiliate_disclosure', self::get_default_disclosure() );
		if ( empty( $disclosure ) ) {
			return;
		}

		?>
		<div class="wpshadow-affiliate-disclosure">
			<?php echo wp_kses_post( $disclosure ); ?>
		</div>
		<?php
	}

	/**
	 * Get default affiliate disclosure text.
	 *
	 * @return string
	 */
	private static function get_default_disclosure(): string {
		return sprintf(
			/* translators: %s: Site name */
			__( '<strong>Affiliate Disclosure:</strong> This page contains affiliate links. %s may earn a commission when you click through and make a purchase. This does not affect the price you pay.', 'wpshadow' ),
			get_bloginfo( 'name' )
		);
	}
}
