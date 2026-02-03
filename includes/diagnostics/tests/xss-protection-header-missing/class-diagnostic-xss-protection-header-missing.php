<?php
/**
 * XSS Protection Header Missing Diagnostic
 *
 * Checks XSS protection.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_XSS_Protection_Header_Missing Class
 *
 * Performs diagnostic check for Xss Protection Header Missing.
 *
 * @since 1.26033.2033
 */
class Diagnostic_XSS_Protection_Header_Missing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'xss-protection-header-missing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'XSS Protection Header Missing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks XSS protection';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'add_xss_protection_header' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('XSS Protection header missing. Add X-XSS-Protection header (legacy support) and Content-Security-Policy for modern browsers.',
						'severity'   =>   'high',
						'threat_level'   =>   65,
						'auto_fixable'   =>   true,
						'kb_link'   =>   'https://wpshadow.com/kb/xss-protection-header-missing'
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
