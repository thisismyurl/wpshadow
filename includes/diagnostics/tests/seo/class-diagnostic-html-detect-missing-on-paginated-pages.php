<?php
/**
 * HTML Detect Missing Link Rel Next On Paginated Pages Diagnostic
 *
 * Detects missing rel="next" on paginated content.
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
 * HTML Detect Missing Link Rel Next On Paginated Pages Diagnostic Class
 *
 * Identifies paginated content pages without rel="next" links, which helps
 * search engines understand pagination.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Missing_On_Paginated_Pages extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-missing-on-paginated-pages';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Pagination Rel Links';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing rel="prev" and rel="next" on paginated pages';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

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

		// Check if this is a paginated page.
		global $post;

		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) {
			return null;
		}

		// Check if post has pagination (more than 1 page).
		$num_pages = substr_count( $post->post_content, '<!--nextpage-->' ) + 1;

		// Also check if it's a paginated archive.
		$is_archive = is_archive() || is_home();
		$paged      = get_query_var( 'paged' );

		if ( 1 === $num_pages && ! $paged ) {
			return null; // Not paginated
		}

		$has_rel_next = false;
		$has_rel_prev = false;

		// Check scripts for pagination links.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Check for rel="next".
					if ( preg_match( '/<link[^>]*rel=["\']next["\'][^>]*>/i', $data ) ) {
						$has_rel_next = true;
					}

					// Check for rel="prev".
					if ( preg_match( '/<link[^>]*rel=["\']prev["\'][^>]*>/i', $data ) ) {
						$has_rel_prev = true;
					}
				}
			}
		}

		$missing = array();

		if ( ! $has_rel_next ) {
			$missing[] = 'rel="next"';
		}

		if ( ! $has_rel_prev && $paged ) {
			$missing[] = 'rel="prev"';
		}

		if ( ! empty( $missing ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: missing links */
					__( 'Missing pagination rel links: %s. On paginated pages, search engines need rel="next" and rel="prev" links to understand pagination structure. Add: <link rel="next" href="..."> and <link rel="prev" href="..."> to your <head>.', 'wpshadow' ),
					implode( ', ', $missing )
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/html-detect-missing-on-paginated-pages',
				'meta'         => array(
					'is_paginated'    => true,
					'has_rel_next'    => $has_rel_next,
					'has_rel_prev'    => $has_rel_prev,
					'page_number'     => $paged ? $paged : 1,
				),
			);
		}

		return null;
	}
}
