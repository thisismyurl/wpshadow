<?php
/**
 * Tips Coach Page
 *
 * @package WPShadow
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! current_user_can( 'read' ) ) {
	wp_die( 'Insufficient permissions.' );
}

// Simple tips
$tips = array(
	array(
		'title'    => __( 'Enable HTTPS', 'wpshadow' ),
		'message'  => __( 'Using HTTPS encrypts data between your visitors and server, improving security and SEO.', 'wpshadow' ),
		'priority' => 'high',
	),
	array(
		'title'    => __( 'Keep WordPress Updated', 'wpshadow' ),
		'message'  => __( 'Regular updates patch security vulnerabilities and add new features.', 'wpshadow' ),
		'priority' => 'high',
	),
	array(
		'title'    => __( 'Use Strong Passwords', 'wpshadow' ),
		'message'  => __( 'Strong, unique passwords prevent unauthorized access to your site.', 'wpshadow' ),
		'priority' => 'medium',
	),
	array(
		'title'    => __( 'Enable Image Lazy Loading', 'wpshadow' ),
		'message'  => __( 'Lazy loading defers loading offscreen images, improving page load speed.', 'wpshadow' ),
		'priority' => 'low',
	),
	array(
		'title'    => __( 'Optimize Images', 'wpshadow' ),
		'message'  => __( 'Compress and resize images before uploading to reduce page size and improve speed.', 'wpshadow' ),
		'priority' => 'medium',
	),
);
?>

<div class="wrap">
	<h1><?php esc_html_e( 'Tips & Guidance', 'wpshadow' ); ?></h1>
	<?php do_action( 'wpshadow_after_page_header' ); ?>
	<p><?php esc_html_e( 'Helpful tips and best practices for managing your WordPress site.', 'wpshadow' ); ?></p>

	<div class="wpshadow-tool-section" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px; margin-top: 20px;">
		<h2><?php esc_html_e( 'Recommended Actions', 'wpshadow' ); ?></h2>
		
		<?php foreach ( $tips as $tip ) : ?>
			<?php
			// Validate priority value for inline style
			$valid_priorities = array( 'high', 'medium', 'low' );
			$priority         = in_array( $tip['priority'], $valid_priorities, true ) ? $tip['priority'] : 'low';
			$border_color     = $priority === 'high' ? '#d63638' : ( $priority === 'medium' ? '#dba617' : '#00a32a' );
			?>
			<div class="wpshadow-tip" style="padding: 15px; border-left: 4px solid <?php echo esc_attr( $border_color ); ?>; background: #f9f9f9; margin-bottom: 15px;">
				<h3 style="margin-top: 0;">
					<?php echo esc_html( $tip['title'] ); ?>
					<span style="font-size: 12px; font-weight: normal; color: #666;">
						(<?php echo esc_html( ucfirst( $priority ) ); ?> priority)
					</span>
				</h3>
				<p style="margin-bottom: 0;"><?php echo esc_html( $tip['message'] ); ?></p>
			</div>
		<?php endforeach; ?>
	</div>

	<div class="wpshadow-tool-section" style="background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 4px; margin-top: 20px;">
		<h2><?php esc_html_e( 'Additional Resources', 'wpshadow' ); ?></h2>
		<ul>
			<li><a href="<?php echo esc_url( admin_url( 'admin.php?page=wpshadow' ) ); ?>"><?php esc_html_e( 'View Site Health Dashboard', 'wpshadow' ); ?></a></li>
			<li><a href="https://wordpress.org/support/" target="_blank"><?php esc_html_e( 'WordPress Support Forums', 'wpshadow' ); ?></a></li>
			<li><a href="https://wordpress.org/documentation/" target="_blank"><?php esc_html_e( 'WordPress Documentation', 'wpshadow' ); ?></a></li>
		</ul>
	</div>
</div>
