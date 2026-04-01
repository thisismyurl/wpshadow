<?php
/**
 * Missing Media Attachments After Import Treatment
 *
 * Detects posts with broken image links after import.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Treatments\Helpers\Treatment_Request_Helper;
use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Missing Media Attachments After Import Treatment Class
 *
 * Detects when imported posts reference images that failed to download.
 *
 * @since 0.6093.1200
 */
class Treatment_Missing_Media_Attachments_After_Import extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-media-attachments-after-import';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Media Attachments After Import';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects posts with broken image links after import';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Missing_Media_Attachments_After_Import' );
	}
}
