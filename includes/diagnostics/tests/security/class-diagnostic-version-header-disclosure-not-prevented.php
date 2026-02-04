<?php
/**
 * Version Header Disclosure Not Prevented Diagnostic
 *
 * Checks version disclosure.
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
 * Diagnostic_Version_Header_Disclosure_Not_Prevented Class
 *
 * Performs diagnostic check for Version Header Disclosure Not Prevented.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Version_Header_Disclosure_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'version-header-disclosure-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Version Header Disclosure Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks version disclosure';

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
						'remove_version_headers' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Version header disclosure not prevented. Remove X-Powered-By and Server version headers to reduce information exposure.',
						'severity'   =>   'medium',
						'threat_level'   =>   25,
						'auto_fixable'   =>   true,
						'kb_link'   =>   'https://wpshadow.com/kb/version-header-disclosure-not-prevented'
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
