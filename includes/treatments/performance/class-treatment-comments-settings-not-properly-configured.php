<?php
/**
 * Comments Settings Not Properly Configured Treatment
 *
 * Tests for comment moderation settings.
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
 * Comments Settings Not Properly Configured Treatment Class
 *
 * Tests for comment moderation and configuration.
 *
 * @since 1.6093.1200
 */
class Treatment_Comments_Settings_Not_Properly_Configured extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comments-settings-not-properly-configured';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comments Settings Not Properly Configured';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests for comment moderation settings';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comments_Settings_Not_Properly_Configured' );
	}
}
