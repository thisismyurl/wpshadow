<?php
/**
 * No Language Translation Support Diagnostic
 *
 * Detects when multi-language support is not available,
 * limiting international audience reach.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Localization
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Language Translation Support
 *
 * Checks whether multi-language support is
 * implemented for international audiences.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Language_Translation_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-language-translation-support';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Language Translation Support';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether multi-language support exists';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'localization';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for translation plugins
		$has_translation = is_plugin_active( 'wpml/sitepress.php' ) ||
			is_plugin_active( 'polylang/polylang.php' ) ||
			is_plugin_active( 'weglot/weglot.php' );

		if ( ! $has_translation ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Multi-language support isn\'t available, which limits audience to English speakers. Translation plugins enable: multiple language versions, automatic language detection, language switcher. Markets: Spanish (500M speakers), Mandarin (1B), French, German, Portuguese. Translating into 3 languages can 3x your addressable market. Translation plugins: WPML, Polylang, Weglot. Start with your top 2-3 languages.',
					'wpshadow'
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'International Market Reach',
					'potential_gain' => '3x addressable market with 3 language versions',
					'roi_explanation' => 'Multi-language support expands addressable market significantly, enabling growth in Spanish, Mandarin, and other major markets.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/language-translation-support?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
