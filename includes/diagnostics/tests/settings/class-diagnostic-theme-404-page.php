<?php
/**
 * Theme 404 Page Diagnostic
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Theme_404_Page extends Diagnostic_Base {
	protected static $slug = 'theme-404-page';
	protected static $title = 'Theme 404 Page';
	protected static $description = 'Checks if theme has custom 404 page template';
	protected static $family = 'functionality';

	public static function check() {
		$template = locate_template( '404.php' );

		if ( empty( $template ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme does not have a custom 404.php template - users may see default error page', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-404-page',
			);
		}
		return null;
	}
}
