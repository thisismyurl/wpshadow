<?php
/**
 * Content No Short-Form Diagnostic
 *
 * Identifies sites with no short-form content opportunity.
 *
 * @since   1.26033.1645
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content No Short-Form Diagnostic Class
 *
 * Detects sites with only long-form content (all posts > 1,500 words) that miss
 * opportunities for quick, high-velocity content serving different user intents.
 *
 * @since 1.26033.1645
 */
class Diagnostic_Content_No_Shortform extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-no-shortform';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Short-Form Content';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identify sites with only long-form content missing short-form opportunities';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if site has any short-form content
		$has_shortform = apply_filters( 'wpshadow_site_has_shortform_content', false );
		if ( ! $has_shortform ) {
			$issues[] = __( 'Site has no posts under 600 words; short-form content captures different user intents', 'wpshadow' );
		}

		// Check average post length
		$average_length = apply_filters( 'wpshadow_average_post_word_count', 0 );
		if ( $average_length > 1500 ) {
			$issues[] = sprintf(
				/* translators: %d: average word count */
				__( 'Average post length is %d words; consider adding 400-600 word quick reads', 'wpshadow' ),
				$average_length
			);
		}

		// Check for FAQ content opportunity
		$faq_content = apply_filters( 'wpshadow_has_faq_or_definition_posts', false );
		if ( ! $faq_content ) {
			$issues[] = __( 'No FAQ or definition posts found; these make excellent short-form (300-500 words)', 'wpshadow' );
		}

		// Check for news/update posts
		$news_posts = apply_filters( 'wpshadow_has_news_or_update_posts', false );
		if ( ! $news_posts ) {
			$issues[] = __( 'No news or update posts; quick reads (400-700 words) capture trending topics', 'wpshadow' );
		}

		// Check for quick tips content
		$quick_tips = apply_filters( 'wpshadow_has_quick_tips_posts', false );
		if ( ! $quick_tips ) {
			$issues[] = __( 'Consider adding quick tips or \"Today I Learned\" posts (400-600 words)', 'wpshadow' );
		}

		// Check publishing frequency with current strategy
		$publishing_frequency = apply_filters( 'wpshadow_estimated_publishing_frequency', 0 );
		if ( $publishing_frequency < 1 ) {
			$issues[] = __( 'Short-form content enables faster publishing without sacrificing quality', 'wpshadow' );
		}

		// Check if all posts over 1500 words
		$all_posts_long = apply_filters( 'wpshadow_all_posts_exceed_wordcount', false );
		if ( $all_posts_long ) {
			$issues[] = __( '100% of posts are 1,500+ words; diversify with quick-read content', 'wpshadow' );
		}

		// Check for content mix opportunity
		$content_strategy = apply_filters( 'wpshadow_has_defined_content_tier_strategy', false );
		if ( ! $content_strategy ) {
			$issues[] = __( 'Optimal mix: 70% standard (1,000-1,500 words), 20% long-form (2,500+), 10% quick reads (400-600)', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-no-shortform',
			);
		}

		return null;
	}
}
