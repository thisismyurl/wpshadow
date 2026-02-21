<?php
/**
 * Noindex For Staging Environment Not Configured Treatment
 *
 * Checks if staging environment noindex is configured.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Noindex For Staging Environment Not Configured Treatment Class
 *
 * Detects missing staging noindex configuration.
 *
 * @since 1.6030.2352
 */
class Treatment_Noindex_For_Staging_Environment_Not_Configured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'noindex-for-staging-environment-not-configured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Noindex For Staging Environment Not Configured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if staging environment noindex is configured';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Noindex_For_Staging_Environment_Not_Configured' );
	}
}
