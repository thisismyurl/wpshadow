<?php
/**
 * Help Tab View
 *
 * Displays plugin documentation, FAQ, and support resources with dynamic content.
 *
 * @package WPSHADOW_wpshadow_THISISMYURL
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get current tab, default to overview.
$current_tab = isset( $_GET['help_section'] ) ? sanitize_key( $_GET['help_section'] ) : 'overview'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

// Fetch help content.
$help_content = WPSHADOW_Help_Content_API::get_content();

// Define available tabs.
$tabs = array(
	'overview'        => __( 'Overview', 'plugin-wpshadow' ),
	'getting-started' => __( 'Getting Started', 'plugin-wpshadow' ),
	'modules'         => __( 'Modules', 'plugin-wpshadow' ),
	'faq'             => __( 'FAQ', 'plugin-wpshadow' ),
);

// Build tab URLs.
$base_url = add_query_arg( 'wpshadow_tab', 'help', admin_url( 'admin.php?page=wpshadow' ) );
?>

<div class="wrap wps-help-page">
	<h1><?php esc_html_e( 'Help & Documentation', 'plugin-wpshadow' ); ?></h1>

	<!-- License Widget -->
	<div class="wps-help-license-row">
		<div id="wpshadow_license_widget" class="postbox">
			<?php WPSHADOW_License_Widget::render_widget(); ?>
		</div>
	</div>

	<!-- Help Tab Navigation -->
	<nav class="wps-help-tabs nav-tab-wrapper wp-clearfix" role="navigation" aria-label="<?php esc_attr_e( 'Help sections', 'plugin-wpshadow' ); ?>">
		<?php foreach ( $tabs as $tab_id => $tab_label ) : ?>
			<?php
			$tab_url      = add_query_arg( 'help_section', $tab_id, $base_url );
			$is_current   = ( $current_tab === $tab_id );
			$aria_current = $is_current ? ' aria-current="page"' : '';
			?>
			<a href="<?php echo esc_url( $tab_url ); ?>" 
				class="nav-tab<?php echo $is_current ? ' nav-tab-active' : ''; ?>"
				<?php echo $aria_current; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
				<?php echo esc_html( $tab_label ); ?>
			</a>
		<?php endforeach; ?>
	</nav>

	<!-- Help Content Area -->
	<div class="wps-help-content">
		<?php
		// Display content for current tab.
		if ( isset( $help_content[ $current_tab ] ) && is_array( $help_content[ $current_tab ] ) ) :
			$section = $help_content[ $current_tab ];
			?>

			<div class="wps-help-section wps-help-section-<?php echo esc_attr( $current_tab ); ?>">
				<?php if ( ! empty( $section['title'] ) ) : ?>
					<h2><?php echo esc_html( $section['title'] ); ?></h2>
				<?php endif; ?>

				<?php if ( ! empty( $section['description'] ) ) : ?>
					<p class="wps-help-description"><?php echo esc_html( $section['description'] ); ?></p>
				<?php endif; ?>

				<?php
				// Render content based on section type.
				if ( 'faq' === $current_tab && ! empty( $section['content'] ) ) :
					// FAQ format: questions and answers.
					?>
					<div class="wps-help-faq">
						<?php foreach ( $section['content'] as $item ) : ?>
							<?php if ( ! empty( $item['question'] ) && ! empty( $item['answer'] ) ) : ?>
								<div class="wps-faq-item">
									<h3 class="wps-faq-question">
										<span class="dashicons dashicons-arrow-right-alt2" aria-hidden="true"></span>
										<?php echo esc_html( $item['question'] ); ?>
									</h3>
									<div class="wps-faq-answer">
										<p><?php echo esc_html( $item['answer'] ); ?></p>
									</div>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				<?php elseif ( ! empty( $section['content'] ) ) : ?>
					<!-- Standard content format: headings and text -->
					<div class="wps-help-standard-content">
						<?php foreach ( $section['content'] as $item ) : ?>
							<?php if ( ! empty( $item['heading'] ) || ! empty( $item['text'] ) ) : ?>
								<div class="wps-help-content-item">
									<?php if ( ! empty( $item['heading'] ) ) : ?>
										<h3><?php echo esc_html( $item['heading'] ); ?></h3>
									<?php endif; ?>
									<?php if ( ! empty( $item['text'] ) ) : ?>
										<p><?php echo esc_html( $item['text'] ); ?></p>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

		<?php else : ?>
			<!-- Fallback if section not found -->
			<div class="wps-help-section wps-help-error">
				<p><?php esc_html_e( 'Help content is currently unavailable. Please try refreshing the page.', 'plugin-wpshadow' ); ?></p>
			</div>
		<?php endif; ?>

		<!-- Support Links -->
		<div class="wps-help-footer">
			<h3><?php esc_html_e( 'Additional Resources', 'plugin-wpshadow' ); ?></h3>
			<ul class="wps-help-links">
				<li>
					<span class="dashicons dashicons-book" aria-hidden="true"></span>
					<a href="https://wpshadow.com/plugin-wpshadow/" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Full Documentation', 'plugin-wpshadow' ); ?>
					</a>
				</li>
				<li>
					<span class="dashicons dashicons-admin-generic" aria-hidden="true"></span>
					<a href="https://github.com/thisismyurl/plugin-wpshadow" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'GitHub Repository', 'plugin-wpshadow' ); ?>
					</a>
				</li>
				<li>
					<span class="dashicons dashicons-sos" aria-hidden="true"></span>
					<a href="https://wpshadow.com/support" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Professional Support', 'plugin-wpshadow' ); ?>
					</a>
				</li>
			</ul>
		</div>
	</div>
</div>
