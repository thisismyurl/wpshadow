<?php
/**
 * Contextual Documentation Links Diagnostic
 *
 * Checks whether documentation links are specific to the current context.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since      1.6035.1400
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contextual Documentation Links Diagnostic Class
 *
 * Verifies that help links point to specific resources.
 *
 * @since 1.6035.1400
 */
class Diagnostic_Contextual_Documentation_Links extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'contextual-documentation-links';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Documentation Links Are Generic';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if help links point to specific documentation pages';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1400
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$kb_pages = self::find_pages_by_keywords( array( 'help', 'support', 'documentation', 'knowledge base' ) );
		$stats['kb_pages'] = ! empty( $kb_pages ) ? implode( ', ', $kb_pages ) : 'none';

		$has_specific_kb = false;
		if ( ! empty( $kb_pages ) ) {
			foreach ( $kb_pages as $page_title ) {
				if ( false !== stripos( $page_title, 'setup' ) || false !== stripos( $page_title, 'guide' ) ) {
					$has_specific_kb = true;
					break;
				}
			}
		}

		$stats['specific_kb_pages'] = $has_specific_kb ? 'yes' : 'no';

		if ( ! $has_specific_kb ) {
			$issues[] = __( 'Help links appear to point only to generic pages', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Contextual help links should take people directly to the right answer, not a generic help landing page. This saves time and reduces frustration.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/contextual-documentation-links',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
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
					'post_status'    => 'publish',
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
