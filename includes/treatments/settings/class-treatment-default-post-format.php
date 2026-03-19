<?php
/**
 * Default Post Format Treatment
 *
 * Verifies that the site's post format settings are properly configured
 * to support the content types being published.
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
 * Default Post Format Treatment Class
 *
 * Ensures post format support is properly configured.
 *
 * @since 1.6093.1200
 */
class Treatment_Default_Post_Format extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'default-post-format';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Default Post Format';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies post format support is configured';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the treatment check.
	 *
	 * Checks:
	 * - Post formats are supported by theme
	 * - Post format taxonomy is properly registered
	 * - Format choice makes sense for site type
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Default_Post_Format' );
	}
}
