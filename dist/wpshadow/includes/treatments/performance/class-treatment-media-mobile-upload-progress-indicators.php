<?php
/**
 * Media Mobile Upload Progress Indicators Treatment
 *
 * Checks if mobile users receive proper upload progress feedback.
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
 * Media Mobile Upload Progress Indicators Treatment Class
 *
 * Verifies that mobile users receive proper visual feedback during
 * media uploads, including progress bars and status indicators.
 *
 * @since 0.6093.1200
 */
class Treatment_Media_Mobile_Upload_Progress_Indicators extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-mobile-upload-progress-indicators';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Mobile Upload Progress Indicators';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if mobile users receive proper upload progress feedback';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Mobile_Upload_Progress_Indicators' );
	}
}
