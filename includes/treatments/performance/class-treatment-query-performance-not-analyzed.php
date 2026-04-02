<?php
/**
 * Query Performance Not Analyzed Treatment
 *
 * Checks query performance.
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
 * Treatment_Query_Performance_Not_Analyzed Class
 *
 * Performs treatment check for Query Performance Not Analyzed.
 *
 * @since 1.6093.1200
 */
class Treatment_Query_Performance_Not_Analyzed extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'query-performance-not-analyzed';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Query Performance Not Analyzed';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks query performance';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Query_Performance_Not_Analyzed' );
	}
						return null;
						}
						return null;
	}
}
