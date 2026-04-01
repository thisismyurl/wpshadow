<?php
/**
 * Taxonomy Permalink Structure Treatment
 *
 * Tests custom taxonomy permalink structures and validates URL rewriting.
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
 * Taxonomy Permalink Structure Treatment Class
 *
 * Validates that custom taxonomy permalink structures are properly configured
 * and URL rewriting is working correctly.
 *
 * @since 0.6093.1200
 */
class Treatment_Taxonomy_Permalink_Structure extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'taxonomy-permalink-structure';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Taxonomy Permalink Structure';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests custom taxonomy permalink structures and validates URL rewriting';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * Checks if permalinks are enabled and validates custom taxonomy URL rewriting.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Taxonomy_Permalink_Structure' );
	}
}
