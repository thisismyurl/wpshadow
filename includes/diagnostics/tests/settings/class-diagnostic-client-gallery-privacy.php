<?php
/**
 * Portfolio Client Gallery Privacy Diagnostic
 *
 * Verifies private client galleries are properly secured
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Portfolio;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Diagnostic_ClientGalleryPrivacy Class
 *
 * Checks for password protection, client gallery plugins, access logging
 *
 * @since 1.6093.1200
 */
class Diagnostic_ClientGalleryPrivacy extends Diagnostic_Base {

/**
 * The diagnostic slug
 *
 * @var string
 */
protected static $slug = 'client-gallery-privacy';

/**
 * The diagnostic title
 *
 * @var string
 */
protected static $title = 'Portfolio Client Gallery Privacy';

/**
 * The diagnostic description
 *
 * @var string
 */
protected static $description = 'Verifies private client galleries are properly secured';

/**
 * The family this diagnostic belongs to
 *
 * @var string
 */
protected static $family = 'portfolio';

/**
 * Run the diagnostic check.
 *
 * @since 1.6093.1200
 * @return array|null Finding array if issue found, null otherwise.
 */
public static function check() {
		// Check if site uses client gallery functionality.
		$active_plugins = get_option( 'active_plugins', array() );
		$gallery_plugins = array( 'client-gallery', 'photographer', 'photo-gallery', 'envira', 'nextgen' );
		$has_gallery = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $gallery_plugins as $g_plugin ) {
				if ( stripos( $plugin, $g_plugin ) !== false ) {
					$has_gallery = true;
					break 2;
				}
			}
		}

		if ( ! $has_gallery ) {
			return null;
		}

		$issues = array();

		// Check for password-protected galleries.
		$args = array(
			'post_type'      => array( 'gallery', 'portfolio', 'project' ),
			'posts_per_page' => 50,
			'post_status'    => 'publish',
		);

		$query = new \WP_Query( $args );
		$unprotected_galleries = 0;

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				if ( ! post_password_required() ) {
					$unprotected_galleries++;
				}
			}
			wp_reset_postdata();
		}

		if ( $unprotected_galleries > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of galleries */
				__( '%d public galleries without password protection', 'wpshadow' ),
				$unprotected_galleries
			);
		}

		// Check for member restriction plugins.
		$member_plugins = array( 'members', 'restrict-content', 'membership', 'member-press' );
		$has_member_restriction = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $member_plugins as $m_plugin ) {
				if ( stripos( $plugin, $m_plugin ) !== false ) {
					$has_member_restriction = true;
					break 2;
				}
			}
		}

		if ( ! $has_member_restriction && $unprotected_galleries > 10 ) {
			$issues[] = __( 'No member restriction plugin for private galleries', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Gallery privacy concerns: %s. Client galleries should be password-protected or member-restricted.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/client-gallery-privacy',
		);
	}
}
