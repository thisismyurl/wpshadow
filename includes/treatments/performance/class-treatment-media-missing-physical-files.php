<?php
/**
 * Media Missing Physical Files Treatment
 *
 * Detects media library entries with missing physical files
 * on disk and identifies broken attachments.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Missing_Physical_Files Class
 *
 * Checks attachment records and verifies their files exist.
 *
 * @since 0.6093.1200
 */
class Treatment_Media_Missing_Physical_Files extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-missing-physical-files';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Physical Files';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects media entries with missing files on disk';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Missing_Physical_Files' );
	}
}
