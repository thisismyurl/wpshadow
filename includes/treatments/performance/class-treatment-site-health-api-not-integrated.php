<?php
/**
 * Site Health API Not Integrated Treatment
 *
 * Tests for WordPress Site Health integration.
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
 * Site Health API Not Integrated Treatment Class
 *
 * Tests for WordPress Site Health integration.
 *
 * @since 0.6093.1200
 */
class Treatment_Site_Health_API_Not_Integrated extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'site-health-api-not-integrated';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Site Health API Not Integrated';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for WordPress Site Health integration';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Site_Health_API_Not_Integrated' );
	}
}
