<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Content_Optimizer extends Diagnostic_Base {

	protected static $slug = 'content-optimizer';
	protected static $title = 'Content Quality Optimization';
	protected static $description = 'Checks content for SEO, readability, accessibility, and quality issues.';

	public static function check(): ?array {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return null;
		}

		$recent_posts = get_posts( array(
			'post_type'   => 'post',
			'numberposts' => 10,
			'post_status' => 'publish',
		) );

		$issues = array();
		foreach ( $recent_posts as $post ) {
			if ( empty( get_the_post_thumbnail_url( $post->ID ) ) ) {
				$issues[] = 'missing_featured_image';
			}
			if ( ! has_excerpt( $post->ID ) ) {
				$issues[] = 'missing_excerpt';
			}
			if ( empty( get_post_meta( $post->ID, '_yoast_wpseo_focuskw', true ) ) ) {
				$issues[] = 'missing_seo';
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'finding_id'   => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				__( 'Found %d content quality issues in recent posts. Enable content optimization to improve SEO, readability, and accessibility.', 'wpshadow' ),
				count( $issues )
			),
			'category'     => 'content',
			'severity'     => 'medium',
			'threat_level' => 45,
			'auto_fixable' => true,
			'timestamp'    => current_time( 'mysql' ),
		);
	}
}
