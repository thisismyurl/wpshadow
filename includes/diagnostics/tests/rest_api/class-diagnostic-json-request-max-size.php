<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Json_Request_Max_Size extends Diagnostic_Base {
	protected static $slug = 'json-request-max-size';
	protected static $title = 'JSON Request Size Limit';
	protected static $description = 'Tests large JSON payload acceptance';
	protected static $family = 'rest_api';
	public static function check() {
		if ( ! function_exists( 'json_decode' ) ) { return null; }
		$post_max = (int) ini_get( 'post_max_size' );
		$upload_max = (int) ini_get( 'upload_max_filesize' );
		$effective_max = min( $post_max, $upload_max );
		if ( $effective_max < 10 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( 'JSON request size limit is %dMB. This may be too small for large REST API requests. Consider increasing to at least 10MB.', 'wpshadow' ), $effective_max ),
				'severity' => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/json-request-max-size',
				'meta' => array( 'max_size_mb' => $effective_max ),
			);
		}
		return null;
	}
}
