<?php
/**
 * No CTAs in Content Treatment
 *
 * Tests whether posts contain any calls-to-action. Posts without CTAs convert
 * at 0% compared to industry average of 2-5%.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.5003.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_No_CTAs Class
 *
 * Detects posts with no calls-to-action at all. Every post should guide
 * readers toward a next action - subscribe, download, product, etc.
 *
 * @since 1.5003.1200
 */
class Treatment_No_CTAs extends Treatment_Base {

	protected static $slug = 'no-ctas';
	protected static $title = 'No CTAs in Content';
	protected static $description = 'Tests whether posts contain calls-to-action';
	protected static $family = 'conversion';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_No_CTAs' );
	}
}
