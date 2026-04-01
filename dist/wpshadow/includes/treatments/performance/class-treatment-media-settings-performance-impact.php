<?php
/**
 * Media Settings Performance Impact Treatment
 *
 * Analyzes media library settings and identifies configurations that may
 * negatively impact site performance and storage.
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
 * Media Settings Performance Impact Treatment Class
 *
 * Evaluates media settings for performance optimization opportunities.
 *
 * @since 0.6093.1200
 */
class Treatment_Media_Settings_Performance_Impact extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'media-settings-performance-impact';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Media Settings Performance Impact';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Evaluates media settings for performance';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Media_Settings_Performance_Impact' );
	}
}
