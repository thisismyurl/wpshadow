<?php
/**
 * Treatment: Too Few Images
 *
 * Detects posts with <1 image per 1,000 words. Posts with 3-7 images get
 * 94% more views and higher engagement.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.7030.1503
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Insufficient Images Treatment Class
 *
 * Checks for adequate image usage in content.
 *
 * Detection methods:
 * - Image count vs word count ratio
 * - Featured image presence
 * - Image distribution
 *
 * @since 1.7030.1503
 */
class Treatment_Insufficient_Images extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'insufficient-images';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Too Few Images';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = '<1 image per 1,000 words - Posts with 3-7 images get 94% more views';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'structure';

	/**
	 * Run the treatment check.
	 *
	 * Scoring system (3 points):
	 * - 3 points: ≥1 image per 500 words average
	 * - 2 points: ≥1 image per 1,000 words
	 * - 0 points: <1 image per 1,000 words
	 *
	 * @since  1.7030.1503
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Insufficient_Images' );
	}
}
