<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Search_Indexing extends Diagnostic_Base {

	protected static $slug = 'search-indexing';
	protected static $title = 'Search Engine Indexing';
	protected static $description = 'Checks if search engines are blocked from indexing the site.';

	public static function check(): ?array {
		$blog_public = get_option( 'blog_public' );

		if ( '1' === $blog_public || 1 === $blog_public ) {
			return null;
		}

		return array(
			'finding_id'   => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Search engines are blocked from indexing this site! The "Discourage search engines" setting is enabled. This is often accidentally left on after development and prevents the site from appearing in Google. Your site is invisible to search engines.', 'wpshadow' ),
			'category'     => 'seo',
			'severity'     => 'critical',
			'threat_level' => 98,
			'auto_fixable' => true,
			'timestamp'    => current_time( 'mysql' ),
		);
	}
}
