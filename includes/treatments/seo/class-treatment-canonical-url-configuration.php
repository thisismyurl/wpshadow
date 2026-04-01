<?php
/**
 * Canonical URL Configuration Treatment
 *
 * Verifies that canonical URLs are properly configured to prevent duplicate
 * content issues and ensure search engines understand the preferred URL version.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Treatments\Helpers\Treatment_URL_And_Pattern_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Canonical URL Configuration Treatment Class
 *
 * Checks for proper canonical URL implementation to avoid duplicate content
 * penalties and ensure SEO best practices.
 *
 * @since 0.6093.1200
 */
class Treatment_Canonical_URL_Configuration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'canonical-url-configuration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Canonical URL Configuration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies canonical URL implementation for SEO';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'permalinks';

	/**
	 * Run the treatment check.
	 *
	 * Checks:
	 * - WordPress canonical URL support enabled
	 * - Canonical tags properly implemented
	 * - No conflicting canonical plugins
	 * - Home URL canonicalization
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Canonical_URL_Configuration' );
	}
}
