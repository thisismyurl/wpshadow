<?php
/**
 * Treatment: Tap Target Size Validation
 *
 * Validates that interactive elements meet minimum size requirements (44×44px)
 * for mobile touch interaction as per WCAG 2.5.5 and Apple/Material Design guidelines.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4026
 *
 * @package    WPShadow
 * @subpackage Treatments\Mobile
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tap Target Size Treatment
 *
 * Checks if interactive elements are large enough for mobile tapping.
 * Minimum 44×44px per WCAG 2.5.5, Apple HIG. Recommended 48×48px (Material Design).
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Tap_Target_Size extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-tap-target-size';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Tap Target Size Validation';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates interactive elements meet minimum 44×44px touch target size (WCAG 2.5.5)';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Check tap target sizes.
	 *
	 * This treatment checks theme CSS for minimum touch target sizes.
	 * Validates buttons, links, and interactive elements meet WCAG 2.5.5 requirements.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Tap_Target_Size' );
	}
}
