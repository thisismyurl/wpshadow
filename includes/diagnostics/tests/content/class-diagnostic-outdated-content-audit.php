<?php
/**
 * Outdated Content Audit Diagnostic
 *
 * Identifies old content that needs updating to maintain accuracy,
 * relevance, and search engine rankings.
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
 * Diagnostic_Outdated_Content_Audit Class
 *
 * Detects content that hasn't been updated recently.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Outdated_Content_Audit extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'outdated-content-audit';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Outdated Content Audit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies old content needing updates';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if outdated content found, null otherwise.
	 */
	public static function check() {
		$outdated = self::find_outdated_content();

		if ( $outdated['old_count'] === 0 ) {
			return null; // No significantly outdated content
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of outdated posts */
				__( '%d posts not updated in 2+ years. Outdated information damages credibility, rankings decline as content freshness decreases.', 'wpshadow' ),
				$outdated['old_count']
			),
			'severity'     => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/content-freshness',
			'family'       => self::$family,
			'meta'         => array(
				'outdated_posts'       => $outdated['old_count'],
				'very_old_posts'       => $outdated['very_old_count'],
				'freshness_importance' => __( 'Google Query Deserves Freshness (QDF) boosts recent content' ),
				'ranking_decay'        => __( 'Rankings decline 10-20% annually if not updated' ),
			),
			'details'      => array(
				'why_content_freshness_matters' => array(
					__( 'Google QDF: Fresh content ranks higher for time-sensitive queries' ),
					__( 'User trust: Outdated info damages credibility' ),
					__( 'Ranking decay: Old content slowly loses rankings' ),
					__( 'Competitive: Competitors updating = you falling behind' ),
				),
				'content_age_thresholds'  => array(
					'< 6 months' => 'Fresh - minimal maintenance',
					'6-12 months' => 'Monitor - check for outdated facts',
					'1-2 years' => 'Review - update statistics, examples',
					'2-3 years' => 'Refresh - major content update needed',
					'3+ years' => 'Overhaul or retire - rewrite or delete',
				),
				'prioritizing_updates'    => array(
					'High Priority' => array(
						'Top 10 traffic pages (protect rankings)',
						'Product/service pages (business-critical)',
						'Time-sensitive content (outdated stats)',
					),
					'Medium Priority' => array(
						'Blog posts with steady traffic',
						'Resource pages with backlinks',
						'Tutorial/how-to guides',
					),
					'Low Priority' => array(
						'Historical posts (archive, don\'t update)',
						'Low-traffic pages with no links',
						'Outdated products (redirect or delete)',
					),
				),
				'content_update_checklist' => array(
					'Update Statistics' => array(
						'Replace old numbers with current data',
						'Update "as of [date]" references',
						'Add latest research findings',
					),
					'Refresh Examples' => array(
						'Replace outdated screenshots',
						'Update product versions mentioned',
						'Add new case studies',
					),
					'Improve Structure' => array(
						'Add table of contents',
						'Break up long paragraphs',
						'Add subheadings for scannability',
					),
					'Expand Content' => array(
						'Add 200-300 new words',
						'Include recent developments',
						'Answer new related questions',
					),
					'Technical Updates' => array(
						'Fix broken links',
						'Update internal links',
						'Refresh meta description',
						'Update published date to today',
					),
				),
				'content_refresh_strategy' => array(
					'Monthly' => array(
						'Update top 5 traffic pages',
						'Check for broken links site-wide',
						'Review 2 old posts for refresh',
					),
					'Quarterly' => array(
						'Audit all top 20 pages',
						'Update product/service descriptions',
						'Refresh statistics in key posts',
					),
					'Annually' => array(
						'Complete content audit',
						'Delete/redirect low-value pages',
						'Merge thin content pages',
						'Comprehensive refresh of cornerstone content',
					),
				),
			),
		);
	}

	/**
	 * Find outdated content.
	 *
	 * @since  1.2601.2148
	 * @return array Outdated content statistics.
	 */
	private static function find_outdated_content() {
		global $wpdb;

		$two_years_ago   = gmdate( 'Y-m-d H:i:s', strtotime( '-2 years' ) );
		$three_years_ago = gmdate( 'Y-m-d H:i:s', strtotime( '-3 years' ) );

		$old_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				WHERE post_status = 'publish' 
				AND post_type IN ('post', 'page')
				AND post_modified < %s",
				$two_years_ago
			)
		);

		$very_old_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->posts} 
				WHERE post_status = 'publish' 
				AND post_type IN ('post', 'page')
				AND post_modified < %s",
				$three_years_ago
			)
		);

		return array(
			'old_count'      => $old_count,
			'very_old_count' => $very_old_count,
		);
	}
}
