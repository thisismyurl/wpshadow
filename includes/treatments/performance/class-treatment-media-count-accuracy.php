<?php
/**
 * Media Count Accuracy Treatment
 *
 * Verifies media counts shown in the UI match database counts.
 *
 * @package    WPShadow
 * @subpackage Treatments\Tests
 * @since      1.6033.1605
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Media_Count_Accuracy Class
 *
 * Checks attachment counts returned by WordPress APIs
 * against direct database totals.
 *
 * @since 1.6033.1605
 */
class Treatment_Media_Count_Accuracy extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-count-accuracy';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Count Accuracy';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies media counts match database totals';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.1605
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Count_Accuracy' );
	}
}
