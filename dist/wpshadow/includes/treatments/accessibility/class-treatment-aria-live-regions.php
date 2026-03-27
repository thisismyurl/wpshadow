<?php
/**
 * ARIA Live Regions Treatment
 *
 * Issue #4943: Dynamic Content Not Announced to Screen Readers
 * Pillar: 🌍 Accessibility First
 *
 * Checks if dynamic content uses ARIA live regions.
 * Screen readers miss content that appears without page reload.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_ARIA_Live_Regions Class
 *
 * @since 1.6093.1200
 */
class Treatment_ARIA_Live_Regions extends Treatment_Base {

	protected static $slug = 'aria-live-regions';
	protected static $title = 'Dynamic Content Not Announced to Screen Readers';
	protected static $description = 'Checks if dynamic content uses ARIA live regions';
	protected static $family = 'accessibility';

	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_ARIA_Live_Regions' );
	}
}
