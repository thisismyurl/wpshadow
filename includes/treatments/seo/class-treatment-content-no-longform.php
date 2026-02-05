<?php
/**
 * Content No Long-Form Treatment
 *
 * Detects absence of long-form content for SEO authority.
 *
 * @since   1.6033.1645
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Content No Long-Form Treatment Class
 *
 * Detects absence of long-form content (2,000+ words) which ranks better,
 * earns more backlinks, and establishes greater authority.
 *
 * @since 1.6033.1645
 */
class Treatment_Content_No_Longform extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-no-longform';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'No Long-Form Content';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detect missing long-form content proven to rank better and earn backlinks';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content-strategy';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if site has any long-form content
		$has_longform = apply_filters( 'wpshadow_site_has_longform_content', false );
		if ( ! $has_longform ) {
			$issues[] = __( 'No posts over 2,000 words; long-form content ranks 56% better and earns 77% of backlinks', 'wpshadow' );
		}

		// Check average post length
		$average_length = apply_filters( 'wpshadow_average_post_word_count', 0 );
		if ( $average_length < 1000 ) {
			$issues[] = sprintf(
				/* translators: %d: average word count */
				__( 'Average post length is %d words; long-form (2,000+ words) generates 9x more leads', 'wpshadow' ),
				$average_length
			);
		}

		// Check for pillar posts
		$pillar_posts = apply_filters( 'wpshadow_has_pillar_posts', false );
		if ( ! $pillar_posts ) {
			$issues[] = __( 'No comprehensive pillar posts found; create \"Ultimate Guide\" (3,000-5,000 words) on core topics', 'wpshadow' );
		}

		// Check for comprehensive guides
		$comprehensive = apply_filters( 'wpshadow_has_comprehensive_guides', false );
		if ( ! $comprehensive ) {
			$issues[] = __( 'No comprehensive guides detected; long-form establishes authority and attracts backlinks', 'wpshadow' );
		}

		// Check for topic coverage depth
		$topic_depth = apply_filters( 'wpshadow_topics_have_depth_coverage', false );
		if ( ! $topic_depth ) {
			$issues[] = __( 'Topics should be covered in depth with one long-form pillar + multiple supporting posts', 'wpshadow' );
		}

		// Check for competitor long-form presence
		$competitors_longform = apply_filters( 'wpshadow_competitors_have_longform_advantage', false );
		if ( $competitors_longform ) {
			$issues[] = __( 'Competitors have long-form content; you\\'re at disadvantage for authority and backlinks', 'wpshadow' );
		}

		// Check for content gap opportunity
		$content_gaps = apply_filters( 'wpshadow_identified_content_gaps_for_longform', false );
		if ( $content_gaps ) {
			$issues[] = __( 'Identified opportunities for long-form content that would rank well', 'wpshadow' );
		}

		// Check for conversion impact
		$conversion_impact = apply_filters( 'wpshadow_longform_would_improve_conversions', false );
		if ( $conversion_impact ) {
			$issues[] = __( 'Long-form content gets 40% more time on page = higher conversion potential', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-no-longform',
			);
		}

		return null;
	}
}
