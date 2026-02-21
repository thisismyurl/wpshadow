<?php
/**
 * Breadcrumb Navigation Treatment
 *
 * Issue #4963: No Breadcrumb Navigation
 * Pillar: 🎓 Learning Inclusive / #1: Helpful Neighbor
 *
 * Checks if site provides breadcrumb navigation.
 * Breadcrumbs help users understand location and hierarchy.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Breadcrumb_Navigation Class
 *
 * @since 1.6050.0000
 */
class Treatment_Breadcrumb_Navigation extends Treatment_Base {

	protected static $slug = 'breadcrumb-navigation';
	protected static $title = 'No Breadcrumb Navigation';
	protected static $description = 'Checks if site provides breadcrumb trails for navigation';
	protected static $family = 'content';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Breadcrumb_Navigation' );
	}
}
