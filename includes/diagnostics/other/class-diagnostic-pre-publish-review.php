<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Pre_Publish_Review extends Diagnostic_Base {

	protected static $slug = 'pre-publish-review';
	protected static $title = 'Pre-Publish Content Review';
	protected static $description = 'Checks posts before publishing for broken links, missing images, and quality issues.';

	public static function check(): ?array {
		if ( get_option( 'wpshadow_pre_publish_review_enabled', false ) ) {
			return null;
		}

		if ( ! current_user_can( 'publish_posts' ) ) {
			return null;
		}

		return array(
			'finding_id'   => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Enable pre-publish review to automatically check posts for broken links, missing alt text, readability issues, and SEO problems before publishing.', 'wpshadow' ),
			'category'     => 'content',
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => true,
			'timestamp'    => current_time( 'mysql' ),
		);
	}
}
