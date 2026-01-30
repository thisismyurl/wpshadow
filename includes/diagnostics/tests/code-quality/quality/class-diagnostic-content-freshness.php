<?php
/**
 * Content Freshness Diagnostic
 *
 * Identifies outdated content not updated in months,
 * indicating neglect or relevance issues.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Content_Freshness Class
 *
 * Monitors content freshness.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Content_Freshness extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'content-freshness';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Freshness';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies outdated content';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if stale content found, null otherwise.
	 */
	public static function check() {
		$freshness_status = self::check_content_freshness();

		if ( ! $freshness_status['has_issue'] ) {
			return null; // Content is reasonably fresh
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of posts */
				__( '%d posts not updated in 6+ months. Outdated content = visitor confusion = bounce. Update flagship content monthly, archive old posts.', 'wpshadow' ),
				$freshness_status['stale_count']
			),
			'severity'     => 'low',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/content-freshness',
			'family'       => self::$family,
			'meta'         => array(
				'stale_posts' => $freshness_status['stale_count'],
			),
			'details'      => array(
				'why_fresh_content_matters'       => array(
					'SEO' => array(
						'Fresh content = signal to search engines',
						'Recent update dates boost SERP position',
						'Evergreen content: Update yearly',
					),
					'Trust' => array(
						'Old dates = site looks abandoned',
						'Recent dates = actively maintained',
					),
					'Relevance' => array(
						'Outdated information = wrong answers',
						'Prices changed, features updated, etc.',
					),
				),
				'content_update_schedule'         => array(
					'Flagship Posts' => array(
						'Update: Monthly',
						'Example: "How to Choose a Widget"',
						'Impact: Drives most traffic',
					),
					'Evergreen Posts' => array(
						'Update: Quarterly (every 3 months)',
						'Example: "Widget Guide for Beginners"',
						'Purpose: Keep information current',
					),
					'News Posts' => array(
						'Update: Only if relevant',
						'Example: "2024 Widget Trends"',
						'Can be archived after year',
					),
					'Landing Pages' => array(
						'Update: Before each campaign',
						'Example: Homepage, Pricing',
						'Keep current with business',
					),
				),
				'finding_stale_content'           => array(
					'Google Analytics' => array(
						'Report: Behavior → Site Content',
						'Shows: Last update date in Search Console',
					),
					'WordPress Plugin' => array(
						'Plugin: WP Expire Posts',
						'Flags: Posts not updated in X days',
					),
					'Manual Audit' => array(
						'Review: Post publish dates',
						'List: Sort by modified date',
						'Identify: Posts > 6 months old',
					),
				),
				'update_strategy'                 => array(
					'Flagship Content Monthly' => array(
						'Schedule: 1st of each month',
						'Process: Review, update, republish',
						'Impact: Highest traffic posts',
					),
					'Archive Outdated' => array(
						'Decision: Keep or retire',
						'Mark: "Updated [DATE]" in content',
						'Link: To newer replacement',
					),
					'Repurpose' => array(
						'Old post: Outdated information',
						'New format: Video, infographic',
						'Refreshed content, new audience',
					),
				),
				'automation'                      => array(
					__( 'Schedule updates as team task' ),
					__( 'Calendar reminders for evergreen posts' ),
					__( 'Track: Last updated date per post' ),
					__( 'Automate: Email reminder on old posts' ),
				),
			),
		);
	}

	/**
	 * Check content freshness.
	 *
	 * @since  1.2601.2148
	 * @return array Freshness status.
	 */
	private static function check_content_freshness() {
		// Get posts not updated in 6+ months
		$cutoff_date = date( 'Y-m-d', strtotime( '-6 months' ) );

		$stale_posts = get_posts( array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'publish',
			'before'         => $cutoff_date,
			'orderby'        => 'modified',
			'order'          => 'ASC',
			'numberposts'    => 1000,
			'fields'         => 'ids',
		) );

		$stale_count = count( $stale_posts );

		return array(
			'has_issue'  => $stale_count >= 10, // 10+ stale posts = issue
			'stale_count' => $stale_count,
		);
	}
}
