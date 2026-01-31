<?php
/**
 * Theme Archive Pages Diagnostic
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

class Diagnostic_Theme_Archive_Pages extends Diagnostic_Base {
	protected static $slug = 'theme-archive-pages';
	protected static $title = 'Theme Archive Pages';
	protected static $description = 'Verifies theme archive pages display correctly';
	protected static $family = 'functionality';

	public static function check() {
		$archive_templates = array( 'archive.php', 'category.php', 'tag.php', 'author.php', 'date.php' );
		$found_templates = 0;

		foreach ( $archive_templates as $template ) {
			if ( locate_template( $template ) ) {
				++$found_templates;
			}
		}

		if ( 0 === $found_templates ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme has no custom archive templates - archive pages may use generic layout', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-archive-pages',
			);
		}
		return null;
	}
}
