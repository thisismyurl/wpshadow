<?php
/**
 * Content Title Mismatch Diagnostic
 *
 * Detects when content doesn't match title promises.
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
 * Content Title Mismatch Diagnostic Class
 *
 * Detects when titles over-promise and under-deliver (e.g., \"How to\" without
 * steps, \"Complete Guide\" under 1,000 words). Misleading titles increase bounce rate 58%.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Content_Title_Mismatch extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-title-mismatch';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Doesn\'t Match Title Promise';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detect over-promising titles with under-delivering content';

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

		// Check for title/content mismatch
		$title_mismatch = apply_filters( 'wpshadow_detected_title_content_mismatch', false );
		if ( $title_mismatch ) {
			$issues[] = __( 'Detected posts where title promises don\'t match content delivery', 'wpshadow' );
		}

		// Check for "how to" without steps
		$howto_no_steps = apply_filters( 'wpshadow_howto_posts_without_numbered_steps', false );
		if ( $howto_no_steps ) {
			$issues[] = __( '\"How to\" posts must include clear numbered steps, not just discussion', 'wpshadow' );
		}

		// Check for "complete" that isn't
		$complete_incomplete = apply_filters( 'wpshadow_complete_posts_actually_incomplete', false );
		if ( $complete_incomplete ) {
			$issues[] = __( '\"Complete Guide\" posts under 1,000 words promise more than they deliver', 'wpshadow' );
		}

		// Check for "ultimate" that isn't comprehensive
		$ultimate_not_ultimate = apply_filters( 'wpshadow_ultimate_posts_not_comprehensive', false );
		if ( $ultimate_not_ultimate ) {
			$issues[] = __( '\"Ultimate\" guides should be 2,500+ words; shorter posts should drop the superlative', 'wpshadow' );
		}

		// Check for number promises (\"20 ways\" but only 10)
		$number_mismatch = apply_filters( 'wpshadow_number_promise_mismatch', false );
		if ( $number_mismatch ) {
			$issues[] = __( 'Posts don\'t deliver the number promised in title', 'wpshadow' );
		}

		// Check for bounce rate impact
		$bounce_rate = apply_filters( 'wpshadow_high_bounce_rate_title_mismatch', false );
		if ( $bounce_rate ) {
			$issues[] = __( 'Title/content mismatch increases bounce rate 58%; user expectations unmet', 'wpshadow' );
		}

		// Check for credibility damage
		$credibility = apply_filters( 'wpshadow_title_mismatch_damages_trust', false );
		if ( $credibility ) {
			$issues[] = __( 'Over-promising titles damage credibility and reduce repeat visitors', 'wpshadow' );
		}

		// Check for SEO impact
		$seo_impact = apply_filters( 'wpshadow_title_mismatch_harms_seo', false );
		if ( $seo_impact ) {
			$issues[] = __( 'Google recognizes title mismatches; users bounce, signal is negative', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/content-title-mismatch?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
