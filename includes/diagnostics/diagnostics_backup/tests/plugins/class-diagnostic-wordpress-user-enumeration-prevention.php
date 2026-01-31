<?php
/**
 * Wordpress User Enumeration Prevention Diagnostic
 *
 * Wordpress User Enumeration Prevention issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1268.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress User Enumeration Prevention Diagnostic Class
 *
 * @since 1.1268.0000
 */
class Diagnostic_WordpressUserEnumerationPrevention extends Diagnostic_Base {

	protected static $slug = 'wordpress-user-enumeration-prevention';
	protected static $title = 'Wordpress User Enumeration Prevention';
	protected static $description = 'Wordpress User Enumeration Prevention issue detected';
	protected static $family = 'functionality';

	public static function check() {
		// WordPress core feature - always check
		$issues = array();
		
		// Check 1: Test author archive enumeration (/?author=1)
		$test_urls = array(
			site_url( '/?author=1' ),
			site_url( '/?author=2' ),
		);
		
		foreach ( $test_urls as $url ) {
			$response = wp_remote_get( $url, array( 'timeout' => 5, 'redirection' => 0 ) );
			
			if ( ! is_wp_error( $response ) ) {
				$status_code = wp_remote_retrieve_response_code( $response );
				// If redirects to author page (301/302) or displays (200), enumeration possible
				if ( in_array( $status_code, array( 200, 301, 302 ), true ) ) {
					$location = wp_remote_retrieve_header( $response, 'location' );
					if ( ! empty( $location ) && strpos( $location, '/author/' ) !== false ) {
						$issues[] = 'author_enumeration_via_id';
						break;
					}
				}
			}
		}
		
		// Check 2: Verify REST API user endpoint is protected
		$rest_url = rest_url( 'wp/v2/users' );
		$response = wp_remote_get( $rest_url, array( 'timeout' => 5 ) );
		
		if ( ! is_wp_error( $response ) ) {
			$status_code = wp_remote_retrieve_response_code( $response );
			if ( 200 === $status_code ) {
				$body = wp_remote_retrieve_body( $response );
				$data = json_decode( $body, true );
				
				// If returns user data without authentication
				if ( is_array( $data ) && ! empty( $data ) ) {
					$issues[] = 'rest_api_user_enumeration';
				}
			}
		}
		
		// Check 3: Check if login error messages reveal user existence
		$test_login = wp_authenticate( 'nonexistent_user_' . wp_rand(), 'wrong_password' );
		if ( is_wp_error( $test_login ) ) {
			$error_message = $test_login->get_error_message();
			// Generic error is good, specific "unknown username" is bad
			if ( stripos( $error_message, 'username' ) !== false || stripos( $error_message, 'email' ) !== false ) {
				$issues[] = 'login_error_reveals_users';
			}
		}
		
		// Check 4: Check if author archives are enabled
		$users_with_posts = get_users( array( 'has_published_posts' => true, 'number' => 1 ) );
		if ( ! empty( $users_with_posts ) ) {
			$user = $users_with_posts[0];
			$author_url = get_author_posts_url( $user->ID );
			$response = wp_remote_get( $author_url, array( 'timeout' => 5 ) );
			
			if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
				$body = wp_remote_retrieve_body( $response );
				// Check if username is exposed in HTML
				if ( stripos( $body, $user->user_login ) !== false ) {
					$issues[] = 'author_page_exposes_username';
				}
			}
		}
		
		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of user enumeration issues */
				__( 'User enumeration vulnerabilities detected: %s. Attackers can discover valid usernames for targeted brute force attacks. Consider blocking author archives, sanitizing REST API endpoints, and using generic login error messages.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => 60,
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-user-enumeration-prevention',
			);
		}
		
		return null;
	}
}
