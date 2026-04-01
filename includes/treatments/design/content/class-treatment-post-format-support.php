<?php
/**
 * Post Format Support Treatment
 *
 * Checks if post formats (aside, gallery, video, etc.) are properly
 * supported by the theme.
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
 * Post Format Support Treatment Class
 *
 * Verifies that post formats are properly supported and configured
 * in the active theme.
 *
 * @since 0.6093.1200
 */
class Treatment_Post_Format_Support extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-format-support';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Post Format Support';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if post formats are properly supported by the theme';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'posts';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Post_Format_Support' );
	}
}
