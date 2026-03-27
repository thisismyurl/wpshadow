<?php
/**
 * Comment Attachment Issues Treatment
 *
 * Detects problems with comment attachments if enabled by plugins.
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
 * Comment Attachment Treatment Class
 *
 * @since 1.6093.1200
 */
class Treatment_Comment_Attachment_Issues extends Treatment_Base {

	protected static $slug = 'comment-attachment-issues';
	protected static $title = 'Comment Attachment Issues';
	protected static $description = 'Detects problems with comment attachments if enabled';
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Attachment_Issues' );
	}
}
