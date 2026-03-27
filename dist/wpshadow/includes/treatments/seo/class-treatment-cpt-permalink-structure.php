<?php
/**
 * CPT Permalink Structure Treatment
 *
 * Checks if custom post type permalinks work correctly by validating
 * rewrite rules and URL structure. Detects broken rewrites and 404 errors.
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
 * CPT Permalink Structure Class
 *
 * Verifies custom post type permalinks are properly configured with
 * working rewrite rules. Detects issues causing 404 errors or incorrect URLs.
 *
 * @since 1.6093.1200
 */
class Treatment_CPT_Permalink_Structure extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'cpt-permalink-structure';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'CPT Permalink Structure';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if CPT permalinks work correctly';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the treatment check.
	 *
	 * Validates custom post type permalink configuration and rewrite rules.
	 * Detects broken rewrites, missing slugs, and slug conflicts.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if permalink issues found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_CPT_Permalink_Structure' );
	}
}
