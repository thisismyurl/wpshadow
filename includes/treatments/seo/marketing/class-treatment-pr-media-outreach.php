<?php
/**
 * PR and Media Outreach Treatment
 *
 * Checks whether press kit and media outreach assets exist.
 *
 * @package    WPShadow
 * @subpackage Treatments\Marketing
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * PR and Media Outreach Treatment Class
 *
 * Verifies press kit and media outreach indicators.
 *
 * @since 1.6035.1400
 */
class Treatment_Pr_Media_Outreach extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'pr-media-outreach';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'No PR or Media Outreach Strategy';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks for press kits, media pages, and outreach indicators';

	/**
	 * The family this treatment belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'brand-awareness';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Pr_Media_Outreach' );
	}

	/**
	 * Find pages or posts by keyword search.
	 *
	 * @since  1.6035.1400
	 * @param  array $keywords Keywords to search for.
	 * @return array List of matching page titles.
	 */
	private static function find_pages_by_keywords( array $keywords ): array {
		$matches = array();

		foreach ( $keywords as $keyword ) {
			$results = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => array( 'page', 'post' ),
					'posts_per_page' => 5,
				)
			);

			foreach ( $results as $post ) {
				$matches[ $post->ID ] = get_the_title( $post );
			}
		}

		return array_values( $matches );
	}
}
