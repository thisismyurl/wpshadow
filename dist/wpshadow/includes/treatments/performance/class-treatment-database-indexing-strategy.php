<?php
/**
 * Database Indexing Strategy Treatment
 *
 * Analyzes database indexes and identifies missing indexes.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Indexing Strategy Treatment
 *
 * Evaluates database index usage and optimization opportunities.
 *
 * @since 0.6093.1200
 */
class Treatment_Database_Indexing_Strategy extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-indexing-strategy';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Database Indexing Strategy';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes database indexes and identifies missing indexes';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Database_Indexing_Strategy' );
	}
}
