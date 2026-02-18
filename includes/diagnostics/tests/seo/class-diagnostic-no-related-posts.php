<?php
/**
 * Diagnostic: Missing Related Posts
 *
 * Detects missing related posts section, a missed engagement opportunity
 * that can increase pages per session by 40-50%.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.7030.1450
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * No Related Posts Diagnostic Class
 *
 * Checks for related posts functionality via plugins or theme features.
 *
 * Detection methods:
 * - Related posts plugin detection
 * - Theme support for related posts
 * - Related posts in content
 *
 * @since 1.7030.1450
 */
class Diagnostic_No_Related_Posts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-related-posts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Related Posts';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'No related posts section = missed engagement opportunity';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-engagement';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (3 points):
	 * - 2 points: Related posts plugin active
	 * - 1 point: Theme has related posts support
	 *
	 * @since  1.7030.1450
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score           = 0;
		$max_score       = 3;
		$has_plugin      = false;
		$has_theme_support = false;
		$active_plugin   = '';

		// Check for related posts plugins.
		$related_plugins = array(
			'yet-another-related-posts-plugin/yarpp.php' => 'YARPP',
			'contextual-related-posts/contextual-related-posts.php' => 'Contextual Related Posts',
			'related-posts-for-wp/related-posts-for-wp.php' => 'Related Posts for WordPress',
			'jetpack/jetpack.php'                        => 'Jetpack Related Posts',
			'wordpress-popular-posts/wordpress-popular-posts.php' => 'WordPress Popular Posts',
		);

		foreach ( $related_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$score        += 2;
				$has_plugin    = true;
				$active_plugin = $name;
				break;
			}
		}

		// Check if theme has related posts support.
		$theme = wp_get_theme();
		$theme_name = $theme->get( 'Name' );

		// Many popular themes have built-in related posts.
		$themes_with_related = array( 'Astra', 'GeneratePress', 'OceanWP', 'Neve', 'Kadence' );
		foreach ( $themes_with_related as $theme_check ) {
			if ( stripos( $theme_name, $theme_check ) !== false ) {
				$score             += 1;
				$has_theme_support  = true;
				break;
			}
		}

		// Check a sample post for related posts in content/footer.
		$sample_post = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 1,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( ! empty( $sample_post ) ) {
			setup_postdata( $sample_post[0] );
			ob_start();
			comments_template();
			$template_output = ob_get_clean();
			wp_reset_postdata();

			// Check for related posts patterns.
			$related_patterns = array( 'related', 'you may also like', 'similar posts', 'recommended' );
			foreach ( $related_patterns as $pattern ) {
				if ( stripos( $template_output, $pattern ) !== false ) {
					$score             += 1;
					$has_theme_support  = true;
					break;
				}
			}
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.7 ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __( 'No related posts functionality detected. Related posts increase pages per session by 40-50%, reduce bounce rate by 20%, and boost SEO via internal linking. They keep readers engaged after finishing an article, discover more content, and improve dwell time (ranking factor). Best practices: Show 3-6 related posts, use thumbnails, match by category/tags (not just recent), place at end of content (not sidebar where often ignored). Algorithm: YARPP uses full-text analysis, others use tags/categories. Jetpack uses both.', 'wpshadow' ),
			'severity'    => 'medium',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/missing-related-posts',
			'stats'       => array(
				'has_plugin'        => $has_plugin,
				'active_plugin'     => $active_plugin,
				'has_theme_support' => $has_theme_support,
				'theme_name'        => $theme_name,
			),
			'recommendation' => __( 'Install YARPP or Contextual Related Posts plugin. Configure to show 4-6 posts with thumbnails. Place at end of content. Match by tags and categories. Test: Does clicking related post feel natural? Are suggestions relevant?', 'wpshadow' ),
		);
	}
}
