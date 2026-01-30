<?php
/**
 * HTML Detect Multiple Conflicting Language Attributes Diagnostic
 *
 * Detects conflicting language attributes in HTML.
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
 * HTML Detect Multiple Conflicting Language Attributes Diagnostic Class
 *
 * Identifies pages with conflicting or multiple language attributes that
 * confuse search engines and screen readers.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Multiple_Conflicting_Language_Attributes extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-multiple-conflicting-language-attributes';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Conflicting Language Attributes';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects multiple or conflicting language attributes';

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

		$language_issues = array();
		$html_lang       = null;
		$meta_langs      = array();

		// Check scripts for HTML lang attribute and meta language tags.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Check for html lang attribute.
					if ( preg_match( '/<html[^>]*\s+lang=["\']?([a-z]{2}(?:-[a-z]{2})?)["\']?[^>]*>/i', $data, $m ) ) {
						$html_lang = strtolower( $m[1] );
					}

					// Check for meta language tags.
					if ( preg_match_all( '/<meta[^>]*(?:http-equiv|name)=["\']content-language["\'][^>]*content=["\']([^"\']+)["\'][^>]*>/i', $data, $matches ) ) {
						$meta_langs = array_merge( $meta_langs, $matches[1] );
					}

					// Check for og:locale.
					if ( preg_match_all( '/<meta[^>]*property=["\']og:locale["\'][^>]*content=["\']([^"\']+)["\'][^>]*>/i', $data, $matches ) ) {
						$meta_langs = array_merge( $meta_langs, $matches[1] );
					}
				}
			}
		}

		// Check for conflicts.
		if ( ! empty( $html_lang ) && ! empty( $meta_langs ) ) {
			$meta_langs_normalized = array_map( function( $lang ) {
				return strtolower( substr( $lang, 0, 2 ) );
			}, $meta_langs );

			foreach ( $meta_langs_normalized as $meta_lang ) {
				if ( substr( $html_lang, 0, 2 ) !== $meta_lang ) {
					$language_issues[] = array(
						'html_lang'  => $html_lang,
						'meta_lang'  => $meta_lang,
						'issue'      => sprintf(
							__( 'HTML lang="%s" conflicts with meta content-language="%s"', 'wpshadow' ),
							$html_lang,
							$meta_lang
						),
					);
				}
			}
		}

		// Check for multiple different meta language tags.
		if ( count( array_unique( $meta_langs ) ) > 1 ) {
			$language_issues[] = array(
				'issue' => sprintf(
					/* translators: %s: language list */
					__( 'Multiple conflicting meta language tags: %s', 'wpshadow' ),
					implode( ', ', array_unique( $meta_langs ) )
				),
			);
		}

		if ( ! empty( $language_issues ) ) {
			$items_list = '';
			$max_items  = 3;

			foreach ( array_slice( $language_issues, 0, $max_items ) as $item ) {
				$items_list .= "\n- " . esc_html( $item['issue'] );
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: issues list */
					__( 'Found conflicting language attributes. Search engines and screen readers use the HTML <html lang="..."> attribute to determine page language. If you also have conflicting meta tags, they cause confusion. Define language once in the HTML tag and remove conflicting meta tags.%s', 'wpshadow' ),
					$items_list
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/html-detect-multiple-conflicting-language-attributes',
				'meta'         => array(
					'html_lang'     => $html_lang,
					'meta_langs'    => $meta_langs,
					'issues'        => $language_issues,
				),
			);
		}

		return null;
	}
}
