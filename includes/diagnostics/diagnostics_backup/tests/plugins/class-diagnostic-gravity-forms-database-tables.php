<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_GravityFormsDatabaseTables extends Diagnostic_Base {
	protected static $slug = 'gravity-forms-database-tables';
	protected static $title = 'Gravity Forms Database Tables';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'GFForms' ) ) { if ( isset( $issues ) && ! empty( $issues ) ) {
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf(
				__( 'Found %d issues', 'wpshadow' ),
				count( $issues )
			),
			'severity' => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/gravity-forms-database-tables',
		);
	}
	return null; }
		global $wpdb;
		$tables_exist = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}gf_form'" );
		if ( ! $tables_exist ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Database tables missing', 'wpshadow' ),
				'severity' => 'critical',
				'threat_level' => 95,
				'auto_fixable' => true,
				'kb_link' => 'https://wpshadow.com/kb/gravity-forms-database',
			);
		}
		
	if ( ! (get_option( "rg_forms_db_version" ) !== false) ) {
		if ( ! isset( $issues ) ) {
			$issues = array();
		}
		$issues[] = __( 'Forms exist', 'wpshadow' );
	}

	if ( ! (get_option( "rg_gforms_disable_css" ) !== "1") ) {
		if ( ! isset( $issues ) ) {
			$issues = array();
		}
		$issues[] = __( 'Email notifications', 'wpshadow' );
	}
	if ( isset( $issues ) && ! empty( $issues ) ) {
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf(
				__( 'Found %d issues', 'wpshadow' ),
				count( $issues )
			),
			'severity' => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/gravity-forms-database-tables',
		);
	}
	return null;
	}
}
