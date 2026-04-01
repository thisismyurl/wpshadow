<?php
/**
 * Helpful 404 Page Diagnostic
 *
 * Checks whether the 404 page provides helpful navigation.
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
 * Helpful 404 Page Diagnostic Class
 *
 * Verifies that the 404 template includes helpful navigation.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Helpful_404_Page extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'helpful-404-page';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = '404 Pages Not Helpful';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if the 404 page offers search and navigation';

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

		$template_path = get_theme_file_path( '404.php' );
		$stats['template_found'] = ( $template_path && file_exists( $template_path ) ) ? 'yes' : 'no';

		if ( empty( $template_path ) || ! file_exists( $template_path ) ) {
			$issues[] = __( 'No 404 template found in the active theme', 'wpshadow' );
		} else {
			$template = file_get_contents( $template_path );
			$has_search = false !== strpos( $template, 'get_search_form' ) || false !== strpos( $template, 'searchform' );
			$has_home_link = false !== strpos( $template, 'home_url' ) || false !== strpos( $template, "'/'" );

			$stats['has_search'] = $has_search ? 'yes' : 'no';
			$stats['has_home_link'] = $has_home_link ? 'yes' : 'no';

			if ( ! $has_search ) {
				$issues[] = __( '404 template does not include a search form', 'wpshadow' );
			}

			if ( ! $has_home_link ) {
				$issues[] = __( '404 template does not link back to the homepage', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'A helpful 404 page guides visitors to what they need next. A search box and a home link make it easy to recover.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/helpful-404-page?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'stats'  => $stats,
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
