<?php
/**
 * Heading Hierarchy Structure Treatment
 *
 * Issue #4892: Heading Structure Not Hierarchical (Skip Levels)
 * Pillar: 🌍 Accessibility First
 *
 * Checks if headings follow proper h1→h2→h3 structure.
 * Screen readers use headings to understand page structure.
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
 * Treatment_Heading_Hierarchy_Structure Class
 *
 * @since 1.6093.1200
 */
class Treatment_Heading_Hierarchy_Structure extends Treatment_Base {

	protected static $slug = 'heading-hierarchy-structure';
	protected static $title = 'Heading Structure Not Hierarchical (Skip Levels)';
	protected static $description = 'Checks if headings follow logical h1→h2→h3 hierarchy';
	protected static $family = 'accessibility';

	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Heading_Hierarchy_Structure' );
	}
}
