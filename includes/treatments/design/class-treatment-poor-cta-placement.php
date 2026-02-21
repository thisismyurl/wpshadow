<?php
/**
 * CTA Placement Issues Treatment
 *
 * Tests CTA placement. CTAs only at bottom miss 65% of readers who don't
 * scroll that far. Strategic placement throughout content increases conversions.
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
 * Treatment_Poor_CTA_Placement Class
 *
 * Detects when CTAs are only placed at the bottom of content, missing
 * readers who don't scroll that far (65% of visitors).
 *
 * @since 1.5003.1200
 */
class Treatment_Poor_CTA_Placement extends Treatment_Base {

	protected static $slug = 'poor-cta-placement';
	protected static $title = 'CTA Placement Issues';
	protected static $description = 'Tests whether CTAs are strategically placed throughout content';
	protected static $family = 'conversion';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Poor_CTA_Placement' );
	}
}
