<?php
/**
 * Elementor Revision Bloat Diagnostic
 *
 * Detects excessive Elementor page revisions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1730
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

class Diagnostic_Elementor_Revision_Bloat extends Diagnostic_Base {

protected static $slug        = 'elementor-revision-bloat';
protected static $title       = 'Elementor Revision Database Bloat';
protected static $description = 'Detects excessive Elementor revisions';
protected static $family      = 'database';

public static function check() {
'wpshadow_elementor_revision_bloat';
 = get_transient( $cache_key );

!== $cached ) {
 $cached;
did_action( 'elementor/loaded' ) ) {
sient( $cache_key, null, 24 * HOUR_IN_SECONDS );
 null;
= $wpdb->get_results(
COUNT(r.ID) as revision_count
JOIN {$wpdb->posts} r ON r.post_parent = p.ID AND r.post_type = 'revision'
IN ('page', 'post')
D EXISTS (
{$wpdb->postmeta}
p.ID AND meta_key = '_elementor_edit_mode'
p.ID
G revision_count > 50
! empty( $pages_with_excess ) ) {
t( $pages_with_excess );
(
        => self::$slug,
     => self::$title,
'  => sprintf(
slators: %d: count */
tor pages have >50 revisions. Clean up for better performance.', 'wpshadow' ),
   => 'medium',
=> true,
k'      => 'https://wpshadow.com/kb/database-elementor-revisions',
      => array(
=> $total_excess,
sient( $cache_key, $result, 12 * HOUR_IN_SECONDS );
 $result;
sient( $cache_key, null, 24 * HOUR_IN_SECONDS );
 null;
}
}
