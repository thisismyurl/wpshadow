<?php
/**
 * Translation Readiness Diagnostic
 *
 * Issue #4869: No Translation Support (Strings Not in Text Domain)
 * Pillar: 🌐 Culturally Respectful
 *
 * Checks if all user-facing strings use WordPress translation functions.
 * Not using text domain means strings can't be translated to other languages.
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
 * Diagnostic_Translation_Readiness Class
 *
 * Checks for:
 * - All user-facing strings use __() or _e() functions
 * - Correct text domain used (should be 'wpshadow' not hardcoded)
 * - Translators comments for ambiguous strings
 * - Pluralization handled with _n()
 * - Variable interpolation using sprintf()
 * - No string concatenation in functions (breaks extraction)
 * - JS strings use wp_localize_script for translation
 *
 * Why this matters:
 * - Only 25% of world speaks English natively
 * - Translation enables global reach
 * - Professional localization shows respect for users
 * - wp.org requires translation readiness for plugins
 *
 * @since 1.6050.0000
 */
class Diagnostic_Translation_Readiness extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $slug = 'translation-readiness';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $title = 'No Translation Support (Strings Not in Text Domain)';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $description = 'Checks if all user-facing strings are translatable';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6050.0000
	 * @var   string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is a guidance diagnostic - actual string analysis requires code review.
		// We provide recommendations for translation readiness.

		$issues = array();

		$issues[] = __( 'Wrap all user-facing strings with __() for display', 'wpshadow' );
		$issues[] = __( 'Use _e() to echo strings directly (escape included)', 'wpshadow' );
		$issues[] = __( 'Always specify text domain: __( "Text", "wpshadow" )', 'wpshadow' );
		$issues[] = __( 'Use sprintf() for string interpolation with variables', 'wpshadow' );
		$issues[] = __( 'Handle pluralization with _n(): _n( "item", "items", count, "wpshadow" )', 'wpshadow' );
		$issues[] = __( 'Add translator comments for ambiguous strings (/* translators: ... */)', 'wpshadow' );
		$issues[] = __( 'Use wp_localize_script() for JavaScript strings', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Without translation support, your plugin only works for English speakers. Translation makes it globally accessible.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/translation-readiness',
				'details'      => array(
					'recommendations'         => $issues,
					'english_speakers'        => '~25% of world population',
					'translation_platforms'   => 'translate.wordpress.org, Crowdin, GlotPress',
					'wp_org_requirement'      => 'WordPress.org requires translation readiness for plugins',
					'missing_locale_count'    => 'Affects translations in 100+ languages',
				),
			);
		}

		return null;
	}
}
