<?php
/**
 * No PR or Media Outreach Strategy Diagnostic
 *
 * Checks whether press or media outreach assets are available.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\BusinessPerformance
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PR and Media Outreach Diagnostic
 *
 * Detects when there is no visible press kit or media page. Press coverage
 * builds credibility quickly and can create lasting trust signals.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Pr_Or_Media_Outreach_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-pr-media-outreach-strategy';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'PR or Media Outreach Assets Available';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether a press kit or media page is available';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$press_assets = self::count_press_assets();

		if ( 0 === $press_assets ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'A press or media page is not visible yet. Media coverage acts like a trusted recommendation and can boost credibility quickly. A simple press kit makes it easy for journalists and partners to feature you.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/pr-media-outreach?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'press_assets_found' => $press_assets,
					'recommendation'     => __( 'Create a simple press kit page with your story, logos, screenshots, and contact details.', 'wpshadow' ),
					'press_kit_items'     => self::get_press_kit_items(),
				),
			);
		}

		return null;
	}

	/**
	 * Count press-related pages or posts.
	 *
	 * @since 0.6093.1200
	 * @return int Number of press assets found.
	 */
	private static function count_press_assets(): int {
		$keywords = array(
			'press',
			'media kit',
			'press kit',
			'newsroom',
			'in the news',
			'as seen in',
		);

		return self::count_posts_by_keywords( $keywords );
	}

	/**
	 * Count posts/pages containing any keyword.
	 *
	 * @since 0.6093.1200
	 * @param  array $keywords Keywords to search for.
	 * @return int Count of matching posts/pages.
	 */
	private static function count_posts_by_keywords( array $keywords ): int {
		$total = 0;

		foreach ( $keywords as $keyword ) {
			$matches = get_posts( array(
				'post_type'   => array( 'page', 'post' ),
				'numberposts' => 5,
				's'           => $keyword,
			) );

			$total += count( $matches );
		}

		return $total;
	}

	/**
	 * Provide examples of press kit items.
	 *
	 * @since 0.6093.1200
	 * @return array Press kit items.
	 */
	private static function get_press_kit_items(): array {
		return array(
			__( 'Short company description and mission', 'wpshadow' ),
			__( 'Founders or leadership bios', 'wpshadow' ),
			__( 'Logos and product screenshots', 'wpshadow' ),
			__( 'Key milestones or impact metrics', 'wpshadow' ),
			__( 'Media contact email', 'wpshadow' ),
		);
	}
}
