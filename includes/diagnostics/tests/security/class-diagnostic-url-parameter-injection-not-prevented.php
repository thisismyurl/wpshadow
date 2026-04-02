<?php
/**
 * URL Parameter Injection Not Prevented Diagnostic
 *
 * Checks URL injection.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_URL_Parameter_Injection_Not_Prevented Class
 *
 * Performs diagnostic check for Url Parameter Injection Not Prevented.
 *
 * @since 1.6093.1200
 */
class Diagnostic_URL_Parameter_Injection_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'url-parameter-injection-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'URL Parameter Injection Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks URL injection';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! has_filter( 'init', 'validate_url_parameters' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'URL parameter injection protections are not configured yet. Validating and sanitizing query parameters helps prevent malicious input.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/url-parameter-injection-not-prevented',
			);
		}

		return null;
	}
}
