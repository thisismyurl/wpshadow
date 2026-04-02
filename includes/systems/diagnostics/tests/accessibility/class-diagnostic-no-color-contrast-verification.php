<?php
/**
 * No Color Contrast Verification Diagnostic
 *
 * Detects when text lacks sufficient color contrast,
 * making content unreadable for low-vision users.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Accessibility
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Color Contrast Verification
 *
 * Checks whether text has sufficient color contrast
 * for accessibility compliance.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Color_Contrast_Verification extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-color-contrast-verification';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Color Contrast Verification';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether color contrast is adequate';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// This is a guidance diagnostic - color contrast must be manually checked
		// Check if accessibility auditing has been documented
		$has_contrast_audit = get_option( 'wpshadow_contrast_audit_completed' );

		if ( ! $has_contrast_audit ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Color contrast hasn\'t been verified, which makes text unreadable for low-vision users. WCAG AA requires: 4.5:1 contrast for normal text, 3:1 for large text (18pt+). Common failures: light gray text on white (too subtle), dark text on dark background. Tools: WebAIM Contrast Checker, Chrome DevTools Accessibility tab. Test combinations: text color vs background color. This is 1 of the most common accessibility failures and easiest to fix.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Content Readability for Low-Vision Users',
					'potential_gain' => 'Enable 4-8% of users to read content comfortably',
					'roi_explanation' => 'Poor contrast makes text unreadable for ~4-8% of users with low vision or color blindness.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/color-contrast-verification',
			);
		}

		return null;
	}
}
