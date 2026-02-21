<?php
/**
 * Theme CSS/JS Loading Errors Treatment
 *
 * Detects 404 errors or loading issues with theme assets.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.5049.1245
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme CSS/JS Loading Errors Treatment Class
 *
 * Checks for broken or missing theme assets.
 *
 * @since 1.5049.1245
 */
class Treatment_Theme_CSS_JS_Loading_Errors extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-css-js-loading-errors';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Theme CSS/JS Loading Errors';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for broken theme assets';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.5049.1245
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Theme_CSS_JS_Loading_Errors' );
	}
}
