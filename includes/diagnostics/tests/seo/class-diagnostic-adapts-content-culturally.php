<?php
/**
 * Cultural Adaptation Diagnostic
 *
 * Tests whether the site adapts content for cultural differences beyond just translation.
 * Cultural adaptation includes imagery, colors, formats, examples, and messaging that
 * resonate with specific cultural contexts.
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
 * Diagnostic_Adapts_Content_Culturally Class
 *
 * Diagnostic #26: Cultural Adaptation from Specialized & Emerging Success Habits.
 * Checks if the website adapts content for cultural differences beyond simple
 * translation, including imagery, formats, and culturally relevant messaging.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Adapts_Content_Culturally extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'adapts-content-culturally';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cultural Adaptation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site adapts content for cultural differences beyond just translation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'international-ecommerce';

	/**
	 * Run the diagnostic check.
	 *
	 * Cultural adaptation demonstrates respect for diverse audiences. This diagnostic
	 * checks for region-specific content, cultural references, localized examples,
	 * and cultural sensitivity documentation.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score          = 0;
		$max_score      = 5;
		$score_details  = array();
		$recommendations = array();

		// Check 1: Multi-language plugin (foundation for cultural adaptation).
		$multilingual_plugins = array(
			'sitepress-multilingual-cms/sitepress.php', // WPML.
			'polylang/polylang.php',                    // Polylang.
			'translatepress-multilingual/index.php',    // TranslatePress.
			'weglot/weglot.php',                        // Weglot.
		);

		$has_multilingual = false;
		foreach ( $multilingual_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_multilingual = true;
				break;
			}
		}

		if ( $has_multilingual ) {
			++$score;
			$score_details[] = __( '✓ Multi-language plugin active (foundation for cultural adaptation)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No multi-language plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Install WPML or Polylang as foundation for cultural content adaptation', 'wpshadow' );
		}

		// Check 2: Region-specific content or pages.
		$all_pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 20,
				'post_status'    => 'publish',
			)
		);

		$regional_pages = 0;
		$region_keywords = array( 'for', 'in', 'europe', 'asia', 'americas', 'africa', 'oceania', 'uk', 'us', 'canada', 'australia' );

		foreach ( $all_pages as $page ) {
			$title_lower = strtolower( $page->post_title );
			foreach ( $region_keywords as $keyword ) {
				if ( stripos( $title_lower, $keyword ) !== false ) {
					++$regional_pages;
					break;
				}
			}
		}

		if ( $regional_pages >= 3 ) {
			++$score;
			$score_details[] = sprintf(
				/* translators: %d: number of regional pages */
				__( '✓ Region-specific content detected (%d+ pages)', 'wpshadow' ),
				$regional_pages
			);
		} else {
			$score_details[]   = __( '✗ No region-specific content found', 'wpshadow' );
			$recommendations[] = __( 'Create region-specific landing pages with culturally relevant content and examples', 'wpshadow' );
		}

		// Check 3: Cultural references or localized examples.
		$all_posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => 20,
				'post_status'    => 'publish',
			)
		);

		$cultural_references = 0;
		$cultural_keywords = array(
			'local customs', 'cultural', 'traditional', 'regional preferences',
			'local market', 'adapted for', 'tailored to', 'designed for',
		);

		foreach ( $all_posts as $post ) {
			$content_lower = strtolower( $post->post_content );
			foreach ( $cultural_keywords as $keyword ) {
				if ( stripos( $content_lower, $keyword ) !== false ) {
					++$cultural_references;
					break;
				}
			}
		}

		if ( $cultural_references >= 3 ) {
			++$score;
			$score_details[] = __( '✓ Cultural adaptation references in content', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No cultural adaptation messaging found', 'wpshadow' );
			$recommendations[] = __( 'Include culturally relevant examples, case studies, and messaging for each target market', 'wpshadow' );
		}

		// Check 4: Date/number format localization awareness.
		$format_awareness = 0;
		foreach ( $all_posts as $post ) {
			if ( stripos( $post->post_content, 'DD/MM/YYYY' ) !== false ||
				 stripos( $post->post_content, 'MM/DD/YYYY' ) !== false ||
				 stripos( $post->post_content, 'locale' ) !== false ||
				 stripos( $post->post_content, 'format' ) !== false ) {
				++$format_awareness;
				break;
			}
		}

		if ( $format_awareness > 0 ) {
			++$score;
			$score_details[] = __( '✓ Awareness of regional formats (dates, numbers, units)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No regional format references', 'wpshadow' );
			$recommendations[] = __( 'Adapt date formats (DD/MM vs MM/DD), units (metric vs imperial), and number formats by region', 'wpshadow' );
		}

		// Check 5: Cultural sensitivity documentation or guidelines.
		$sensitivity_pages = 0;
		$sensitivity_keywords = array( 'diversity', 'inclusion', 'cultural sensitivity', 'accessibility', 'global' );

		foreach ( $all_pages as $page ) {
			$title_content_lower = strtolower( $page->post_title . ' ' . $page->post_content );
			foreach ( $sensitivity_keywords as $keyword ) {
				if ( stripos( $title_content_lower, $keyword ) !== false ) {
					++$sensitivity_pages;
					break;
				}
			}
		}

		if ( $sensitivity_pages >= 1 ) {
			++$score;
			$score_details[] = __( '✓ Cultural sensitivity or diversity documentation found', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No cultural sensitivity guidelines documented', 'wpshadow' );
			$recommendations[] = __( 'Document your approach to cultural sensitivity and inclusive content for global audiences', 'wpshadow' );
		}

		// Calculate score percentage.
		$score_percentage = ( $score / $max_score ) * 100;

		// Determine severity based on score.
		if ( $score_percentage < 30 ) {
			$severity     = 'medium';
			$threat_level = 20;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 10;
		} else {
			// Cultural adaptation is adequate.
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Cultural adaptation score: %d%%. 76%% of consumers prefer content in their native language, but 65%% also want culturally relevant imagery and examples. Cultural missteps can damage brand reputation and lose 40%% of potential customers in new markets.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/cultural-adaptation',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Cultural adaptation demonstrates respect for diverse audiences, increases engagement by 35%, and prevents offensive or irrelevant content that could harm brand reputation.', 'wpshadow' ),
		);
	}
}
