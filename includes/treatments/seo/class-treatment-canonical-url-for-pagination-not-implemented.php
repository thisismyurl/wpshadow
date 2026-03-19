<?php
/**
 * Canonical URL For Pagination Not Implemented Treatment
 *
 * Checks if canonical URLs for pagination are implemented.
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
 * Canonical URL For Pagination Not Implemented Treatment Class
 *
 * Detects missing pagination canonical tags.
 *
 * @since 1.6093.1200
 */
class Treatment_Canonical_URL_For_Pagination_Not_Implemented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'canonical-url-for-pagination-not-implemented';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Canonical URL For Pagination Not Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if canonical URLs for pagination are implemented';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Canonical_URL_For_Pagination_Not_Implemented' );
	}
}
