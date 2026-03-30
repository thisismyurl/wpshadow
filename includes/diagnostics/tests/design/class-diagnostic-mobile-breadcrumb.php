<?php
/**
 * Mobile Breadcrumb Navigation
 *
 * Validates breadcrumb usability on mobile devices.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Navigation
 * @since      1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Diagnostic_Mobile_Breadcrumb Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Mobile_Breadcrumb extends Diagnostic_Base {

    protected static $slug = 'mobile-breadcrumb-navigation';
    protected static $title = 'Mobile Breadcrumb Navigation';
    protected static $description = 'Validates breadcrumb links for mobile touch';
    protected static $family = 'navigation';

    public static function check() {
        return null;
    }
}
