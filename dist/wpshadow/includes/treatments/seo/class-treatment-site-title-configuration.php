<?php
/**
 * Site Title Configuration
 *
 * Checks if site title is properly configured.
 *
 * @package    WPShadow
 * @subpackage Treatments\Configuration
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Site_Title_Configuration Class
 *
 * Validates site title configuration.
 *
 * @since 0.6093.1200
 */
class Treatment_Site_Title_Configuration extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-title-configuration';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Site Title Configuration';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates site title configuration for SEO and branding';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * Tests site title configuration.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Site_Title_Configuration' );
	}
}
