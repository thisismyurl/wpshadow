<?php
/**
 * Media Descriptions for Assistive Tech Treatment
 *
 * Tests comprehensive descriptions for screen readers.
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
 * Media Descriptions for Assistive Tech Treatment Class
 *
 * Verifies that media library elements have comprehensive descriptions
 * including ARIA labels, titles, and descriptions for screen readers.
 *
 * @since 1.6093.1200
 */
class Treatment_Media_Descriptions_For_Assistive_Tech extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-descriptions-for-assistive-tech';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Descriptions for Assistive Tech';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests comprehensive descriptions for screen readers';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Descriptions_For_Assistive_Tech' );
	}
}
