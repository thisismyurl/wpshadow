<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Embed_Disable extends Diagnostic_Base {

	protected static $slug = 'embed-disable';
	protected static $title = 'WordPress Embed Scripts';
	protected static $description = 'Checks if WordPress embed scripts are loaded but not being used.';

	public static function check(): ?array {
		if ( get_option( 'wpshadow_embed_disable_enabled', false ) ) {
			return null;
		}

		if ( ! wp_script_is( 'wp-embed', 'enqueued' ) && ! wp_script_is( 'wp-embed', 'registered' ) ) {
			return null;
		}

		return array(
			'finding_id'   => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'WordPress embed scripts (wp-embed.js) are loaded but may not be needed. Disabling saves bandwidth and improves performance if you don\'t use embed features.', 'wpshadow' ),
			'category'     => 'performance',
			'severity'     => 'low',
			'threat_level' => 15,
			'auto_fixable' => true,
			'timestamp'    => current_time( 'mysql' ),
		);
	}
}
