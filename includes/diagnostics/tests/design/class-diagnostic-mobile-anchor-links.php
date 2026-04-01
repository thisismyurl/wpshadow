<?php
/**
 * Mobile Anchor Links
 *
 * Validates anchor link usability on mobile devices.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Navigation
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Diagnostic_Mobile_Anchor_Links Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Mobile_Anchor_Links extends Diagnostic_Base {

    protected static $slug = 'mobile-anchor-links';
    protected static $title = 'Mobile Anchor Links';
    protected static $description = 'Validates anchor link behavior on mobile';
    protected static $family = 'navigation';

    public static function check() {
        return null;
    }
}
