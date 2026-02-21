<?php
/**
 * Search Performance Treatment
 *
 * Evaluates WordPress search functionality for performance impact and
 * recommends optimizations to reduce load on search queries.
 *
 * @since   1.6033.2088
 * @package WPShadow\Treatments
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
 * Verifies search optimization:
 * - Search indexing
 * - Search query count
 * - Search results caching
 * - Search plugin usage
 * - Full-text search capabilities
 *
 * @since 1.6033.2088
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
	protected static $description = 'Evaluates search functionality for performance optimization';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2088
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Search_Performance' );
	}
}
