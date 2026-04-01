<?php
/**
 * Mobile Third-Party Script Impact
 *
 * Quantifies performance impact of third-party scripts.
 *
 * @package    WPShadow
 * @subpackage Treatments\Performance
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Third-Party Script Impact
 *
 * Identifies external scripts (analytics, ads, widgets) and estimates
 * their performance impact on mobile loading.
 *
 * @since 0.6093.1200
 */
class Treatment_Mobile_Third_Party_Script_Impact extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-third-party-impact';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Third-Party Script Impact';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Quantifies performance impact of third-party scripts';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Third_Party_Script_Impact' );
	}
}
