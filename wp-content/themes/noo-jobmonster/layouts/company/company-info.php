<?php
$company_name = get_post_field('post_title', $company_id);
$logo_company = Noo_Company::get_company_logo($company_id);
$all_socials = noo_get_social_fields();
wp_enqueue_style('noo-rating');
wp_enqueue_script('noo-rating');

//$slogan = get_post_meta( get_the_ID(), '_jm_company_field__slogan', true );
$slogan = noo_get_post_meta($company_id, '_slogan');
$noo_single_jobs_layout = noo_get_option('noo_single_jobs_layout', 'right_company');
$is_schema = noo_get_option( 'noo_job_schema', false );
$schema_company = $schema_url = $schema_name = $schema_logo = '';
if($is_schema){
    $schema_company = ' itemscope itemtype="http://schema.org/Organization"';
    $schema_url = ' itemprop="url"';
    $schema_name = ' itemprop="name"';
    $schema_logo = ' itemprop="logo"';
}
?>
    <div id="company-desc" class="company-desc" <?php echo esc_attr($schema_company);?>>

        <div class="company-header">
            <div class="company-featured"><a
                        href="<?php echo get_permalink($company_id); ?>" <?php echo esc_attr($schema_url);?>><span <?php echo esc_attr($schema_logo);?>><?php echo $logo_company; ?></span></a></div>
            <div class="company-info-style2">
                <h3 class="company-title" <?php echo esc_attr($schema_name);?>>
                    <a href="<?php echo get_permalink($company_id); ?>">
                        <?php echo esc_html($company_name); ?>
                    </a>
                </h3>
                <?php if (!empty($slogan)) : ?>
                    <div class="slogan">
                        <?php echo esc_html($slogan); ?>
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
                        $html[] = '<a title="' . sprintf($data['label']) . '" class="noo-icon fa ' . $data['icon'] . '" href="' . $url . '" target="_blank"></a>';
                    }
                }


                if (!empty($html) && count($html) > 0) : ?>
                    <div class="job-social clearfix">

                        <?php echo implode("\n", $html); ?>
                    </div>
                <?php endif; ?>
                <?php if (Noo_Company::review_is_enable()): ?>
                    <span class="total-review">
					            <?php noo_box_rating(noo_get_total_point_review($company_id), true) ?>
                        <span><?php echo '(' . noo_get_total_review($company_id) . ')' ?></span>
					        </span>
                <?php endif; ?>
            </div>
        </div>

        <div class="company-info">
            <?php
            // Custom Fields
            $fields = jm_get_company_custom_fields();
            $html = array();
            foreach ($fields as $field) {
                if ($field['name'] == '_logo' || $field['name'] == '_cover_image' || $field['name'] == '_portfolio') {
                    continue;
                }

                $id = jm_company_custom_fields_name($field['name'], $field);
                $value = noo_get_post_meta($company_id, $id, '');

                if ($field['name'] == '_job_category') {

                    $archive_link = get_post_type_archive_link( 'noo_company' );

                    $field['type'] = 'text';
                    $field['is_tax'] = false;
                    $meta = noo_get_post_meta($company_id, '_job_category',array());
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
                    $html[] = '<li>' . noo_display_field($field, $id, $value, array('label_tag' => 'strong', 'label_class' => 'company-cf', 'value_tag' => 'span'), false) . '</li>';
                }
            }
            if (!empty($html) && count($html) > 0) : ?>
                <div class="company-custom-fields">
                    <strong class="company-cf-title"><?php _e('Company Information', 'noo'); ?></strong>
                    <ul>
                        <li class="total-job">
                            <strong><?php _e('Total Jobs ', 'noo') ?></strong>
                            <span><?php echo sprintf(esc_html__('%s Jobs', 'noo'), $total_job) ?></span>
                        </li>
                        <?php echo implode("\n", $html); ?>
                        <?php
                        if (is_singular('noo_company')) {
                            jm_display_full_address_field($company_id);
                        }
                        ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php
            wp_enqueue_script('google-map');
            wp_enqueue_script('google-map-custom');


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
                <div class="job-social clearfix">
                    <span class="noo-social-title"><?php echo esc_html('Connect with us', 'noo'); ?></span>
                    <?php echo implode("\n", $html); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php

// hidden is job submit page.
if (!is_page_template('page-post-job.php')):
    if (noo_get_option('noo_single_company_contact_form', true)):
        $company = get_post($company_id);
        $company_author = $company->post_author;
        $company_author = get_user_by('id', $company_author);
        if ($company_author):
            $company_email = $company_author->user_email;
            if (!empty($company_email)):
                ?>
                <div class="noo-company-contact">
                    <div class="noo-company-contact-title">
                        <?php _e('Contact Us', 'noo'); ?>
                    </div>
                    <div class="noo-company-contact-form">
                        <form id="contact_company_form" class="form-horizontal jform-validate">
                            <div style="display: none">
                                <input type="hidden" name="action" value="noo_ajax_send_contact">
                                <input type="hidden" name="to_email" value="<?php echo $company_email; ?>"/>
                                <input type="hidden" class="security" name="security"
                                       value="<?php echo wp_create_nonce('noo-ajax-send-contact') ?>"/>
                            </div>
                            <div class="form-group">
                            <span class="input-icon">
                                <input type="text" class="form-control jform-validate" id="name" name="from_name"
                                       required="" placeholder="<?php _e('Enter Your Name', 'noo'); ?>"/>
                                <i class="fa fa-home"></i>
                            </span>
                            </div>
                            <div class="form-group">
                            <span class="input-icon">
                                <input type="email" class="form-control jform-validate jform-validate-email" id="email"
                                       name="from_email" required=""
                                       placeholder="<?php _e('Email Address', 'noo'); ?>"/>
                                <i class="fa fa-envelope"></i>
                                <input class="hide" type="text" name="email_rehot" autocomplete="off"/>
                            </span>
                            </div>
                            <div class="form-group">
                            <span class="input-icon">
                                <textarea class="form-control jform-validate" id="message" name="from_message" rows="5"
                                          placeholder="<?php _e('Message...', 'noo'); ?>"></textarea>
                                <i class="fa fa-comment"></i>
                            </span>
                            </div>
                            <?php do_action('noo_company_contact_form'); ?>
                            <div class="form-actions">
                                <button type="submit"
                                        class="btn btn-primary"><?php _e('Send Message', 'noo'); ?></button>
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