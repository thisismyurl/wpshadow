<?php
/**
 * Multisite Database Per Site Not Optimized Diagnostic
 *
 * Checks if multisite is using database per site optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Database Per Site Not Optimized Diagnostic Class
 *
 * Detects multisite database configuration issues.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Multisite_Database_Per_Site_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'multisite-database-per-site-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multisite Database Per Site Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks multisite database optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only check on multisite installations
		if ( ! is_multisite() ) {
			return null;
		}

		// Check if site count indicates need for database optimization
		$site_count = get_blog_count();

		if ( $site_count > 10 && ! defined( 'MULTISITE_DB_PER_SITE' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( 'Multisite has %d sites but database-per-site optimization is not enabled. This may impact performance with many sites.', 'wpshadow' ),
					absint( $site_count )
				),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/multisite-database-per-site-not-optimized',
			);
		}

		return null;
	}
}
