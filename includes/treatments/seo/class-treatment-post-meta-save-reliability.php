<?php
/**
 * Post Meta Save Reliability Treatment
 *
 * Verifies post meta data saves correctly by testing update_post_meta
 * and checking for data persistence issues.
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
 * Post Meta Save Reliability Class
 *
 * Tests post meta save operations and detects issues that may cause
 * meta data to fail saving or be lost during updates.
 *
 * @since 0.6093.1200
 */
class Treatment_Post_Meta_Save_Reliability extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'post-meta-save-reliability';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Post Meta Save Reliability';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies post meta data saves correctly';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the treatment check.
	 *
	 * Tests post meta save reliability and checks for common issues
	 * that prevent meta data from persisting correctly.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if save issues found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Post_Meta_Save_Reliability' );
	}
}
