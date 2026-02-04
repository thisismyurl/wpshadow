<?php
/**
 * No Content Gap Analysis Diagnostic
 *
 * Detects when content gaps are not being analyzed,
 * missing keyword and topic opportunities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Content Gap Analysis
 *
 * Checks whether content gaps are analyzed
 * to identify missing topics and keywords.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Content_Gap_Analysis extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-content-gap-analysis';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Gap Analysis';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether content gaps are analyzed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for content gap analysis strategy
		$has_gap_analysis = get_option( 'wpshadow_content_gap_analysis' );

		if ( ! $has_gap_analysis ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not analyzing content gaps, which means you\'re missing valuable keyword opportunities. Content gap analysis finds: keywords competitors rank for that you don\'t, questions your audience asks that you haven\'t answered, topics in your niche you haven\'t covered. Tools like Ahrefs and SEMrush show these gaps. Filling gaps is easier than competing for crowded keywords—you\'re targeting proven demand with less competition.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'SEO Opportunity Discovery',
					'potential_gain' => 'Identify untapped keyword opportunities',
					'roi_explanation' => 'Content gap analysis reveals low-competition, high-value keywords you\'re missing but competitors rank for.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/content-gap-analysis',
			);
		}

		return null;
	}
}
