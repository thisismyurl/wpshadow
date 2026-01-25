<?php
/**
 * Footer template.
 */
?>
</main>
<footer class="site-footer">
	<div class="container footer-inner">
		<p>&copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'Powered by WPShadow.', 'wpshadow-theme' ); ?></p>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
