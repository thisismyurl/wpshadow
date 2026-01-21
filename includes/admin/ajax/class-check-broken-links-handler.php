<?php
/**
 * Check Broken Links AJAX Handler
 *
 * @package WPShadow
 */

declare(strict_types=1);

namespace WPShadow\Admin\Ajax;

use WPShadow\Core\AJAX_Handler_Base;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Check_Broken_Links_Handler extends AJAX_Handler_Base {
    public static function register() : void {
        add_action( 'wp_ajax_wpshadow_check_broken_links', [ __CLASS__, 'handle' ] );
    }

    public static function handle() : void {
        self::verify_request( 'wpshadow_link_check', 'read', 'nonce' );

        $check_internal = self::get_post_param( 'check_internal', 'int', 1 );
        $check_external = self::get_post_param( 'check_external', 'int', 1 );
        $check_images   = self::get_post_param( 'check_images', 'int', 0 );

        $broken_links  = array();
        $posts_checked = 0;
        $links_checked = 0;

        $args = array(
            'post_type'      => array( 'post', 'page' ),
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        );

        $posts = get_posts( $args );
        $posts_checked = count( $posts );

        foreach ( $posts as $post ) {
            $content = $post->post_content;

            preg_match_all( '/<a\s+(?:[^>]*?\s+)?href=["\']([^"\']+)["\']/', $content, $matches );
            if ( ! empty( $matches[1] ) ) {
                foreach ( $matches[1] as $url ) {
                    $links_checked++;

                    if ( strpos( $url, '#' ) === 0 ) {
                        continue;
                    }

                    $is_internal = strpos( $url, home_url() ) === 0 || strpos( $url, '/' ) === 0;

                    if ( $is_internal && ! $check_internal ) {
                        continue;
                    }

                    if ( ! $is_internal && ! $check_external ) {
                        continue;
                    }

                    if ( strpos( $url, '/' ) === 0 ) {
                        $url = home_url( $url );
                    }

                    $response = wp_remote_head( $url, array(
                        'timeout'     => 5,
                        'redirection' => 2,
                    ) );

                    if ( is_wp_error( $response ) ) {
                        $broken_links[] = array(
                            'url'         => $url,
                            'post_title'  => $post->post_title,
                            'edit_url'    => get_edit_post_link( $post->ID, 'raw' ),
                            'status_code' => 'ERROR',
                        );
                    } else {
                        $code = wp_remote_retrieve_response_code( $response );
                        if ( $code >= 400 ) {
                            $broken_links[] = array(
                                'url'         => $url,
                                'post_title'  => $post->post_title,
                                'edit_url'    => get_edit_post_link( $post->ID, 'raw' ),
                                'status_code' => $code,
                            );
                        }
                    }
                }
            }

            if ( $check_images ) {
                preg_match_all( '/<img\s+(?:[^>]*?\s+)?src=["\']([^"\']+)["\']/', $content, $img_matches );
                if ( ! empty( $img_matches[1] ) ) {
                    foreach ( $img_matches[1] as $img_url ) {
                        $links_checked++;

                        $response = wp_remote_head( $img_url, array(
                            'timeout'     => 5,
                            'redirection' => 2,
                        ) );

                        if ( is_wp_error( $response ) ) {
                            $broken_links[] = array(
                                'url'         => $img_url,
                                'post_title'  => $post->post_title . ' (image)',
                                'edit_url'    => get_edit_post_link( $post->ID, 'raw' ),
                                'status_code' => 'ERROR',
                            );
                        } else {
                            $code = wp_remote_retrieve_response_code( $response );
                            if ( $code >= 400 ) {
                                $broken_links[] = array(
                                    'url'         => $img_url,
                                    'post_title'  => $post->post_title . ' (image)',
                                    'edit_url'    => get_edit_post_link( $post->ID, 'raw' ),
                                    'status_code' => $code,
                                );
                            }
                        }
                    }
                }
            }
        }

        self::send_success( array(
            'posts_checked' => $posts_checked,
            'links_checked' => $links_checked,
            'broken_links'  => $broken_links,
            'broken_count'  => count( $broken_links ),
        ) );
    }
}
