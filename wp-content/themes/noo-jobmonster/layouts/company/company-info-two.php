<?php
$company_name = get_post_field('post_title', $company_id);
$all_socials = noo_get_social_fields();
wp_enqueue_script('noo-lightgallery');
wp_enqueue_style('noo-lightgallery');
?>
<?php
$company_name = get_post_field( 'post_title', get_the_ID() );
$logo_company = Noo_Company::get_company_logo( get_the_ID() );
$post_view = noo_get_post_views( get_the_ID() );
//$slogan = get_post_meta( get_the_ID(), '_jm_company_field__slogan', true );
$slogan         = noo_get_post_meta( $company_id, '_slogan' );

?>
<div class="noo-company-info-simple">
    <div class="company-avatar">
        <a href="<?php echo get_permalink(); ?>"><?php echo $logo_company;?></a>
    </div>
    <div class="company-info">
        <h3 class="company-name">
            <?php echo get_the_title(); ?>
        </h3>
        <?php if ( !empty( $slogan ) ) : ?>
            <div class="slogan">
                <?php echo esc_html( $slogan ); ?>
            </div>
        <?php endif; ?>
	    <?php
	    // Job's social info
	    $socials = jm_get_company_socials();
	    $html = array();

	    foreach ($socials as $social) {
		    if (!isset($all_socials[$social])) continue;
		    $data = $all_socials[$social];
		    $value = get_post_meta($company_id, "_{$social}", true);
		    if (!empty($value)) {
			    $url = $social == 'email_address' ? 'mailto:' . $value : esc_url($value);
			    $html[] = '<a title="' . sprintf(esc_attr__('Connect with us on %s', 'noo'), $data['label']) . '" class="noo-icon fa ' . $data['icon'] . '" href="' . $url . '" target="_blank"></a>';
		    }
	    }

	    if (!empty($html) && count($html) > 0) : ?>
            <div class="company-social">
			    <?php echo implode("\n", $html); ?>
            </div>
	    <?php endif; ?>
	    <?php if ( Noo_Company::review_is_enable() ): ?>
        <span class="total-review">
            <?php noo_box_rating( noo_get_total_point_review( get_the_ID() ), true ) ?>
            <span><?php echo '(' . noo_get_total_review( get_the_ID() ) . ')' ?></span>
        </span>
	    <?php endif; ?>
    </div>
</div>

<?php
// Custom Fields
$fields = jm_get_company_custom_fields();
$html = array();

foreach ($fields as $field) {
    if( $field['name'] == '_logo' || $field['name'] == '_cover_image' || $field['name'] == '_slogan' || $field['name'] == '_address' || $field['name'] == '_portfolio' ) {
        continue;
    }

    $id = jm_company_custom_fields_name($field['name'], $field);
    $value = noo_get_post_meta($company_id, $id, '');

    if ($field['name'] == '_job_category') {

        $archive_link = get_post_type_archive_link( 'noo_company' );

        $field['type'] = 'text';
        $field['is_tax'] = false;
        $meta = noo_get_post_meta($company_id, $id, '');
        if( ! empty($meta)){
            $meta=noo_json_decode($meta);
            $links = array();
            foreach ( $meta as $cat_id) {
                $term = get_term_by('id', $cat_id, 'job_category');
                if (!empty($term)){
                    $cat_name = $term->name;
                    $cat_url = esc_url( add_query_arg( array( 'company_category' => $term->term_id ), $archive_link ) );
                    $links[] = '<a href="' . $cat_url . '">' . $cat_name . '</a>';
                }
            }
            $value = join(", ", $links);
        }
    }

    if (!empty($value)) {
        $html[] = '<li class="' . $field['name'] . '">' . noo_display_field($field, $id, $value, array('label_tag' => 'strong', 'label_class' => 'company-cf', 'value_tag' => 'span'), false) . '</li>';
    }
}
if (!empty($html) && count($html) > 0) : ?>
    <div class="company-custom-fields">
        <h3 class="noo-heading"><?php _e('Information Company', 'noo'); ?></h3>
		<?php if ( is_singular( 'noo_company' ) ) :
					jm_the_job_meta( array(
						'show_company' => false,
						'fields'       => array(
							'job_category',
						),
					) );
			endif; ?>
        <ul>
            <li class="total-job">
            	<strong><?php _e('Total Jobs','noo' ) ?></strong>
                <span><?php echo sprintf( esc_html__( '%s Jobs', 'noo' ), $total_job ) ?></span>
            </li>
            <li class="_address">
                <?php echo noo_get_company_address( get_the_ID() ) ?>
            </li>
            <?php echo implode("\n", $html); ?>
        </ul>
    </div>
