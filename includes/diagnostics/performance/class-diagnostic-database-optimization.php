<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Database Optimization Needed?
 * 
 * Target Persona: Web Hosting Provider
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Database_Optimization extends Diagnostic_Base {
    protected static $slug = 'database-optimization';
    protected static $title = 'Database Optimization Needed?';
    protected static $description = 'Identifies tables needing optimization.';


    public static function check(): ?array {
        global $wpdb;
        $result = $wpdb->get_results("SHOW TABLE STATUS LIKE '{$wpdb->prefix}%'");
        $overhead = 0;
        foreach ($result as $table) {
            if (isset($table->Data_free)) {
                $overhead += $table->Data_free;
            }
        }
        if ($overhead > 10485760) {
            return array(
                'id'            => static::$slug,
                'title'         => static::$title,
                'description'   => 'Database has ' . size_format($overhead) . ' overhead - consider optimization.',
                'color'         => '#ff9800',
                'bg_color'      => '#fff3e0',
                'kb_link'       => 'https://wpshadow.com/kb/database-optimization/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=database-optimization',
                'training_link' => 'https://wpshadow.com/training/database-optimization/',
                'auto_fixable'  => false,
                'threat_level'  => 60,
                'module'        => 'Performance',
                'priority'      => 2,
            );
        }
        return null;
    }

}