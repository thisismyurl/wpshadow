<?php
/**
 * Query Result Set Size Treatment
 *
 * Detects queries returning massive result sets that waste memory and time.
 *
 * **What This Check Does:**
 * 1. Identifies queries loading 10,000+ rows
 * 2. Measures memory consumed by result sets
 * 3. Flags queries loading unnecessary columns
 * 4. Detects pagination misses (loading all instead of paging)
 * 5. Analyzes impact on PHP memory usage
 * 6. Flags unbounded queries (no LIMIT)\n *
 * **Why This Matters:**\n * Loading 1 million rows into PHP memory = 1GB RAM wasted. Query at 5ms, but processing in PHP = 5
 * seconds + memory crash. A proper query with LIMIT 20 takes 1ms and uses 1MB. Same data, 1000x faster\n * and 1000x less memory.\n *
 * **Real-World Scenario:**\n * Admin report page tried to load ALL orders (500,000 orders) for analysis. Query returned 500k rows.
 * PHP tried to load into memory: 2GB immediately needed. Server only had 512MB. Page crashed (white
 * screen). After adding pagination (show 100 per page), user could page through results. Memory: 1MB per
 * page. Fast and responsive.\n *
 * **Business Impact:**\n * - Server crashes from memory exhaustion\n * - Admin pages timeout and become unusable\n * - Reports fail to generate\n * - Export operations fail\n * - Server upgrade needed ($100-$500+ monthly)\n *
 * **Philosophy Alignment:**\n * - #8 Inspire Confidence: Prevents memory-exhaustion crashes\n * - #9 Show Value: Enables data analysis that was previously impossible\n * - #10 Talk-About-Worthy: "We can now analyze millions of records"\n *
 * **Related Checks:**\n * - PHP Memory Limit (available memory)\n * - Query Timeout Risk (execution time)\n * - Database Index Efficiency (query optimization)\n * - Pagination Implementation (result limiting)\n *
 * **Learn More:**\n * - KB Article: https://wpshadow.com/kb/query-result-optimization\n * - Video: https://wpshadow.com/training/pagination-best-practices (6 min)\n * - Advanced: https://wpshadow.com/training/large-dataset-handling (12 min)\n *
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
 * Treatment_Query_Result_Set_Size Class
 *
 * Identifies queries that return excessively large result sets.
 */
class Treatment_Query_Result_Set_Size extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'query-result-set-size';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Query Result Set Size';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects queries returning excessive data that may cause memory issues';

	/**
	 * Treatment family
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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Query_Result_Set_Size' );
	}
}
