<?php
/**
 * Local Schema Markup Treatment
 *
 * Tests whether the site properly implements LocalBusiness schema markup for rich
 * snippet eligibility. Proper schema markup significantly improves local search visibility.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Implements_Local_Schema_Markup Class
 *
 * Treatment #19: Local Schema Markup from Specialized & Emerging Success Habits.
 * Checks if the site implements LocalBusiness schema markup correctly.
 *
 * @since 0.6093.1200
 */
class Treatment_Implements_Local_Schema_Markup extends Treatment_Base {

	protected static $slug = 'implements-local-schema-markup';
	protected static $title = 'Local Schema Markup';
	protected static $description = 'Tests whether the site properly implements LocalBusiness schema markup for rich snippets';
	protected static $family = 'local-seo';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Implements_Local_Schema_Markup' );
	}
}
