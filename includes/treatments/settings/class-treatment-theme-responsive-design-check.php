<?php
/**
 * Theme Responsive Design Treatment
 *
 * Checks if theme is responsive and mobile-friendly.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.5049.1245
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Responsive Design Treatment Class
 *
 * Analyzes theme's responsive design implementation.
 *
 * @since 1.5049.1245
 */
class Treatment_Theme_Responsive_Design_Check extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-responsive-design-check';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Responsive Design Check';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks theme responsive design';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.5049.1245
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme = wp_get_theme();
		$issues = array();

		// Check homepage HTML.
		$home_url = home_url( '/' );
		$response = wp_remote_get( $home_url, array( 'timeout' => 10 ) );

		if ( ! is_wp_error( $response ) ) {
			$html = wp_remote_retrieve_body( $response );

			// Check for viewport meta tag.
			if ( ! preg_match( '/<meta[^>]*name=["\']viewport["\']/i', $html ) ) {
				$issues[] = __( 'Missing viewport meta tag', 'wpshadow' );
			}

			// Check for media queries in stylesheets.
			$has_media_queries = preg_match( '/@media/i', $html );
			if ( ! $has_media_queries ) {
				$issues[] = __( 'No media queries detected', 'wpshadow' );
			}

			// Check for mobile-specific classes.
			$has_mobile_classes = preg_match( '/mobile|responsive|xs|sm|md|lg/i', $html );
			if ( ! $has_mobile_classes ) {
				$issues[] = __( 'No responsive CSS classes detected', 'wpshadow' );
			}
		}

		// Check style.css for media queries.
		$theme_dir = get_stylesheet_directory();
		$style_css = $theme_dir . '/style.css';

		if ( file_exists( $style_css ) ) {
			$css_content = file_get_contents( $style_css );
			$media_query_count = substr_count( strtolower( $css_content ), '@media' );

			if ( $media_query_count < 3 ) {
				$issues[] = sprintf(
					/* translators: %d: number of media queries */
					__( 'Only %d media queries found in style.css', 'wpshadow' ),
					$media_query_count
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Theme may not be fully responsive', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'details'     => array(
					'theme'  => $theme->get( 'Name' ),
					'issues' => $issues,
				),
				'kb_link'     => 'https://wpshadow.com/kb/theme-responsive-design-check',
			);
		}

		return null;
	}
}
