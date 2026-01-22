<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Redis Object Cache Active?
 * 
 * Target Persona: Enterprise WordPress Platform (Automattic/WPEngine)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_Redis_Integration extends Diagnostic_Base {
    protected static $slug = 'redis-integration';
    protected static $title = 'Redis Object Cache Active?';
    protected static $description = 'Verifies Redis cache connection.';


    public static function check(): ?array {
        if (!is_plugin_active('redis-cache/redis-cache.php')) {
            return null;
        }
        if (class_exists('RedisObjectCache') && method_exists('RedisObjectCache', 'get_redis_status')) {
            $status = RedisObjectCache::get_redis_status();
            if ($status && isset($status['connected']) && $status['connected']) {
                return null;
            }
            return array(
                'id'            => static::$slug,
                'title'         => static::$title,
                'description'   => 'Redis plugin active but not connected.',
                'color'         => '#f44336',
                'bg_color'      => '#ffebee',
                'kb_link'       => 'https://wpshadow.com/kb/redis-integration/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=redis-integration',
                'training_link' => 'https://wpshadow.com/training/redis-integration/',
                'auto_fixable'  => false,
                'threat_level'  => 60,
                'module'        => 'Performance',
                'priority'      => 1,
            );
        }
        return null;
    }
}