<?php endif; ?>

<div class="noo-company-content">
    <h3 class="noo-heading"><?php _e('Something About Company', 'noo'); ?></h3>
	<?php
	$content = get_post_field( 'post_content', get_the_ID() );
	$content = apply_filters('the_content', $content);
	echo $content;
	?>
</div>

<?php
$photo_arr = noo_get_post_meta(get_the_ID(), "_portfolio", '');
if(!empty($photo_arr)) :
	if ( !is_array( $photo_arr ) ) {
		$photo_arr = explode(',', $photo_arr);
	}
	?>
    <div class="noo-company-content">
        <h3 class="noo-heading">
            <span><?php _e( 'Office Photos', 'noo' ); ?></span>
        </h3>
        <div id="company-photo" class="company-photo row is-flex">
			<?php
			foreach ( $photo_arr as $image_id ) :
				if ( empty( $image_id ) )
					continue;

				$image = wp_get_attachment_image_src( $image_id, array(145,180));
				$image_full = wp_get_attachment_image_src( $image_id, 'full');

				echo '<a class="col-md-6" href="' . $image_full[0] . '"><img src="' . esc_url( $image[0] ) . '" alt="*" /></a>';

			endforeach;
			?>
        </div>
    </div>
<?php endif; ?>
<?php

// hidden is job submit page.
if ( ! is_page_template( 'page-post-job.php' ) ):

	if ( noo_get_option( 'noo_single_company_contact_form', true ) ):
		$company = get_post( get_the_ID() );
		$company_author = $company->post_author;
		$company_author = get_user_by( 'id', $company_author );
		if ( $company_author ):
			$company_email = $company_author->user_email;
			if ( ! empty( $company_email ) ):
				?>
				<div class="noo-company-contact">
					<h3 class="noo-heading">
						<?php _e( 'Contact Us', 'noo' ); ?>
					</h3>
					<div class="noo-company-contact-form">
						<form id="contact_company_form" class="form-horizontal jform-validate">
							<div style="display: none">
								<input type="hidden" name="action" value="noo_ajax_send_contact">
								<input type="hidden" name="to_email" value="<?php echo $company_email; ?>"/>
								<input type="hidden" class="security" name="security"
								       value="<?php echo wp_create_nonce( 'noo-ajax-send-contact' ) ?>"/>
							</div>
							<div class="form-group">
                            <span class="input-icon">
                                <input type="text" class="form-control jform-validate" id="name" name="from_name"
                                       required="" placeholder="<?php _e( 'Enter Your Name', 'noo' ); ?>"/>
                                <i class="fa fa-home"></i>
                            </span>
							</div>
							<div class="form-group">
                            <span class="input-icon">
                                <input type="email" class="form-control jform-validate jform-validate-email" id="email"
                                       name="from_email" required=""
                                       placeholder="<?php _e( 'Email Address', 'noo' ); ?>"/>
                                <i class="fa fa-envelope"></i>
                                <input class="hide" type="text" name="email_rehot" autocomplete="off"/>
                            </span>
							</div>
							<div class="form-group">
                            <span class="input-icon">
                                <textarea class="form-control jform-validate" id="message" name="from_message" rows="5"
                                          placeholder="<?php _e( 'Message...', 'noo' ); ?>"></textarea>
                                <i class="fa fa-comment"></i>
                            </span>
							</div>
							<?php do_action( 'noo_company_contact_form' ); ?>
							<div class="form-actions">
								<button type="submit"
								        class="btn btn-primary"><?php _e( 'Send Message', 'noo' ); ?></button>
							</div>
							<div class="noo-ajax-result"></div>
						</form>
					</div>
				</div>
				<?php
			endif;
		endif;
	endif;

endif;
?>

<?php add_action( 'wp_footer', function() { ?>
    <script>
		jQuery(document).ready(function() {
			lightGallery(document.getElementById('company-photo'), {
				thumbnail:true
			});
		});
    </script>
<?php }, 999);