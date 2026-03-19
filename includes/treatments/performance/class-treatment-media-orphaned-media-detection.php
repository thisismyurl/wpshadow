<?php
/**
 * Media Orphaned Media Detection Treatment
 *
 * Identifies media files not attached to any posts and
 * detects unused media bloat.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Orphaned_Media_Detection Class
 *
 * Counts unattached media and warns if the volume is high.
 *
 * @since 1.6093.1200
 */
class Treatment_Media_Orphaned_Media_Detection extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-orphaned-media-detection';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Orphaned Media Detection';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies media not attached to any posts';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Orphaned_Media_Detection' );
	}
}
