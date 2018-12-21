<?php
/**
 * noo-faq.php
 *
 * @author  : NooTheme
 * @since   : 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! function_exists( 'noo_faq_shortcode' ) ) :
	function noo_faq_shortcode( $atts, $content = null ) {
		extract(shortcode_atts(array(
			'noo_faq_group' =>  ''
		),$atts));

		$noo_faq_new = vc_param_group_parse_atts( $noo_faq_group );

		ob_start();

		$class = uniqid('noo_faq_group_');

		?>
		<div class="noo-faq">
			<div class="noo_faq_group <?php echo esc_attr($class); ?>">
				<?php
				if( isset($noo_faq_new) && !empty($noo_faq_new) ):
					foreach( $noo_faq_new as $item ){
						?>
						<div class="noo_faq_item <?php echo esc_attr($item['open']); ?>">
							<h4 class="noo_faq_control">
								<?php
								echo esc_html($item['title']);
								?>
							</h4>
							<div class="noo_faq_content"><?php echo __( $item['description'] ); ?></div>
						</div>
						<?php
					}
				endif;
				?>
			</div>
		</div>
		<script>
			jQuery(document).ready(function($) {
				$('.hide_faq .noo_faq_content').slideUp(0);
				$('.<?php echo esc_attr($class)  ?> .noo_faq_item .noo_faq_control').click(function() {
					var $this = $(this).parent('.noo_faq_item');
					$('.<?php echo esc_attr($class)  ?> .noo_faq_item').removeClass('open_faq').addClass('hide_faq');
					$('.hide_faq .noo_faq_content').slideUp(300);

					$this.removeClass('hide_faq');
					$this.addClass('open_faq');
					$this.find('.noo_faq_content').slideDown(300);
				});
			});
		</script>
		<?php
		$faq = ob_get_contents();
		ob_end_clean();
		return $faq;
	}

	add_shortcode( 'noo_faq', 'noo_faq_shortcode' );

endif;