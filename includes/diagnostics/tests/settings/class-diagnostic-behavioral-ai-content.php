<?php
/**
 * Diagnostic: AI Content Assistance
 *
 * Tests whether the site uses AI tools to augment content creation and
 * increase publishing output by 200-300%.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4552
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Behavioral
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AI Content Assistance Diagnostic
 *
 * Checks for AI content creation tools. AI assistance accelerates research,
 * outlining, and drafting - increasing content output 2-3x while maintaining quality.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Behavioral_AI_Content extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'uses-ai-content-assistance';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'AI Content Assistance';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site uses AI tools to augment content creation';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for AI content tools implementation.
	 *
	 * Looks for AI writing assistant plugins.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if missing, null if present.
	 */
	public static function check() {
		// Check for AI content plugins.
		$ai_content_plugins = array(
			'ai-engine/ai-engine.php'                        => 'AI Engine',
			'wpai/wpai.php'                                  => 'WPAI',
		);

		foreach ( $ai_content_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return null; // Has AI content tools.
			}
		}

		// Check publishing volume (proxy for AI usage).
		$recent_posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 100,
				'post_status'    => 'publish',
				'date_query'     => array(
					array(
						'after' => '6 months ago',
					),
				),
			)
		);

		// High volume sites likely using AI already.
		if ( count( $recent_posts ) > 50 ) {
			// 50+ posts in 6 months = ~2/week, likely has workflow.
			return null;
		}

		// Only recommend for content-focused sites.
		$is_content_site = false;
		
		// Check if blog is primary focus.
		$total_posts = wp_count_posts( 'post' )->publish;
		$total_pages = wp_count_posts( 'page' )->publish;
		
		if ( $total_posts > $total_pages ) {
			$is_content_site = true;
		}

		// Check for news/magazine themes.
		$theme = wp_get_theme();
		$theme_name = strtolower( $theme->get( 'Name' ) );
		$content_keywords = array( 'news', 'magazine', 'blog', 'journal' );
		
		foreach ( $content_keywords as $keyword ) {
			if ( strpos( $theme_name, $keyword ) !== false ) {
				$is_content_site = true;
				break;
			}
		}

		if ( ! $is_content_site ) {
			return null; // Not content-focused.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'No AI content assistance detected. AI writing tools accelerate research, outlining, and first drafts - increasing output 2-3x. AI doesn\'t replace human creativity but handles tedious tasks: research summaries, outline generation, SEO optimization, headline variations. Consider AI tools (ChatGPT, Claude, AI Engine plugin) to augment human writers, not replace them.',
				'wpshadow'
			),
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/ai-content-assistance',
		);
	}
}
