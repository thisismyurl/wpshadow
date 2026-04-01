<?php
/**
 * Generic CTAs Treatment
 *
 * Tests whether CTAs are generic ('Click Here') or specific. Specific CTAs
 * that tell users exactly what they'll get convert 3x better.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Generic_CTAs Class
 *
 * Detects generic calls-to-action like "Click Here" or "Read More" which
 * convert poorly compared to specific, benefit-focused CTAs.
 *
 * @since 0.6093.1200
 */
class Treatment_Generic_CTAs extends Treatment_Base {

	protected static $slug = 'generic-ctas';
	protected static $title = 'Generic CTAs';
	protected static $description = 'Tests whether CTAs are specific or generic';
	protected static $family = 'conversion';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Generic_CTAs' );
	}
}
