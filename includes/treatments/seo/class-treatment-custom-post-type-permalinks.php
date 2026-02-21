<?php
/**
 * Custom Post Type Permalinks Treatment
 *
 * Validates CPT permalink structures and tests rewrite slug configuration.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6032.1402
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Post Type Permalinks Treatment Class
 *
 * Checks for properly configured permalink structures and rewrite rules
 * for custom post types to ensure clean, SEO-friendly URLs.
 *
 * @since 1.6032.1402
 */
class Treatment_Custom_Post_Type_Permalinks extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'custom-post-type-permalinks';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Custom Post Type Permalinks';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates CPT permalink structures and rewrite slug configuration';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * Validates custom post type permalink configurations including:
	 * - Rewrite slug configuration
	 * - Hierarchical structure support
	 * - Permalink conflicts with existing pages/posts
	 * - Archive page URL structure
	 *
	 * @since  1.6032.1402
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Custom_Post_Type_Permalinks' );
	}
}
