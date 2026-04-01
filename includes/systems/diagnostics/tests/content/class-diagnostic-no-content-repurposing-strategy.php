<?php
/**
 * No Content Repurposing Strategy Diagnostic
 *
 * Detects when content is not being repurposed across formats,
 * missing efficiency and reach opportunities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Content Repurposing Strategy
 *
 * Checks whether content is being repurposed into
 * multiple formats for different channels.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Content_Repurposing_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-content-repurposing-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Content Repurposing Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether content is being repurposed';

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
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for content repurposing strategy
		$has_repurposing = get_option( 'wpshadow_content_repurposing_strategy' );

		if ( ! $has_repurposing ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'You\'re not repurposing content into multiple formats, which means you\'re creating content once and using it once. Repurposing multiplies content value: one blog post becomes: 5 social posts, 1 video, 1 infographic, 1 podcast episode, 1 email newsletter. This reaches different audiences (some prefer video, some prefer reading) with minimal extra work. Top content creators repurpose everything, getting 5-10x more mileage from each piece.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Content Efficiency & Reach',
					'potential_gain' => '5-10x more content from same effort',
					'roi_explanation' => 'Repurposing content into multiple formats multiplies reach and efficiency, getting 5-10x more value from each piece.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/content-repurposing-strategy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
