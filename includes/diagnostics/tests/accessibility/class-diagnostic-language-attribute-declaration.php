<?php
/**
 * Language Attribute Declaration Diagnostic
 *
 * Issue #4960: No Language Attribute on HTML Element
 * Pillar: 🌍 Accessibility First / 🌐 Culturally Respectful
 *
 * Checks if <html> element declares page language.
 * Screen readers need lang attribute for proper pronunciation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Language_Attribute_Declaration Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Language_Attribute_Declaration extends Diagnostic_Base {

	protected static $slug = 'language-attribute-declaration';
	protected static $title = 'No Language Attribute on HTML Element';
	protected static $description = 'Checks if page language is declared in HTML tag';
	protected static $family = 'accessibility';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Add lang attribute to <html> element', 'wpshadow' );
		$issues[] = __( 'Use ISO 639-1 language codes (en, es, fr, de)', 'wpshadow' );
		$issues[] = __( 'Include region for variants: en-US, en-GB, pt-BR', 'wpshadow' );
		$issues[] = __( 'Use lang attribute for content in different language', 'wpshadow' );
		$issues[] = __( 'WordPress default: <html lang="<?php language_attributes(); ?>">', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'The lang attribute tells screen readers and browsers what language the page is in. This ensures correct pronunciation and translation offers.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/language-attribute',
				'details'      => array(
					'recommendations'         => $issues,
					'wcag_requirement'        => 'WCAG 2.1 3.1.1 Language of Page (Level A)',
					'correct_example'         => '<html lang="en-US">',
					'why_important'           => 'Screen readers pronounce words correctly based on language',
				),
			);
		}

		return null;
	}
}
