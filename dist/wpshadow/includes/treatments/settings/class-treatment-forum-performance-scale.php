<?php
/**
 * Forum Performance at Scale Treatment
 *
 * Verifies forum sites are optimized for high traffic and large datasets
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments\Forum;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Treatment_ForumPerformanceScale Class
 *
 * Checks for caching, database optimization, CDN, lazy loading
 *
 * @since 0.6093.1200
 */
class Treatment_ForumPerformanceScale extends Treatment_Base {

/**
 * The treatment slug
 *
 * @var string
 */
protected static $slug = 'forum-performance-scale';

/**
 * The treatment title
 *
 * @var string
 */
protected static $title = 'Forum Performance at Scale';

/**
 * The treatment description
 *
 * @var string
 */
protected static $description = 'Verifies forum sites are optimized for high traffic and large datasets';

/**
 * The family this treatment belongs to
 *
 * @var string
 */
protected static $family = 'forum';

/**
 * Run the treatment check.
 *
 * @since 0.6093.1200
 * @return array|null Finding array if issue found, null otherwise.
 */
public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Forum\\Diagnostic_ForumPerformanceScale' );
	}
}

