<?php
/**
 * Comment Engagement and Community Health
 *
 * Validates comment section engagement metrics and community activity.
 *
 * @since 0.6093.1200
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Comment_Engagement Class
 *
 * Checks comment section engagement and community health indicators.
 *
 * @since 0.6093.1200
 */
class Treatment_Comment_Engagement extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-engagement';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Engagement and Community Health';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes comment engagement metrics and community activity patterns';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Engagement' );
	}
}
