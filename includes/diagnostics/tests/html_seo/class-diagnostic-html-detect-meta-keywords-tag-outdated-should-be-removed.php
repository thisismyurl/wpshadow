<?php
/**
 * HTML Detect Meta Keywords Tag Outdated Should Be Removed Diagnostic
 *
 * Detects outdated meta keywords tag.
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
 * HTML Detect Meta Keywords Tag Outdated Should Be Removed Diagnostic Class
 *
 * Identifies pages still using the outdated meta keywords tag,
 * which search engines have ignored for years.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Meta_Keywords_Tag_Outdated_Should_Be_Removed extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-meta-keywords-tag-outdated-should-be-removed';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Outdated Meta Keywords Tag';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects obsolete meta keywords tag (no longer used by search engines)';

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

		$keywords_found = array();

		// Check scripts for meta keywords tag.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Look for meta keywords tag.
					if ( preg_match( '/<meta[^>]*name=["\']?keywords["\']?[^>]*content=["\']([^"\']*)["\'][^>]*>/i', $data, $m ) ) {
						$keywords = $m[1];

						$keywords_found[] = array(
							'handle'   => $handle,
							'keywords' => substr( $keywords, 0, 100 ),
							'length'   => strlen( $keywords ),
							'issue'    => __( 'Meta keywords tag is outdated and should be removed', 'wpshadow' ),
						);
					}

					// Alternative pattern: content before name.
					if ( preg_match( '/<meta[^>]*content=["\']([^"\']*)["\'][^>]*name=["\']?keywords["\']?[^>]*>/i', $data, $m ) ) {
						$keywords = $m[1];

						$keywords_found[] = array(
							'handle'   => $handle,
							'keywords' => substr( $keywords, 0, 100 ),
							'length'   => strlen( $keywords ),
							'issue'    => __( 'Meta keywords tag is outdated and should be removed', 'wpshadow' ),
						);
					}
				}
			}
		}

		if ( empty( $keywords_found ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $keywords_found, 0, $max_items ) as $item ) {
			$items_list .= sprintf(
				"\n- %s: \"%s...\"",
				esc_html( $item['handle'] ),
				esc_html( substr( $item['keywords'], 0, 50 ) )
			);
		}

		if ( count( $keywords_found ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more keywords tags", 'wpshadow' ),
				count( $keywords_found ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d meta keywords tag(s). This tag is deprecated and ignored by major search engines (Google, Bing, Yahoo) since the early 2000s due to keyword stuffing abuse. Remove it to clean up your HTML and reduce page bloat.%2$s', 'wpshadow' ),
				count( $keywords_found ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-meta-keywords-tag-outdated-should-be-removed',
			'meta'         => array(
				'keywords' => $keywords_found,
			),
		);
	}
}
