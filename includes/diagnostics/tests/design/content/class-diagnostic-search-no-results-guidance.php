<?php
/**
 * Search No Results Guidance Diagnostic
 *
 * Checks whether search pages guide users when no results are found.
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
 * Search No Results Guidance Diagnostic Class
 *
 * Verifies that search templates include helpful guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Search_No_Results_Guidance extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'search-no-results-guidance';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Search Results Don\'t Explain No Results';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if search pages guide users when no results are found';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array();

		$search_template = get_theme_file_path( 'search.php' );
		$stats['search_template'] = ( $search_template && file_exists( $search_template ) ) ? 'yes' : 'no';

		$content_template = get_theme_file_path( 'template-parts/content-none.php' );
		$stats['content_none_template'] = ( $content_template && file_exists( $content_template ) ) ? 'yes' : 'no';

		$template_content = '';
		if ( $search_template && file_exists( $search_template ) ) {
			$template_content .= file_get_contents( $search_template );
		}
		if ( $content_template && file_exists( $content_template ) ) {
			$template_content .= file_get_contents( $content_template );
		}

		$has_search_form = false !== strpos( $template_content, 'get_search_form' ) || false !== strpos( $template_content, 'searchform' );
		$has_suggestions = false !== strpos( $template_content, 'no results' ) || false !== strpos( $template_content, 'did you mean' );

		$stats['has_search_form'] = $has_search_form ? 'yes' : 'no';
		$stats['has_suggestions'] = $has_suggestions ? 'yes' : 'no';

		if ( empty( $template_content ) ) {
			$issues[] = __( 'Search templates not found to guide users on no results', 'wpshadow' );
		} else {
			if ( ! $has_search_form ) {
				$issues[] = __( 'Search templates do not show a search form for trying again', 'wpshadow' );
			}
			if ( ! $has_suggestions ) {
				$issues[] = __( 'No clear guidance for zero results detected', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'When a search returns nothing, visitors need a helpful next step. Suggestions and a retry box keep them engaged.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/search-no-results-guidance?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
