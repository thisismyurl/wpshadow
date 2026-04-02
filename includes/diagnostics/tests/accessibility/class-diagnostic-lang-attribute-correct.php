<?php
/**
 * HTML Lang Attribute Correct Diagnostic
 *
 * Verifies that the site's configured language produces a valid BCP-47
 * language tag in the HTML <html lang=""> attribute. Screen readers rely
 * on this tag to select the correct pronunciation engine and dictionary.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Lang_Attribute_Correct Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Lang_Attribute_Correct extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'lang-attribute-correct';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'HTML Lang Attribute Correct';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks that the site language produces a valid BCP-47 tag for the HTML lang attribute so screen readers use the correct pronunciation and dictionary.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * Reads the language tag WordPress would render as the HTML lang attribute
	 * via get_bloginfo('language') and validates it against the BCP-47 format.
	 * An empty value or a malformed tag both trigger a finding.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		// WordPress derives this from the WPLANG/site-language setting.
		// It returns a BCP-47 tag such as 'en-US' or 'fr-FR'.
		$lang   = (string) get_bloginfo( 'language' );
		$locale = (string) get_locale();

		if ( '' === trim( $lang ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'The HTML lang attribute is empty. Screen readers rely on this tag to select the correct language engine and dictionary for your content.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/lang-attribute-correct?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'locale'   => $locale,
					'lang_tag' => '',
					'fix'      => __( 'Go to Settings &rsaquo; General, choose the correct Site Language for your content, and save.', 'wpshadow' ),
				),
			);
		}

		// BCP-47 basic structure:
		// primary language subtag (2-3 letters) optionally followed by region,
		// script, or extension subtags separated by hyphens.
		if ( ! preg_match( '/^[a-zA-Z]{2,3}(-[a-zA-Z0-9]{2,8})*$/', $lang ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %s: the invalid language tag value */
					__( 'The HTML lang attribute value &#8220;%s&#8221; does not match BCP-47 format. Screen readers may fall back to a default language and mispronounce your content.', 'wpshadow' ),
					esc_html( $lang )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/lang-attribute-correct?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'locale'   => $locale,
					'lang_tag' => $lang,
					'fix'      => __( 'Go to Settings &rsaquo; General and select a valid Site Language. The language tag must follow BCP-47 format (e.g., en-US, fr-FR, de-DE).', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
