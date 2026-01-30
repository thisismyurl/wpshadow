<?php
/**
 * HTML Detect Excessive Stylesheet Requests Diagnostic
 *
 * Detects pages with too many CSS file requests.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\HTML
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTML Detect Excessive Stylesheet Requests Diagnostic Class
 *
 * Identifies pages requesting too many CSS files, which increases HTTP
 * requests and slows down page load. Styles should be consolidated where
 * possible, or combined using critical CSS inlining.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Excessive_Stylesheet_Requests extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-excessive-stylesheet-requests';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Excessive Stylesheet Requests';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects pages with too many separate CSS file requests';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( is_admin() ) {
			return null;
		}

		$css_count = 0;
		$css_files = array();

		// Count WordPress styles.
		global $wp_styles;

		if ( ! empty( $wp_styles ) && isset( $wp_styles->queue ) ) {
			foreach ( $wp_styles->queue as $handle ) {
				if ( isset( $wp_styles->registered[ $handle ] ) ) {
					$style_obj = $wp_styles->registered[ $handle ];

					if ( isset( $style_obj->src ) && ! empty( $style_obj->src ) ) {
						$css_count++;
						$css_files[] = array(
							'handle' => $handle,
							'src'    => (string) $style_obj->src,
						);
					}
				}
			}
		}

		// Check inline styles for additional stylesheet links.
		if ( ! empty( $wp_styles ) && isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style_obj ) {
				if ( isset( $style_obj->extra['data'] ) ) {
					$data = (string) $style_obj->extra['data'];

					// Count <link rel="stylesheet"> tags.
					if ( preg_match_all( '/<link[^>]*rel=["\']?stylesheet["\']?[^>]*href=["\']([^"\']+)["\'][^>]*>/i', $data, $matches ) ) {
						$css_count += count( $matches[1] );

						foreach ( $matches[1] as $href ) {
							$css_files[] = array(
								'handle' => $handle,
								'src'    => $href,
							);
						}
					}
				}
			}
		}

		// Threshold: 8+ CSS files is excessive.
		$threshold = 8;

		if ( $css_count < $threshold ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $css_files, 0, $max_items ) as $file ) {
			$items_list .= sprintf(
				"\n- %s",
				esc_html( basename( $file['src'] ) )
			);
		}

		if ( count( $css_files ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more stylesheets", 'wpshadow' ),
				count( $css_files ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d separate CSS files/requests on this page. This is excessive and increases page load time. HTTP request overhead adds delays. Consider: consolidating stylesheets, using critical CSS inlining for above-the-fold styles, or using CSS bundling tools to combine files.%2$s', 'wpshadow' ),
				$css_count,
				$items_list
			),
			'severity'     => 'medium',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-excessive-stylesheet-requests',
			'meta'         => array(
				'count'  => $css_count,
				'files'  => $css_files,
				'advice' => __( 'Target: 3-5 CSS files maximum on most sites', 'wpshadow' ),
			),
		);
	}
}
