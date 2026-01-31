<?php
/**
 * Journalism News Corrections Policy Diagnostic
 *
 * Verifies news sites have a published corrections policy and system
 * for tracking and displaying content corrections transparently.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Journalism
 * @since      1.6031.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Journalism;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * News Corrections Policy Diagnostic Class
 *
 * Checks if journalism sites have proper corrections policies in place.
 *
 * @since 1.6031.1445
 */
class Diagnostic_News_Corrections_Policy extends Diagnostic_Base {

/**
 * The diagnostic slug
 *
 * @var string
 */
protected static $slug = 'news-corrections-policy';

/**
 * The diagnostic title
 *
 * @var string
 */
protected static $title = 'Journalism News Corrections Policy';

/**
 * The diagnostic description
 *
 * @var string
 */
protected static $description = 'Verifies news sites have a corrections policy';

/**
 * The family this diagnostic belongs to
 *
 * @var string
 */
protected static $family = 'journalism';

/**
 * Run the diagnostic check.
 *
 * @since  1.6031.1445
 * @return array|null Finding array if issue found, null otherwise.
 */
public static function check() {
Check if site is journalism-focused.
ame    = get_bloginfo( 'name' );
e = get_bloginfo( 'description' );
alism_terms = array( 'news', 'journalism', 'reporter', 'press', 'media' );

alism_site = false;
( $journalism_terms as $term ) {
( stripos( $site_name, $term ) !== false || stripos( $site_tagline, $term ) !== false ) {
alism_site = true;
( ! $is_journalism_site ) {
 null;
= array();

Check for corrections policy page.
s_page = get_page_by_path( 'corrections' );
( ! $corrections_page ) {
= __( 'No corrections policy page found', 'wpshadow' );
Check for editorial workflow plugins.
s = get_option( 'active_plugins', array() );
s = array( 'editorial', 'workflow', 'edit-flow' );
= false;
( $active_plugins as $plugin ) {
( $editorial_plugins as $e_plugin ) {
( stripos( $plugin, $e_plugin ) !== false ) {
= true;
2;
( ! $has_editorial ) {
= __( 'No editorial workflow plugin detected', 'wpshadow' );
Check for revision tracking.
( ! wp_revisions_enabled() ) {
= __( 'Post revisions not enabled', 'wpshadow' );
( empty( $issues ) ) {
 null;
 array(
          => self::$slug,
       => self::$title,
'  => sprintf(
translators: %s: comma-separated list of issues */
'Corrections policy concerns: %s. News sites should maintain transparency about content corrections.', 'wpshadow' ),
', ', $issues )
'     => 'medium',
=> 60,
=> false,
k'      => 'https://wpshadow.com/kb/news-corrections-policy',
}
