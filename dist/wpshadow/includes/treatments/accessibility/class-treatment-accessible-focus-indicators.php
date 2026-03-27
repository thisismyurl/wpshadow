<?php
/**
 * Accessible Focus Indicators Treatment
 *
 * Issue #4889: Focus Indicators Removed or Invisible
 * Pillar: 🌍 Accessibility First
 *
 * Checks if keyboard focus is always visible.
 * Keyboard users need to see where they are on the page.
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
 * Treatment_Accessible_Focus_Indicators Class
 *
 * @since 1.6093.1200
 */
class Treatment_Accessible_Focus_Indicators extends Treatment_Base {

	protected static $slug = 'accessible-focus-indicators';
	protected static $title = 'Focus Indicators Removed or Invisible';
	protected static $description = 'Checks if keyboard focus is always visible for navigation';
	protected static $family = 'accessibility';

	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Accessible_Focus_Indicators' );
	}
}
