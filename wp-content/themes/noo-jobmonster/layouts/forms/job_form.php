<?php
wp_enqueue_script('typeahead');
wp_enqueue_script('bootstrap-tagsinput');

$job_id = isset($_GET['job_id']) ? absint($_GET['job_id']) : 0;
$employer_id = get_current_user_id();
$job = get_post($job_id);
$default_job_content = jm_get_job_setting('default_job_content', '');
$content = $job_id ? $job->post_content : $default_job_content;
$fields = jm_get_job_custom_fields();

?>
<div class="job-form">
    <div class="job-form-detail row">
        <div class="form-group row col-md-12 required-field">
            <label for="position" class="col-sm-3 control-label"><?php _e('Job Title', 'noo') ?></label>
            <div class="col-sm-9">
                <input type="text" value="<?php echo($job_id ? $job->post_title : '') ?>"
                       class="form-control jform-validate" id="position" name="position" autofocus required
                       placeholder="<?php echo esc_attr__('Enter a short title for your job', 'noo') ?>">
            </div>
        </div>
        <div class="form-group row col-md-12">
            <label for="desc" class="col-sm-3 control-label"><?php _e('Job Description', 'noo') ?></label>
            <div class="col-sm-9">
                <?php noo_wp_editor($content, 'desc'); ?>
            </div>
        </div>
        <?php
        if (!empty($fields)) {
            foreach ($fields as $field) {

                if ($field['name'] == 'job_category') {
                    $label=(!empty($field['plural'])) ? $field['plural'] : $field['label'];

                    $allow_multiple_select = strpos($field['type'], 'multi') !== false;

                    $name = $allow_multiple_select ? 'category[]' : 'category';
                    $selected = array();
                    if ($job_id) {
                        $cats = get_the_terms($job_id, 'job_category');
                        if (!empty($cats) && !is_wp_error($cats)) {
                            foreach ((array)$cats as $cat) {
                                $selected[] = $cat->term_id;
                            }
                        }

                    }

                    $required = $field['required'] ? 'required-field' : '';

                    $job_category_args = array(
                        'hide_empty' => 0,
                        'echo' => 1,
                        'selected' => $selected,
                        'hierarchical' => 1,
                        'name' => $name,
                        'id' => 'noo-field-job_category',
                        'class' => 'form-control noo-select form-control-chosen',
                        'depth' => 0,
                        'taxonomy' => 'job_category',
                        'value_field' => 'term_id',
                        'required' => true,
                        'orderby' => 'name',
                        'multiple' => $allow_multiple_select,
                        'walker' => new Noo_Walker_TaxonomyDropdown(),
                    );
                    if (!$allow_multiple_select) {
                        $job_category_args['show_option_none'] = sprintf(esc_html__('Select %s', 'noo'), $field['label']);
                        $job_category_args['option_none_value'] = '';
                    }

                    ?>
                    <div class="form-group row col-md-12 <?php echo $required; ?>"
                         data-placeholder="<?php echo esc_html__((sprintf( __( 'Select %s', 'noo'),$field['label']) )); ?>">
                        <label for="<?php echo 'noo-field-' . esc_attr($field['name']); ?>"
                               class="col-sm-3 control-label"><?php echo $label ?></label>
                        <div class="col-sm-9">
                            <?php wp_dropdown_categories($job_category_args); ?>
                        </div>
                    </div>
                    <?php
                } else {
                    jm_job_render_form_field($field, $job_id);
                }
            }
        }
        ?>

        <div class="form-group row col-md-12">
            <label for="application_email"
                   class="col-sm-3 control-label"><?php _e('Notification Email', 'noo') ?></label>
            <div class="col-sm-9">
                <input type="text" value="<?php echo($job ? noo_get_post_meta($job_id, '_application_email') : '') ?>"
                       class="form-control" id="application_email" name="application_email">
                <em><?php _e('Email to receive application notification. Leave it blank to use your account email.', 'noo'); ?></em>
            </div>
        </div>
        <?php $custom_apply_link = jm_get_setting('noo_job_linkedin', 'custom_apply_link');
        if ($custom_apply_link == 'employer') :
            ?>
            <div class="form-group row col-md-12">
                <label for="custom_application_url"
                       class="col-sm-3 control-label"><?php _e('Custom Application URL', 'noo') ?></label>
                <div class="col-sm-9">
                    <input type="text"
                           value="<?php echo($job ? noo_get_post_meta($job_id, '_custom_application_url') : '') ?>"
                           class="form-control" id="custom_application_url" name="custom_application_url">
                    <em><?php _e('Custom link to redirect job seekers to when applying for this job.', 'noo'); ?></em>
                </div>
            </div>
        <?php endif; ?>

        <?php do_action('job_form_after_field', $job_id); ?>
    </div>
    <?php if (!jm_get_employer_company($employer_id)): ?>
        <div class="job-form-company">
            <h4><?php _e('Company Profile', 'noo') ?></h4>
            <?php echo Noo_Member::get_company_profile_form(false) ?>
        </div>
    <?php
    endif;
    ?>
</div>
