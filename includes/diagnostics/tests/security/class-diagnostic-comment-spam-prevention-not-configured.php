<?php
/**
 * Comment Spam Prevention Not Configured Diagnostic
 *
 * Checks if spam prevention is configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Spam Prevention Not Configured Diagnostic Class
 *
 * Detects missing spam prevention.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Comment_Spam_Prevention_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-spam-prevention-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Spam Prevention Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if spam prevention is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for spam prevention plugin
		if ( ! is_plugin_active( 'akismet/akismet.php' ) && ! is_plugin_active( 'antispam-bee/antispam-bee.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Comment spam prevention is not configured. Enable Akismet or another spam filter to automatically block spam comments.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/comment-spam-prevention-not-configured?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
