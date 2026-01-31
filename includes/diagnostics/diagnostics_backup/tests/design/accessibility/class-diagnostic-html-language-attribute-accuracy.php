<?php
/**
 * HTML Language Attribute Accuracy Diagnostic
 *
 * Validates HTML lang attribute matches actual content language.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTML Language Attribute Accuracy Class
 *
 * Tests HTML lang attribute.
 *
 * @since 1.26028.1905
 */
class Diagnostic_Html_Language_Attribute_Accuracy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-language-attribute-accuracy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'HTML Language Attribute Accuracy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates HTML lang attribute matches actual content language';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$lang_check = self::check_language_attribute();
		
		if ( ! $lang_check['is_accurate'] ) {
			$issues = array();
			
			if ( ! $lang_check['has_lang_attribute'] ) {
				$issues[] = __( 'HTML <lang> attribute missing (screen reader pronunciation issue)', 'wpshadow' );
			}

			if ( $lang_check['mismatch_with_wp_setting'] ) {
				$issues[] = sprintf(
					/* translators: 1: HTML lang, 2: WordPress locale */
					__( 'HTML lang="%1$s" does not match WordPress locale "%2$s"', 'wpshadow' ),
					$lang_check['html_lang'],
					$lang_check['wp_locale']
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/html-language-attribute-accuracy',
				'meta'         => array(
					'has_lang_attribute'      => $lang_check['has_lang_attribute'],
					'html_lang'               => $lang_check['html_lang'],
					'wp_locale'               => $lang_check['wp_locale'],
					'mismatch_with_wp_setting' => $lang_check['mismatch_with_wp_setting'],
				),
			);
		}

		return null;
	}

	/**
	 * Check language attribute accuracy.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_language_attribute() {
		$check = array(
			'is_accurate'              => true,
			'has_lang_attribute'       => false,
			'html_lang'                => '',
			'wp_locale'                => '',
			'mismatch_with_wp_setting' => false,
		);

		// Get WordPress locale setting.
		$check['wp_locale'] = get_locale();

		// Get homepage HTML.
		$response = wp_remote_get( get_home_url(), array( 'timeout' => 10 ) );
		
		if ( is_wp_error( $response ) ) {
			return $check;
		}

		$html = wp_remote_retrieve_body( $response );

		// Check for lang attribute.
		if ( preg_match( '/<html[^>]*\slang=["\']([^"\']+)["\']/i', $html, $matches ) ) {
			$check['has_lang_attribute'] = true;
			$check['html_lang'] = $matches[1];

			// Check if lang matches WordPress locale.
			$expected_lang = self::locale_to_lang_code( $check['wp_locale'] );
			
			if ( strtolower( $check['html_lang'] ) !== strtolower( $expected_lang ) ) {
				$check['mismatch_with_wp_setting'] = true;
				$check['is_accurate'] = false;
			}
		} else {
			$check['has_lang_attribute'] = false;
			$check['is_accurate'] = false;
		}

		return $check;
	}

	/**
	 * Convert WordPress locale to BCP 47 language code.
	 *
	 * @since  1.26028.1905
	 * @param  string $locale WordPress locale.
	 * @return string BCP 47 language code.
	 */
	private static function locale_to_lang_code( $locale ) {
		// WordPress uses underscore, BCP 47 uses hyphen.
		$lang_code = str_replace( '_', '-', $locale );

		// Handle special cases.
		if ( 'en-US' === $lang_code ) {
			return 'en';
		}

		return $lang_code;
	}
}
