<?php
/**
 * AI Content Assistance Diagnostic
 *
 * Tests whether the site uses AI tools for content creation and optimization.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AI Content Assistance Diagnostic Class
 *
 * AI-powered content tools help with writing, SEO optimization, grammar checking,
 * and content ideation, improving quality and reducing production time.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Ai_Content_Assistance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ai-content-assistance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'AI Content Assistance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site uses AI tools for content creation and optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$ai_score = 0;
		$max_score = 7;

		// Check for AI writing assistant plugins.
		$ai_writing_plugins = array(
			'ai-engine/ai-engine.php' => 'AI Engine',
			'bertha-ai/bertha-ai.php' => 'Bertha AI',
			'gpt3-ai-content-generator/gpt3-ai-content-generator.php' => 'GPT-3 AI Content',
			'jetpack/jetpack.php' => 'Jetpack (AI Assistant)',
		);

		$has_ai_writing = false;
		foreach ( $ai_writing_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$has_ai_writing = true;
				$ai_score++;
				break;
			}
		}

		if ( ! $has_ai_writing ) {
			$issues[] = __( 'No AI writing assistant plugin detected', 'wpshadow' );
		}

		// Check for SEO optimization tools with AI.
		$seo_ai_tools = self::check_seo_ai_tools();
		if ( $seo_ai_tools ) {
			$ai_score++;
		} else {
			$issues[] = __( 'No AI-powered SEO optimization tools', 'wpshadow' );
		}

		// Check for grammar and readability checkers.
		$grammar_checkers = self::check_grammar_checkers();
		if ( $grammar_checkers ) {
			$ai_score++;
		} else {
			$issues[] = __( 'No grammar or readability checking tools', 'wpshadow' );
		}

		// Check for content ideation tools.
		$ideation_tools = self::check_ideation_tools();
		if ( $ideation_tools ) {
			$ai_score++;
		} else {
			$issues[] = __( 'No content ideation or topic suggestion tools', 'wpshadow' );
		}

		// Check for image generation/optimization AI.
		$image_ai = self::check_image_ai();
		if ( $image_ai ) {
			$ai_score++;
		} else {
			$issues[] = __( 'No AI-powered image generation or optimization', 'wpshadow' );
		}

		// Check for content translation AI.
		$translation_ai = self::check_translation_ai();
		if ( $translation_ai ) {
			$ai_score++;
		} else {
			$issues[] = __( 'No AI-powered translation for multilingual content', 'wpshadow' );
		}

		// Check for content performance analysis.
		$performance_analysis = self::check_performance_analysis();
		if ( $performance_analysis ) {
			$ai_score++;
		} else {
			$issues[] = __( 'No AI content performance analysis or recommendations', 'wpshadow' );
		}

		// Determine severity based on AI content assistance implementation.
		$ai_percentage = ( $ai_score / $max_score ) * 100;

		if ( $ai_percentage < 30 ) {
			// Minimal or no AI content assistance.
			$severity = 'low';
			$threat_level = 30;
		} elseif ( $ai_percentage < 60 ) {
			// Basic AI content assistance.
			$severity = 'low';
			$threat_level = 20;
		} else {
			// Good AI content assistance - no issue.
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: AI content assistance percentage */
				__( 'AI content assistance at %d%%. ', 'wpshadow' ),
				(int) $ai_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'AI tools can reduce content creation time by 50% while improving quality', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ai-content-assistance?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}

	/**
	 * Check for SEO AI tools.
	 *
	 * @since 0.6093.1200
	 * @return bool True if SEO AI tools exist, false otherwise.
	 */
	private static function check_seo_ai_tools() {
		// Check for SEO plugins with AI features.
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php' => 'Yoast SEO',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
			'seo-by-rank-math/rank-math.php' => 'Rank Math',
		);

		foreach ( $seo_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_seo_ai_tools', false );
	}

	/**
	 * Check for grammar and readability checkers.
	 *
	 * @since 0.6093.1200
	 * @return bool True if grammar checkers exist, false otherwise.
	 */
	private static function check_grammar_checkers() {
		// Check for grammar checking plugins.
		$grammar_plugins = array(
			'jetpack/jetpack.php' => 'Jetpack',
			'grammarly/grammarly.php' => 'Grammarly',
			'languagetool/languagetool.php' => 'LanguageTool',
		);

		foreach ( $grammar_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		// Check for readability analysis in SEO plugins.
		if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) ||
			 is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_grammar_checkers', false );
	}

	/**
	 * Check for content ideation tools.
	 *
	 * @since 0.6093.1200
	 * @return bool True if ideation tools exist, false otherwise.
	 */
	private static function check_ideation_tools() {
		// Check for content planning plugins.
		$ideation_plugins = array(
			'editorial-calendar/edcal.php',
			'publishpress/publishpress.php',
			'content-views-query-and-display-post-page/content-views.php',
		);

		foreach ( $ideation_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_ideation_tools', false );
	}

	/**
	 * Check for image AI tools.
	 *
	 * @since 0.6093.1200
	 * @return bool True if image AI exists, false otherwise.
	 */
	private static function check_image_ai() {
		// Check for AI image plugins.
		$image_ai_plugins = array(
			'imagify/imagify.php' => 'Imagify',
			'shortpixel-image-optimiser/wp-shortpixel.php' => 'ShortPixel',
			'ewww-image-optimizer/ewww-image-optimizer.php' => 'EWWW',
			'ai-image-generator/ai-image-generator.php' => 'AI Image Generator',
		);

		foreach ( $image_ai_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_image_ai', false );
	}

	/**
	 * Check for translation AI.
	 *
	 * @since 0.6093.1200
	 * @return bool True if translation AI exists, false otherwise.
	 */
	private static function check_translation_ai() {
		// Check for translation plugins with AI.
		$translation_plugins = array(
			'translatepress-multilingual/index.php' => 'TranslatePress',
			'weglot/weglot.php' => 'Weglot',
			'gtranslate/gtranslate.php' => 'GTranslate',
			'polylang/polylang.php' => 'Polylang',
		);

		foreach ( $translation_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_translation_ai', false );
	}

	/**
	 * Check for content performance analysis.
	 *
	 * @since 0.6093.1200
	 * @return bool True if performance analysis exists, false otherwise.
	 */
	private static function check_performance_analysis() {
		// Check for analytics and insights plugins.
		$analytics_plugins = array(
			'google-site-kit/google-site-kit.php',
			'jetpack/jetpack.php',
			'independent-analytics/independent-analytics.php',
		);

		foreach ( $analytics_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_performance_analysis', false );
	}
}
