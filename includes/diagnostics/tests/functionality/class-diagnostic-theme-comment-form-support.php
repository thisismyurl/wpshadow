<?php
/**
 * Theme Comment Form Support Diagnostic
 *
 * Detects issues with theme's comment form implementation and customization options.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Comment Form Support Diagnostic Class
 *
 * Checks for proper theme comment form implementation, including template
 * customization, accessibility features, and styling support.
 *
 * @since 1.5049.1200
 */
class Diagnostic_Theme_Comment_Form_Support extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-comment-form-support';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Comment Form Support';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if theme properly supports comment forms';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme = wp_get_theme();
		$issues = array();

		// Check for comments.php template.
		$comments_template = locate_template( 'comments.php' );
		if ( empty( $comments_template ) ) {
			$issues[] = __( 'Theme missing comments.php template', 'wpshadow' );
		}

		// Check if theme supports comment forms (on a sample post).
		$sample_post = get_posts( array(
			'post_type'   => 'post',
			'numberposts' => 1,
			'post_status' => 'publish',
		) );

		if ( ! empty( $sample_post ) ) {
			$post_id = $sample_post[0]->ID;
			
			// Check if comments are open on this post.
			if ( comments_open( $post_id ) ) {
				// Try to get comment form HTML.
				ob_start();
				comment_form( array(), $post_id );
				$form_html = ob_get_clean();

				// Check for basic form elements.
				if ( empty( $form_html ) || ! preg_match( '/<form[^>]*id=["\']commentform["\']/i', $form_html ) ) {
					$issues[] = __( 'Theme may not properly render comment forms', 'wpshadow' );
				}

				// Check for accessibility attributes.
				if ( ! preg_match( '/aria-required|required/i', $form_html ) ) {
					$issues[] = __( 'Comment form may lack accessibility attributes', 'wpshadow' );
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: comma-separated list of issues */
					__( 'Theme comment form issues detected: %s', 'wpshadow' ),
					implode( ', ', $issues )
				),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'details'     => array(
					'theme'  => $theme->get( 'Name' ),
					'issues' => $issues,
				),
				'kb_link'     => 'https://wpshadow.com/kb/theme-comment-form-support',
			);
		}

		return null;
	}
}
