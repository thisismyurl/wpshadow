<?php
/**
 * MIME Type Sniffing Not Prevented Diagnostic
 *
 * Checks MIME sniffing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_MIME_Type_Sniffing_Not_Prevented Class
 *
 * Performs diagnostic check for Mime Type Sniffing Not Prevented.
 *
 * @since 1.6033.2033
 */
class Diagnostic_MIME_Type_Sniffing_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mime-type-sniffing-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'MIME Type Sniffing Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks MIME sniffing';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'prevent_mime_sniffing' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('MIME type sniffing not prevented. Set X-Content-Type-Options: nosniff header to prevent browser MIME sniffing.',
						'severity'   =>   'medium',
						'threat_level'   =>   50,
						'auto_fixable'   =>   true,
						'kb_link'   =>   'https://wpshadow.com/kb/mime-type-sniffing-not-prevented'
						);
						);,
						);
						}
						return null;
						}
						return null;
						}
						return null;
	}
}
