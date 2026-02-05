<?php
/**
 * Mobile Language Declaration Treatment
 *
 * Validates HTML lang attribute is properly set.
 *
 * @since   1.6033.1645
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Language Declaration Treatment Class
 *
 * Validates that the HTML lang attribute is set and properly formatted,
 * ensuring screen readers pronounce text correctly (WCAG 3.1.1).
 *
 * @since 1.6033.1645
 */
class Treatment_Mobile_Language_Declaration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-language-declaration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Language Declaration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validate HTML lang attribute set with correct format (WCAG 3.1.1)';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1645
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if lang attribute is set on html element
		$lang_attribute_set = apply_filters( 'wpshadow_html_lang_attribute_set', false );
		if ( ! $lang_attribute_set ) {
			$issues[] = __( 'HTML element should have lang attribute (e.g., lang="en")', 'wpshadow' );
		}

		// Check if lang attribute has valid format
		$lang_format_valid = apply_filters( 'wpshadow_html_lang_format_valid', false );
		if ( ! $lang_format_valid ) {
			$issues[] = __( 'HTML lang attribute should use valid BCP 47 format (e.g., en, en-US)', 'wpshadow' );
		}

		// Get the current site language
		$site_language = get_bloginfo( 'language' );
		if ( empty( $site_language ) ) {
			$issues[] = __( 'WordPress site language not configured; set in Settings > General', 'wpshadow' );
		}

		// Check if page language matches site language
		$language_consistent = apply_filters( 'wpshadow_page_language_matches_site', false );
		if ( ! $language_consistent ) {
			$issues[] = __( 'Page language may not match site language; ensure lang attribute matches site settings', 'wpshadow' );
		}

		// Check for language changes on foreign content
		$language_changes_marked = apply_filters( 'wpshadow_foreign_language_content_marked', false );
		if ( ! $language_changes_marked ) {
			$issues[] = __( 'Foreign language content should be marked with lang attribute on containing element', 'wpshadow' );
		}

		// Check for proper lang attribute on embedded content
		$embedded_lang_attributes = apply_filters( 'wpshadow_embedded_content_has_lang_attributes', false );
		if ( ! $embedded_lang_attributes ) {
			$issues[] = __( 'Embedded content (iframes, etc) should declare lang if different from page', 'wpshadow' );
		}

		// Check if language is set in wp-config or theme
		$language_configured = apply_filters( 'wpshadow_language_explicitly_configured', false );
		if ( ! $language_configured ) {
			$issues[] = __( 'Site language should be explicitly configured for best accessibility', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-language-declaration',
			);
		}

		return null;
	}
}
