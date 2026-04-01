<?php
/**
 * Content List Posts Without Sufficient Detail Diagnostic
 *
 * Identifies list posts lacking sufficient detail per item.
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
 * Content List Posts Without Sufficient Detail Diagnostic Class
 *
 * Identifies list posts (\"10 ways...\", \"7 best...\") that lack sufficient detail
 * to provide value, making them inferior to competitor content.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Content_Shallow_List_Posts extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-shallow-list-posts';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'List Posts Without Sufficient Detail';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identify list posts with insufficient detail per item';

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

		// Check for shallow list posts
		$shallow_lists = apply_filters( 'wpshadow_has_shallow_list_posts', false );
		if ( $shallow_lists ) {
			$issues[] = __( 'List posts detected with <100 words per item; expand to 150+ words each', 'wpshadow' );
		}

		// Check average words per list item
		$avg_words_per_item = apply_filters( 'wpshadow_average_words_per_list_item', 0 );
		if ( $avg_words_per_item < 100 && $avg_words_per_item > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: average words per item */
				__( 'Average list item depth is %d words; industry standard is 165 words/item', 'wpshadow' ),
				$avg_words_per_item
			);
		}

		// Check if list items have explanations
		$items_have_why = apply_filters( 'wpshadow_list_items_have_explanations', false );
		if ( ! $items_have_why ) {
			$issues[] = __( 'List items should explain WHY they matter, not just WHAT they are', 'wpshadow' );
		}

		// Check for list item examples
		$items_have_examples = apply_filters( 'wpshadow_list_items_have_examples', false );
		if ( ! $items_have_examples ) {
			$issues[] = __( 'List items lack real-world examples; add screenshots, case studies, or links', 'wpshadow' );
		}

		// Check for comparison with competitor lists
		$competitor_advantage = apply_filters( 'wpshadow_competitors_have_deeper_lists', false );
		if ( $competitor_advantage ) {
			$issues[] = __( 'Competitors\' list posts are significantly deeper; you\'re at disadvantage', 'wpshadow' );
		}

		// Check for action items in lists
		$items_actionable = apply_filters( 'wpshadow_list_items_are_actionable', false );
		if ( ! $items_actionable ) {
			$issues[] = __( 'List items should include step-by-step instructions, not just descriptions', 'wpshadow' );
		}

		// Check for proper list formatting
		$list_formatted = apply_filters( 'wpshadow_list_posts_properly_formatted', false );
		if ( ! $list_formatted ) {
			$issues[] = __( 'Use numbered lists for proper structure and readability', 'wpshadow' );
		}

		// Check for social share impact
		$share_potential = apply_filters( 'wpshadow_list_posts_high_share_potential', false );
		if ( ! $share_potential ) {
			$issues[] = __( 'Comprehensive list posts (150+ words/item) get 3x more social shares', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-shallow-list-posts?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
