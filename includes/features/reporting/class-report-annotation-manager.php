<?php
/**
 * Report Annotation Manager
 *
 * Handles notes and comments on report findings.
 *
 * @package    WPShadow
 * @subpackage Reporting
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Reporting;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Report_Annotation_Manager Class
 *
 * Manages annotations on report findings.
 *
 * @since 1.6093.1200
 */
class Report_Annotation_Manager {

	/**
	 * Comment type used for report annotations.
	 *
	 * @var string
	 */
	private const COMMENT_TYPE = 'wpshadow_report_annotation';

	/**
	 * Maybe create table
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	public static function maybe_create_table() {
		return;
	}

	/**
	 * Add annotation
	 *
	 * @since 1.6093.1200
	 * @param  string $report_id Report ID.
	 * @param  string $finding_id Finding ID.
	 * @param  string $text Annotation text.
	 * @param  array  $options Additional options.
	 * @return int|false Annotation ID or false.
	 */
	public static function add_annotation( $report_id, $finding_id, $text, $options = array() ) {
		self::maybe_create_table();

		$annotation_id = wp_insert_comment(
			array(
				'comment_post_ID'      => 0,
				'comment_content'      => wp_kses_post( $text ),
				'user_id'              => get_current_user_id(),
				'comment_author'       => wp_get_current_user()->display_name,
				'comment_author_email' => wp_get_current_user()->user_email,
				'comment_type'         => self::COMMENT_TYPE,
				'comment_approved'     => 1,
			)
		);

		if ( $annotation_id ) {
			update_comment_meta( $annotation_id, 'report_id', sanitize_key( $report_id ) );
			update_comment_meta( $annotation_id, 'finding_id', sanitize_key( $finding_id ) );
			update_comment_meta( $annotation_id, 'action_taken', isset( $options['action_taken'] ) ? sanitize_text_field( $options['action_taken'] ) : '' );
			update_comment_meta( $annotation_id, 'status', isset( $options['status'] ) ? sanitize_key( $options['status'] ) : 'open' );
			update_comment_meta( $annotation_id, 'updated_at', '' );
			
			/**
			 * Fires after annotation is added.
			 *
			 * @since 1.6093.1200
			 *
			 * @param int    $annotation_id Annotation ID.
			 * @param string $report_id Report ID.
			 * @param string $finding_id Finding ID.
			 */
			do_action( 'wpshadow_after_annotation_added', $annotation_id, $report_id, $finding_id );
			
			return $annotation_id;
		}
		
		return false;
	}

	/**
	 * Get annotations for finding
	 *
	 * @since 1.6093.1200
	 * @param  string $report_id Report ID.
	 * @param  string $finding_id Finding ID.
	 * @return array Annotations.
	 */
	public static function get_annotations( $report_id, $finding_id ) {
		$query = new \WP_Comment_Query();
		$results = $query->query(
			array(
				'type'       => self::COMMENT_TYPE,
				'status'     => 'approve',
				'orderby'    => 'comment_date_gmt',
				'order'      => 'DESC',
				'number'     => 500,
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key'   => 'report_id',
						'value' => sanitize_key( $report_id ),
					),
					array(
						'key'   => 'finding_id',
						'value' => sanitize_key( $finding_id ),
					),
				),
			)
		);

		if ( empty( $results ) ) {
			return array();
		}

		$annotations = array();
		foreach ( $results as $comment ) {
			$annotations[] = self::map_comment_to_annotation( $comment );
		}

		return $annotations;
	}

	/**
	 * Update annotation status
	 *
	 * @since 1.6093.1200
	 * @param  int    $annotation_id Annotation ID.
	 * @param  string $status New status.
	 * @return bool Success.
	 */
	public static function update_status( $annotation_id, $status ) {
		$annotation_id = absint( $annotation_id );
		if ( $annotation_id <= 0 ) {
			return false;
		}

		$meta_updated = update_comment_meta( $annotation_id, 'status', sanitize_key( $status ) );
		update_comment_meta( $annotation_id, 'updated_at', current_time( 'mysql' ) );

		return false !== $meta_updated;
	}

	/**
	 * Get all annotations for report
	 *
	 * @since 1.6093.1200
	 * @param  string $report_id Report ID.
	 * @return array Annotations grouped by finding.
	 */
	public static function get_report_annotations( $report_id ) {
		$query = new \WP_Comment_Query();
		$results = $query->query(
			array(
				'type'       => self::COMMENT_TYPE,
				'status'     => 'approve',
				'orderby'    => 'comment_date_gmt',
				'order'      => 'DESC',
				'number'     => 500,
				'meta_query' => array(
					array(
						'key'   => 'report_id',
						'value' => sanitize_key( $report_id ),
					),
				),
			)
		);
		
		// Group by finding_id
		$grouped = array();
		foreach ( $results as $annotation ) {
			$normalized = self::map_comment_to_annotation( $annotation );
			$finding_id = $normalized['finding_id'];
			if ( ! isset( $grouped[ $finding_id ] ) ) {
				$grouped[ $finding_id ] = array();
			}
			$grouped[ $finding_id ][] = $normalized;
		}
		
		return $grouped;
	}

	/**
	 * Delete annotation
	 *
	 * @since 1.6093.1200
	 * @param  int $annotation_id Annotation ID.
	 * @return bool Success.
	 */
	public static function delete_annotation( $annotation_id ) {
		return false !== wp_delete_comment( absint( $annotation_id ), true );
	}

	/**
	 * Normalize comment record into legacy annotation array shape.
	 *
	 * @since 1.6093.1200
	 * @param  \WP_Comment $comment Comment object.
	 * @return array Annotation array.
	 */
	private static function map_comment_to_annotation( \WP_Comment $comment ) {
		$updated_at = get_comment_meta( $comment->comment_ID, 'updated_at', true );

		return array(
			'id'              => (int) $comment->comment_ID,
			'report_id'       => (string) get_comment_meta( $comment->comment_ID, 'report_id', true ),
			'finding_id'      => (string) get_comment_meta( $comment->comment_ID, 'finding_id', true ),
			'annotation_text' => (string) $comment->comment_content,
			'action_taken'    => (string) get_comment_meta( $comment->comment_ID, 'action_taken', true ),
			'status'          => (string) get_comment_meta( $comment->comment_ID, 'status', true ),
			'user_id'         => (int) $comment->user_id,
			'created_at'      => (string) $comment->comment_date,
			'updated_at'      => ! empty( $updated_at ) ? (string) $updated_at : null,
		);
	}
}
