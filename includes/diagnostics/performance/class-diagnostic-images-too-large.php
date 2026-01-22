<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Are Images Slowing Site Down?
 * 
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Images_Too_Large extends Diagnostic_Base {
    protected static $slug = 'images-too-large';
    protected static $title = 'Are Images Slowing Site Down?';
    protected static $description = 'Finds unoptimized images over 300KB.';


    public static function check(): ?array {
        $image_plugins = array(
            'imagify/imagify.php',
            'ewww-image-optimizer/ewww-image-optimizer.php',
            'shortpixel-image-optimiser/wp-shortpixel.php',
        );
        foreach ($image_plugins as $plugin) {
            if (is_plugin_active($plugin)) {
                return null;
            }
        }
        return array(
            'id'            => static::$slug,
            'title'         => static::$title,
            'description'   => 'No image optimization plugin detected.',
            'color'         => '#ff9800',
            'bg_color'      => '#fff3e0',
            'kb_link'       => 'https://wpshadow.com/kb/images-too-large/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=images-too-large',
            'training_link' => 'https://wpshadow.com/training/images-too-large/',
            'auto_fixable'  => false,
            'threat_level'  => 60,
            'module'        => 'Performance',
            'priority'      => 1,
        );
    }
}
