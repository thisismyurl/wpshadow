<?php
/**
 * Theme Search Functionality Diagnostic
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26031.1600
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Theme_Search_Functionality extends Diagnostic_Base {
	protected static $slug = 'theme-search-functionality';
	protected static $title = 'Theme Search Functionality';
	protected static $description = 'Detects issues with theme search template functionality';
	protected static $family = 'functionality';

	public static function check() {
		$search_template = locate_template( 'search.php' );
		$searchform = locate_template( 'searchform.php' );
		
		if ( empty( $search_template ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme does not have custom search.php template - search results may use generic layout', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-search-functionality',
			);
		}
		return null;
	}
}
