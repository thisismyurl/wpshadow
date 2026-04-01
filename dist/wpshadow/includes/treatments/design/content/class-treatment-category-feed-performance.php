<?php
/**
 * Category Feed Performance Treatment
 *
 * Analyzes performance impact of category feed generation.
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
 * Category Feed Performance Treatment Class
 *
 * Checks performance implications of category feeds.
 *
 * @since 0.6093.1200
 */
class Treatment_Category_Feed_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'category-feed-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Category Feed Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes category feed performance impact';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'reading';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Category_Feed_Performance' );
	}
}
