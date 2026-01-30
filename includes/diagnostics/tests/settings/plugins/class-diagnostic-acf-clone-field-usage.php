<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_AcfCloneFieldUsage extends Diagnostic_Base {
	protected static $slug = 'acf-clone-field-usage';
	protected static $title = 'ACF Clone Field Usage';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'ACF' ) ) { return null; }
		$groups = acf_get_field_groups();
		$clone_count = 0;
		foreach ( $groups as $group ) {
			$fields = acf_get_fields( $group['key'] );
			foreach ( $fields as $field ) {
				if ( 'clone' === $field['type'] ) {
					$clone_count++;
				}
			}
		}
		if ( $clone_count > 20 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( '%d clone fields may impact performance', 'wpshadow' ), $clone_count ),
				'severity' => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/acf-clones',
			);
		}
		return null;
	}
}
