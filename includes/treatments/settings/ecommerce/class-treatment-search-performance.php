<?php
/**
 * Search Performance Treatment
 *
 * Checks if product search performs with <500ms response time.
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
 * Search Performance Treatment Class
 *
 * Verifies that product search returns results quickly and that the
 * search functionality is optimized.
 *
 * @since 0.6093.1200
 */
class Treatment_Search_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'search-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Search Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if product search performs with <500ms response time';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the search performance treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if search performance issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Search_Performance' );
	}
}
