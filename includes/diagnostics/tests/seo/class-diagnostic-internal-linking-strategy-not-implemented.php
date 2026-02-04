<?php
/**
 * Internal Linking Strategy Not Implemented Diagnostic
 *
 * Checks if internal linking is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Internal Linking Strategy Not Implemented Diagnostic Class
 *
 * Detects missing internal linking strategy.
 *
 * @since 1.6030.2352
 */
class Diagnostic_Internal_Linking_Strategy_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'internal-linking-strategy-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Internal Linking Strategy Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if internal linking is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for internal linking plugins.
		$linking_plugins = array(
			'yet-another-related-posts-plugin/yarpp.php' => 'YARPP',
			'related-posts-by-taxonomy/related-posts-by-taxonomy.php' => 'Related Posts by Taxonomy',
			'internal-links/internal-links.php'          => 'Internal Links',
			'link-whisper/link-whisper.php'              => 'Link Whisper',
			'wordpress-seo/wp-seo.php'                   => 'Yoast SEO',
			'wordpress-seo-premium/wp-seo-premium.php'   => 'Yoast SEO Premium',
		);

		$plugin_detected = false;
		$plugin_name     = '';

		foreach ( $linking_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$plugin_detected = true;
				$plugin_name     = $name;
				break;
			}
		}

		// Sample recent posts to check for internal links.
		$recent_posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 10,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$posts_with_links = 0;
		$total_internal_links = 0;
		$site_url = site_url();

		foreach ( $recent_posts as $post ) {
			$content = $post->post_content;
			
			// Count internal links in post content.
			preg_match_all( '/<a[^>]+href=["\'](' . preg_quote( $site_url, '/' ) . '[^"\']*)["\'][^>]*>/i', $content, $matches );
			
			if ( ! empty( $matches[0] ) ) {
				$link_count = count( $matches[0] );
				if ( $link_count > 0 ) {
					$posts_with_links++;
					$total_internal_links += $link_count;
				}
			}
		}

		$posts_checked = count( $recent_posts );
		$avg_links = $posts_checked > 0 ? round( $total_internal_links / $posts_checked, 1 ) : 0;
		$link_percentage = $posts_checked > 0 ? round( ( $posts_with_links / $posts_checked ) * 100 ) : 0;

		// Check for related posts widget/shortcode.
		$has_related_posts = has_action( 'wp_footer' ) && ( 
			has_shortcode( get_post_field( 'post_content', get_option( 'page_on_front' ) ), 'related' ) ||
			has_shortcode( get_post_field( 'post_content', get_option( 'page_on_front' ) ), 'yarpp' )
		);

		// Critical: No plugin and very few internal links.
		if ( ! $plugin_detected && $avg_links < 2 && $link_percentage < 50 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: average links per post, 2: percentage of posts with links */
					__( 'Internal linking strategy not implemented. Recent posts average only %1$s internal links. Only %2$d%% of posts contain internal links. Internal linking distributes page authority, improves navigation, and helps search engines discover content.', 'wpshadow' ),
					number_format_i18n( $avg_links, 1 ),
					$link_percentage
				),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/internal-linking-strategy',
				'details'     => array(
					'posts_checked'        => $posts_checked,
					'posts_with_links'     => $posts_with_links,
					'total_internal_links' => $total_internal_links,
					'avg_links_per_post'   => $avg_links,
					'link_percentage'      => $link_percentage,
					'plugin_detected'      => false,
					'recommendation'       => __( 'Install Link Whisper (premium, automated) or YARPP (free, related posts). Aim for 3-5 contextual internal links per post. Link to pillar content from supporting articles.', 'wpshadow' ),
					'seo_benefits'         => array(
						'page_authority'  => 'Distributes link equity across site',
						'crawlability'    => 'Helps search engines discover deep content',
						'user_engagement' => 'Keeps visitors on site longer',
						'context'         => 'Shows topical relationships',
					),
					'best_practices'       => array(
						'3-5 links per post',
						'Use descriptive anchor text',
						'Link to related, relevant content',
						'Create hub pages (pillar content)',
						'Link new posts to older authority content',
					),
				),
			);
		}

		// Low: Some linking but could be improved.
		if ( $avg_links >= 2 && $avg_links < 4 ) {
			return array(
				'id'          => self::$slug,
				'title'       => __( 'Internal Linking Could Be Improved', 'wpshadow' ),
				'description' => sprintf(
					/* translators: %s: average links per post */
					__( 'Average %s internal links per post. Good start, but aim for 3-5 contextual links for better SEO and user experience.', 'wpshadow' ),
					number_format_i18n( $avg_links, 1 )
				),
				'severity'    => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/internal-linking-strategy',
				'details'     => array(
					'avg_links_per_post' => $avg_links,
					'recommendation'     => __( 'Review older posts and add contextual internal links. Use Link Whisper for automated suggestions.', 'wpshadow' ),
				),
			);
		}

		// No issues - good internal linking.
		return null;
	}
}
