<?php
/**
 * JSON Injection Not Prevented Diagnostic
 *
 * Checks JSON injection prevention.
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
 * Diagnostic_JSON_Injection_Not_Prevented Class
 *
 * Performs diagnostic check for Json Injection Not Prevented.
 *
 * @since 1.26033.2033
 */
class Diagnostic_JSON_Injection_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'json-injection-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'JSON Injection Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks JSON injection prevention';

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
						'sanitize_json_output' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('JSON injection not prevented. Use wp_json_encode() and ensure JSON output never includes unescaped user input.',
						'severity'   =>   'high',
						'threat_level'   =>   60,
						'auto_fixable'   =>   true,
						'kb_link'   =>   'https://wpshadow.com/kb/json-injection-not-prevented'
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
