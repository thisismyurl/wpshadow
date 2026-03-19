<?php
/**
 * Comment Export Security Treatment
 *
 * Detects security and privacy issues with comment data export functionality.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Export Security Treatment Class
 *
 * Checks for insecure comment export functionality that could expose
 * sensitive data or allow unauthorized access.
 *
 * @since 1.6093.1200
 */
class Treatment_Comment_Export_Issues extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-export-issues';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Export Security';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for secure comment data export handling';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Export_Issues' );
	}
}
