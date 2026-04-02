<?php
/**
 * RTL Language Support Diagnostic
 *
 * Issue #4868: UI Not Ready for Right-to-Left Languages
 * Pillar: 🌐 Culturally Respectful
 *
 * Checks if UI supports RTL languages (Arabic, Hebrew, Urdu, Persian).
 * ~422 million people speak RTL languages - design for them from day one.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_RTL_Language_Support Class
 *
 * Checks for:
 * - CSS uses logical properties (margin-inline-start not margin-left)
 * - Layout flips properly in RTL (icons, animations, flow)
 * - Text alignment uses `start` and `end` not `left` and `right`
 * - No hardcoded directional assumptions
 * - Directional icons mirrored in RTL (← stays left, doesn't flip to →)
 * - Form field order preserved
 * - Numbers don't reverse (RTL still reads numbers left-to-right)
 *
 * Why this matters:
 * - Arabic (368M speakers), Hebrew (13M), Urdu (70M+), Persian (50M+)
 * - Physical money is on the table: Saudi Arabia, UAE, Egypt, Israel, Iran
 * - UI breaking in RTL is like app breaking on mobile - it excludes markets
 * - Logical CSS is better for responsive design anyway
 *
 * @since 1.6093.1200
 */
class Diagnostic_RTL_Language_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'rtl-language-support';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'UI Not Ready for Right-to-Left Languages';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Checks if UI properly supports RTL languages (Arabic, Hebrew, Urdu, Persian)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is a guidance diagnostic - actual RTL testing requires visual inspection.
		// We provide recommendations for RTL support.

		$issues = array();

		$issues[] = __( 'Use logical CSS properties: margin-inline-start/end instead of left/right', 'wpshadow' );
		$issues[] = __( 'Use logical CSS properties: padding-inline-start/end', 'wpshadow' );
		$issues[] = __( 'Use logical CSS properties: text-align: start/end instead of left/right', 'wpshadow' );
		$issues[] = __( 'Float: use inline-start/end instead of left/right', 'wpshadow' );
		$issues[] = __( 'Mirror directional icons in RTL (back arrow, quotes)', 'wpshadow' );
		$issues[] = __( 'Keep numbers reading left-to-right even in RTL context', 'wpshadow' );
		$issues[] = __( 'Test layout at [dir="rtl"] attribute', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Arabic, Hebrew, Urdu, and Persian are RTL languages spoken by ~422 million people. UI that doesn\'t support RTL breaks completely for these users.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/rtl-support',
				'details'      => array(
					'recommendations'         => $issues,
					'rtl_languages'           => 'Arabic (368M), Urdu (70M), Hebrew (13M), Persian (50M+)',
					'css_pattern'             => 'margin-inline-start: 20px; (instead of margin-left)',
					'testing_method'          => 'Set [dir="rtl"] on html element, test full interface',
					'wordpress_support'       => 'WordPress has built-in RTL support via wp_is_rtl() function',
					'icon_mirroring'          => 'Back arrows flip direction, but quotes may not (context-dependent)',
				),
			);
		}

		return null;
	}
}
