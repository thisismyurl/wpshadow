<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_GravityFormsEntryManagement extends Diagnostic_Base {
	protected static $slug = 'gravity-forms-entry-management';
	protected static $title = 'Gravity Forms Entry Management';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'GFForms' ) ) { return null; }
		global $wpdb;
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}gf_entry WHERE status = 'active'" );
		if ( $count > 10000 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( '%d entries (high volume)', 'wpshadow' ), $count ),
				'severity' => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/gravity-forms-entries',
			);
		}
		return null;
	}
}
