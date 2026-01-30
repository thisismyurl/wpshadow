<?php
/**
 * HTML Detect Missing Main Element Diagnostic
 *
 * Detects missing main element on page.
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
 * HTML Detect Missing Main Element Diagnostic Class
 *
 * Identifies pages missing the semantic <main> element, which
 * is essential for proper document structure and accessibility.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Missing_Main_Element extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-missing-main-element';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing <main> Element';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing semantic <main> element on page';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'html';

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

		$missing_main = array();

		// Check scripts for main element presence.
		global $wp_scripts;

		$has_main_element = false;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Check for <main> tag.
					if ( preg_match( '/<main[^>]*>.*?<\/main>/is', $data ) ) {
						$has_main_element = true;
						break;
					}
				}
			}
		}

		// If no <main> element found on content page, that's an issue.
		if ( ! $has_main_element ) {
			global $post;

			if ( ! empty( $post ) && $post instanceof \WP_Post ) {
				// Content pages should have <main>.
				$missing_main[] = array(
					'issue'     => __( '<main> element not found on page', 'wpshadow' ),
					'page_type' => $post->post_type,
					'impact'    => __( 'Screen readers and search engines may not identify main content', 'wpshadow' ),
				);
			}
		}

		// Check if there's a #main or #content div used as workaround.
		if ( ! $has_main_element ) {
			$has_main_div = false;

			if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
				foreach ( $wp_scripts->registered as $handle => $script_obj ) {
					if ( isset( $script_obj->extra['data'] ) ) {
						$data = (string) $script_obj->extra['data'];

						// Check for div#main or div#content as workaround.
						if ( preg_match( '/<div[^>]*id=["\']?(main|content|primary)["\']?[^>]*>/', $data ) ) {
							$has_main_div = true;

							if ( empty( $missing_main ) ) {
								$missing_main[] = array(
									'issue'      => __( '<main> element missing; using <div id="main"> instead', 'wpshadow' ),
									'workaround' => true,
									'recommendation' => __( 'Replace <div id="main"> with semantic <main> element', 'wpshadow' ),
								);
							}
							break;
						}
					}
				}
			}
		}

		if ( empty( $missing_main ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $missing_main, 0, $max_items ) as $item ) {
			$items_list .= sprintf( "\n- %s", esc_html( $item['issue'] ) );
		}

		if ( count( $missing_main ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more issues", 'wpshadow' ),
				count( $missing_main ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d missing <main> element issue(s). Every page should contain exactly one <main> element to identify the primary content area. This is essential for accessibility and SEO. Do not use <div id="main"> as a substitute.%2$s', 'wpshadow' ),
				count( $missing_main ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-missing-main-element',
			'meta'         => array(
				'missing' => $missing_main,
			),
		);
	}
}
