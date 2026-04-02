<?php
/**
 * Lazy Loading Implemented Treatment
 *
 * Tests if images and content are properly lazy loaded
 * for improved initial page load performance.
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
 * Lazy Loading Implemented Treatment Class
 *
 * Evaluates whether the site has proper lazy loading
 * implementation for images, iframes, and content.
 *
 * @since 1.6093.1200
 */
class Treatment_Lazy_Loading_Implemented extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'implements-lazy-loading';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Lazy Loading Implemented';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if images and content are lazy loaded';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the lazy loading implementation treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if lazy loading issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Lazy_Loading_Implemented' );
	}
}
