<?php
/**
 * Treatment: Create and assign a Privacy Policy page
 *
 * GDPR, CCPA, and most global privacy regulations require a published privacy
 * policy page. WordPress has a native option (wp_page_for_privacy_policy) to
 * designate that page; when it is unset or points to an unpublished page this
 * diagnostic fires.
 *
 * This treatment takes the following steps in order:
 *   1. If a published page already contains "privacy" or "privacy policy" in
 *      its title → assign it as the designated privacy policy page.
 *   2. Otherwise, create a placeholder privacy policy page and assign it.
 *
 * The page ID created by WPShadow (if any) is stored for undo().
 *
 * Undo: if WPShadow created the page, it is deleted. Either way, the
 * wp_page_for_privacy_policy option is cleared.
 *
 * @package WPShadow
 * @since   0.6093.1900
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates and/or assigns a Privacy Policy page in WordPress.
 */
class Treatment_Privacy_Policy_Page_Set extends Treatment_Base {

	/** @var string */
	protected static $slug = 'privacy-policy-page-set';

	/** @return string */
	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Locate an existing privacy page or create a placeholder, then assign it.
	 *
	 * @return array
	 */
	public static function apply(): array {
		// 1. Check for an existing published page with "privacy" in the title.
		$existing = get_pages(
			array(
				'post_status' => 'publish',
				'number'      => 10,
			)
		);

		$matched_page = null;

		foreach ( (array) $existing as $page ) {
			if ( stripos( $page->post_title, 'privacy' ) !== false ) {
				$matched_page = $page;
				break;
			}
		}

		if ( $matched_page ) {
			$prev_id = (int) get_option( 'wp_page_for_privacy_policy', 0 );
			update_option( 'wpshadow_privacy_page_prev_id', $prev_id, false );
			update_option( 'wpshadow_privacy_page_created', false, false );
			update_option( 'wp_page_for_privacy_policy', $matched_page->ID );

			return array(
				'success' => true,
				'message' => sprintf(
					/* translators: %s: Page title */
					__( 'Assigned the existing "%s" page as the designated Privacy Policy page.', 'wpshadow' ),
					esc_html( $matched_page->post_title )
				),
			);
		}

		// 2. No match — create a placeholder privacy policy page.
		$content = implode(
			"\n\n",
			array(
				'<!-- wp:paragraph -->',
				'<p>' . __( 'This Privacy Policy page was created automatically by WPShadow as a placeholder. Please replace this content with your actual privacy policy before publishing.', 'wpshadow' ) . '</p>',
				'<!-- /wp:paragraph -->',
				'<!-- wp:paragraph -->',
				'<p>' . __( 'Your privacy policy should describe what personal data you collect, why you collect it, how it is stored, and how users can request its deletion.', 'wpshadow' ) . '</p>',
				'<!-- /wp:paragraph -->',
			)
		);

		$page_id = wp_insert_post(
			array(
				'post_title'   => __( 'Privacy Policy', 'wpshadow' ),
				'post_content' => $content,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_author'  => get_current_user_id() ?: 1,
				'comment_status' => 'closed',
			),
			true
		);

		if ( is_wp_error( $page_id ) ) {
			return array(
				'success' => false,
				'message' => sprintf(
					/* translators: %s: WP_Error message */
					__( 'Could not create a Privacy Policy page: %s', 'wpshadow' ),
					$page_id->get_error_message()
				),
			);
		}

		$prev_id = (int) get_option( 'wp_page_for_privacy_policy', 0 );
		update_option( 'wpshadow_privacy_page_prev_id', $prev_id, false );
		update_option( 'wpshadow_privacy_page_created', $page_id, false ); // Store ID so undo() can delete it.
		update_option( 'wp_page_for_privacy_policy', $page_id );

		return array(
			'success' => true,
			'message' => __( 'A placeholder Privacy Policy page has been created and assigned. Edit the page content with your actual privacy policy before sharing.', 'wpshadow' ),
			'details' => array( 'page_id' => $page_id ),
		);
	}

	/**
	 * Restore previous state: delete created page (if any) and clear the option.
	 *
	 * @return array
	 */
	public static function undo(): array {
		$created_id = get_option( 'wpshadow_privacy_page_created' );
		$prev_id    = (int) get_option( 'wpshadow_privacy_page_prev_id', 0 );
		$messages   = array();

		// Delete the page WPShadow created, if applicable.
		if ( $created_id && (int) $created_id > 0 ) {
			$deleted = wp_delete_post( (int) $created_id, true );
			if ( $deleted ) {
				$messages[] = __( 'Placeholder Privacy Policy page deleted.', 'wpshadow' );
			}
		}

		// Restore previous option value.
		if ( $prev_id > 0 ) {
			update_option( 'wp_page_for_privacy_policy', $prev_id );
			$messages[] = __( 'Privacy Policy page assignment restored to previous value.', 'wpshadow' );
		} else {
			delete_option( 'wp_page_for_privacy_policy' );
			$messages[] = __( 'Privacy Policy page designation cleared.', 'wpshadow' );
		}

		delete_option( 'wpshadow_privacy_page_created' );
		delete_option( 'wpshadow_privacy_page_prev_id' );

		return array(
			'success' => true,
			'message' => implode( ' ', $messages ),
		);
	}
}
