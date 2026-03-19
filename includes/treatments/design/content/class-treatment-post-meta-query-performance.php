<?php
/**
 * Post Meta Query Performance Treatment
 *
 * Measures performance of meta_query operations. Detects slow meta queries that
 * impact site performance and suggests optimization strategies.
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
 * Post Meta Query Performance Treatment Class
 *
 * Checks for performance issues in meta query operations.
 *
 * @since 1.6093.1200
 */
class Treatment_Post_Meta_Query_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-meta-query-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Post Meta Query Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Measures meta_query performance and detects slow operations';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Post_Meta_Query_Performance' );
	}
}
