<?php
/**
 * HTML Confirm Language Attribute Matches Content Language Diagnostic
 *
 * Confirms language attribute matches actual content language.
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
 * HTML Confirm Language Attribute Matches Content Language Diagnostic Class
 *
 * Identifies pages where the HTML language attribute doesn't match the
 * actual content language, which confuses search engines and accessibility tools.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Confirm_Language_Attribute_Matches_Content_Language extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-confirm-language-attribute-matches-content-language';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Language Attribute May Not Match Content';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates HTML language attribute matches content';

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

		$html_lang = null;

		// Check scripts for HTML lang attribute.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Check for html lang attribute.
					if ( preg_match( '/<html[^>]*\s+lang=["\']?([a-z]{2}(?:-[a-z]{2})?)["\']?[^>]*>/i', $data, $m ) ) {
						$html_lang = strtolower( $m[1] );
						break;
					}
				}
			}
		}

		// Get WordPress site language.
		$site_lang = get_locale();

		if ( empty( $html_lang ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: site language */
					__( 'HTML missing language attribute. Your site is configured for "%s" but the HTML <html> tag has no lang attribute. Add: <html lang="%s">', 'wpshadow' ),
					esc_html( $site_lang ),
					esc_html( substr( str_replace( '_', '-', $site_lang ), 0, 5 ) )
				),
				'severity'     => 'medium',
				'threat_level' => 30,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/html-confirm-language-attribute-matches-content-language',
				'meta'         => array(
					'site_language' => $site_lang,
					'html_lang'     => null,
				),
			);
		}

		// Simple check: if WordPress is configured for a different language.
		$wp_lang_short = substr( str_replace( '_', '-', $site_lang ), 0, 2 );
		$html_lang_short = substr( $html_lang, 0, 2 );

		if ( $wp_lang_short !== $html_lang_short && $site_lang !== 'en_US' ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: site language, 2: HTML lang */
					__( 'HTML language attribute may not match content. Site is configured for "%1$s" but HTML has lang="%2$s". Ensure they match so search engines and screen readers display content correctly.', 'wpshadow' ),
					esc_html( $site_lang ),
					esc_html( $html_lang )
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/html-confirm-language-attribute-matches-content-language',
				'meta'         => array(
					'site_language' => $site_lang,
					'html_lang'     => $html_lang,
				),
			);
		}

		return null;
	}
}
