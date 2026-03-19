<?php
/**
 * Page Builder Media Picker Conflicts Treatment
 *
 * Tests media picker functionality in popular page builders (Elementor, Divi, Beaver Builder)
 * and detects modal conflicts, JavaScript errors, and integration issues.
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
 * Page Builder Media Picker Conflicts Treatment Class
 *
 * Detects conflicts between WordPress media picker and page builders.
 *
 * @since 1.6093.1200
 */
class Treatment_Page_Builder_Media_Picker_Conflicts extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'page-builder-media-picker-conflicts';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Page Builder Media Picker Conflicts';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests media picker in page builders and detects modal conflicts';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'integrations';

	/**
	 * Run the treatment check.
	 *
	 * Checks:
	 * - Popular page builders are active
	 * - Media library scripts are properly enqueued
	 * - No conflicting plugins interfering with media modal
	 * - Page builder-specific media settings
	 * - JavaScript errors in media library
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Page_Builder_Media_Picker_Conflicts' );
	}
}
