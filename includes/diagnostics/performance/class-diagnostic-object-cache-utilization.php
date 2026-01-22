<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Object Cache Hit Rate
 *
 * Target Persona: Enterprise WordPress Platform (Automattic/WPEngine)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_Object_Cache_Utilization extends Diagnostic_Base {
	protected static $slug        = 'object-cache-utilization';
	protected static $title       = 'Object Cache Hit Rate';
	protected static $description = 'Measures Redis/Memcached effectiveness.';


	public static function check(): ?array {
		if (!wp_using_ext_object_cache()) {
			return array(
				'id'            => static::$slug,
				'title'         => static::$title,
				'description'   => 'No external object cache configured.',
				'color'         => '#ff9800',
				'bg_color'      => '#fff3e0',
				'kb_link'       => 'https://wpshadow.com/kb/object-cache-utilization/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=object-cache-utilization',
				'training_link' => 'https://wpshadow.com/training/object-cache-utilization/',
				'auto_fixable'  => false,
				'threat_level'  => 60,
				'module'        => 'Performance',
				'priority'      => 1,
			);
		}
		return null;
	}

}
