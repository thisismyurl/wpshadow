<?php
/**
 * HTML Detect Missing Language Attribute On HTML Diagnostic
 *
 * Detects missing language attribute on HTML tag.
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
 * HTML Detect Missing Language Attribute On HTML Diagnostic Class
 *
 * Identifies pages without the required HTML language attribute, which
 * is critical for SEO and accessibility.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Missing_Language_Attribute_On extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-missing-language-attribute-on';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing HTML Language Attribute';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing lang attribute on <html> tag';

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

		$has_lang_attribute = false;

		// Check scripts for HTML lang attribute.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Check for html lang attribute.
					if ( preg_match( '/<html[^>]*\s+lang=["\']?[a-z]{2}["\']?[^>]*>/i', $data ) ) {
						$has_lang_attribute = true;
						break;
					}
				}
			}
		}

		if ( ! $has_lang_attribute ) {
			$site_lang = get_locale();
			$lang_code = substr( str_replace( '_', '-', $site_lang ), 0, 5 );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: language code */
					__( 'Missing language attribute on <html> tag. The lang attribute is essential for SEO and accessibility—it tells search engines and screen readers what language your site uses. Your site is configured for "%s", so add: <html lang="%s">', 'wpshadow' ),
					esc_html( $site_lang ),
					esc_html( $lang_code )
				),
				'severity'     => 'medium',
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/html-detect-missing-language-attribute-on',
				'meta'         => array(
					'site_language'  => $site_lang,
					'recommended_lang' => $lang_code,
				),
			);
		}

		return null;
	}
}
