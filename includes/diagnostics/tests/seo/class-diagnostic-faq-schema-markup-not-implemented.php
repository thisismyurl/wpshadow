<?php
/**
 * FAQ Schema Markup Not Implemented Diagnostic
 *
 * Checks if FAQ schema is implemented.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * FAQ Schema Markup Not Implemented Diagnostic Class
 *
 * Detects missing FAQ schema.
 *
 * @since 1.6093.1200
 */
class Diagnostic_FAQ_Schema_Markup_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'faq-schema-markup-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'FAQ Schema Markup Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if FAQ schema is implemented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for FAQ content in recent posts.
		$recent_posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 20,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$has_faq_content = false;
		foreach ( $recent_posts as $post ) {
			$content = strtolower( $post->post_content );
			// Check for FAQ indicators.
			if ( strpos( $content, 'frequently asked' ) !== false ||
			     strpos( $content, 'faq' ) !== false ||
			     ( strpos( $content, 'question' ) !== false && strpos( $content, 'answer' ) !== false ) ) {
				$has_faq_content = true;
				break;
			}
		}

		// If no FAQ content detected, no schema needed.
		if ( ! $has_faq_content ) {
			return null;
		}

		// Check for SEO plugins with FAQ schema support.
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php'              => 'Yoast SEO',
			'seo-by-rank-math/rank-math.php'        => 'Rank Math SEO',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
			'wp-seopress/seopress.php'              => 'SEOPress',
		);

		$seo_plugin_detected = false;
		$seo_plugin_name     = '';

		foreach ( $seo_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$seo_plugin_detected = true;
				$seo_plugin_name     = $name;
				break;
			}
		}

		// Check for Gutenberg FAQ blocks.
		$has_faq_blocks = false;
		if ( function_exists( 'parse_blocks' ) ) {
			foreach ( $recent_posts as $post ) {
				$blocks = parse_blocks( $post->post_content );
				foreach ( $blocks as $block ) {
					if ( strpos( $block['blockName'], 'faq' ) !== false ||
					     strpos( $block['blockName'], 'accordion' ) !== false ) {
						$has_faq_blocks = true;
						break 2;
					}
				}
			}
		}

		// If FAQ content exists but no schema implementation.
		if ( ! $seo_plugin_detected && ! $has_faq_blocks ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'FAQ schema markup not implemented. You have FAQ content but no structured data. Add FAQ schema to enable Google rich results: FAQ accordion appears directly in search results, increasing visibility and click-through rate. Install Yoast SEO or Rank Math for automatic FAQ schema.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/faq-schema-markup',
				'details'     => array(
					'has_faq_content' => true,
					'seo_plugin'      => false,
					'has_faq_blocks'  => false,
					'recommendation'  => __( 'Install Rank Math SEO (free, 1M+ installs) with built-in FAQ block. Add FAQ content using Rank Math FAQ block, schema is added automatically. Alternative: Yoast SEO Premium includes FAQ schema blocks.', 'wpshadow' ),
					'faq_schema_benefits' => array(
						'rich_results' => 'FAQ accordion appears in Google search results',
						'more_space' => 'FAQ results take up more screen space (better visibility)',
						'ctr_boost' => 'Users can read answers without clicking (increases trust)',
						'mobile_friendly' => 'Expandable accordions work great on mobile',
					),
					'example_markup' => array(
						'type' => 'FAQPage',
						'question' => 'What is FAQ schema?',
						'answer' => 'Structured data that displays Q&A in search results',
					),
					'implementation_options' => array(
						'easy' => 'Rank Math SEO (free FAQ block)',
						'premium' => 'Yoast SEO Premium (FAQ blocks)',
						'custom' => 'Manual JSON-LD implementation',
					),
				),
			);
		}

		// No issues - FAQ schema not needed or implemented.
		return null;
	}
}
