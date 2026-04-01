<?php
/**
 * Site Tagline Missing or Poorly Configured Treatment
 *
 * Tests for site tagline configuration.
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
 * Site Tagline Missing or Poorly Configured Treatment Class
 *
 * Tests for proper site tagline configuration.
 *
 * @since 0.6093.1200
 */
class Treatment_Site_Tagline_Missing_Or_Poorly_Configured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-tagline-missing-or-poorly-configured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Site Tagline Missing or Poorly Configured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for proper site tagline configuration';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Site_Tagline_Missing_Or_Poorly_Configured' );
	}
}
