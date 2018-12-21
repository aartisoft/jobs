<?php

if (!function_exists('jm_get_job_custom_fields')) :
    function jm_get_job_custom_fields($include_disabled_fields = false, $suppress_filters = false)
    {
        $custom_fields = noo_get_custom_fields('noo_job_custom_field', 'noo_job_field_');

        $default_fields = jm_get_job_default_fields();

        $custom_fields = noo_merge_custom_fields($default_fields, $custom_fields, $include_disabled_fields);

        return $suppress_filters ? $custom_fields : apply_filters('jm_job_custom_fields', $custom_fields);
    }
endif;

if (!function_exists('jm_get_job_search_custom_fields')) :
    function jm_get_job_search_custom_fields()
    {
        $custom_fields = jm_get_job_custom_fields();
        $date_field = array(
            'name' => 'date',
            'type' => 'datepicker',
            'label' => __('Publishing Date', 'noo'),
            'is_default' => true,
        );
        $custom_fields[] = $date_field;
        $not_searchable = noo_not_searchable_custom_fields_type();
        foreach ($custom_fields as $key => $field) {
            if (in_array($field['type'], $not_searchable)) {
                unset($custom_fields[$key]);
            }
        }
        return apply_filters('jm_job_search_custom_fields', $custom_fields);
    }
endif;

if (!function_exists('jm_job_custom_fields_prefix')) :
    function jm_job_custom_fields_prefix()
    {
        return apply_filters('jm_job_custom_fields_prefix', '_noo_job_field_');
    }
endif;

if (!function_exists('jm_job_custom_fields_name')) :
    function jm_job_custom_fields_name($field_name = '', $field = array())
    {
        if (empty($field_name)) {
            return '';
        }

        $cf_name = jm_job_custom_fields_prefix() . sanitize_title($field_name);

        if (!empty($field) && isset($field['is_default'])) {
            $cf_name = $field['name'];
        }

        return apply_filters('jm_job_custom_fields_name', $cf_name, $field_name, $field);
    }
endif;

if (!function_exists('jm_get_job_field')) :
    function jm_get_job_field($field_name = '')
    {

        $custom_fields = jm_get_job_custom_fields(false, true);
        if (isset($custom_fields[$field_name])) {
            return $custom_fields[$field_name];
        }

        foreach ($custom_fields as $field) {
            if ($field_name == $field['name']) {
                return $field;
            }
        }

        return array();
    }
endif;

if (!function_exists('jm_get_job_custom_fields_option')) :
    function jm_get_job_custom_fields_option($key = '', $default = null)
    {
        $custom_fields = jm_get_setting('noo_job_custom_field', array());

        if (!$custom_fields || !is_array($custom_fields)) {
            return $default;
        }

        if (isset($custom_fields['__options__']) && isset($custom_fields['__options__'][$key])) {

            return $custom_fields['__options__'][$key];
        }

        return $default;
    }
endif;

if (!function_exists('jm_job_cf_settings_tabs')) :
    function jm_job_cf_settings_tabs($tabs = array())
    {
        $temp1 = array_slice($tabs, 0, 1);
        $temp2 = array_slice($tabs, 1);

        $job_cf_tab = array('job' => __('Job', 'noo'));

        return array_merge($temp1, $job_cf_tab, $temp2);
    }

    // Add to Custom Field (cf) tab.
    add_filter('jm_cf_settings_tabs_array', 'jm_job_cf_settings_tabs', 5);
endif;


if (!function_exists('jm_job_custom_fields_setting')) :
    function jm_job_custom_fields_setting()
    {
        wp_enqueue_style('noo-custom-fields');
        wp_enqueue_script('noo-custom-fields');

        noo_custom_fields_setting(
            'noo_job_custom_field',
            'noo_job_field_',
            jm_get_job_custom_fields(true)
        );

        $field_display = jm_get_job_custom_fields_option('display_position', 'after');
        ?>
        <table class="form-table" cellspacing="0">
            <tbody>
            <tr>
                <th>
                    <?php _e('Show Custom Fields:', 'noo') ?>
                </th>
                <td>
                    <select class="regular-text" name="noo_job_custom_field[__options__][display_position]">
                        <option <?php selected($field_display, 'before') ?>
                                value="before"><?php _e('Before Description', 'noo') ?></option>
                        <option <?php selected($field_display, 'after') ?>
                                value="after"><?php _e('After Description', 'noo') ?></option>
                    </select>
                </td>
            </tr>
            </tbody>
        </table>
        <?php do_action('jm_job_custom_fields_setting_options');
    }

    add_action('jm_cf_setting_job', 'jm_job_custom_fields_setting');
endif;

if (!function_exists('jm_job_render_form_field')) :
    function jm_job_render_form_field($field = array(), $job_id = 0)
    {
        $field_id = jm_job_custom_fields_name($field['name'], $field);

        $value = !empty($job_id) ? noo_get_post_meta($job_id, $field_id, '') : '';
        $value = isset($_REQUEST[$field_id]) ? $_REQUEST[$field_id] : $value;
        $value = !is_array($value) ? trim($value) : $value;

        $params = apply_filters('jm_job_render_form_field_params', compact('field', 'field_id', 'value'), $job_id);
        extract($params);
        $object = array('ID' => $job_id, 'type' => 'post');
        $field_label = (!empty($field['plural'])) ? $field['plural'] : $field['label'];
        $label = isset($field['label_translated']) ? $field['label_translated'] : $field_label;

        ?>
        <div class="form-group row col-md-12 <?php noo_custom_field_class($field, $object); ?>"
            <?php echo($field['name'] == 'job_location' ? 'data-placeholder="' . sprintf(esc_html__('Select %s', 'noo'), $label) . '"' : ''); ?>
        >
            <label for="<?php echo esc_attr($field_id) ?>"
                   class="col-sm-3 control-label"><?php echo($label) ?></label>
            <div class="col-sm-9">
                <?php noo_render_field($field, $field_id, $value, '', $object); ?>
            </div>
        </div>
        <?php
    }
endif;

if (!function_exists('jm_job_render_search_field')) :
    function jm_job_render_search_field($field = array(), $disable_multiple_select = false)
    {
        $field_id = jm_job_custom_fields_name($field['name'], $field);

        $field['required'] = ''; // no need for required fields in search form

        if ($disable_multiple_select) {
            $field['disable_multiple'] = true;
        }

        $value = isset($_GET[$field_id]) ? $_GET[$field_id] : '';
        $value = !is_array($value) ? trim($value) : $value;
        $params = apply_filters('jm_job_render_search_field_params', compact('field', 'field_id', 'value'));
        extract($params);
        $field_label = (!empty($field['plural'])) ? $field['plural'] : $field['label'];
        $label = isset($field['label_translated']) ? $field['label_translated'] : $field_label;
        ?>
        <div class="form-group" data-placeholder="<?php echo sprintf(esc_html__('All %s', 'noo'), $label); ?>">
            <label for="<?php echo 'search-' . esc_attr($field_id) ?>" class="control-label">
                <?php echo($label); ?>
            </label>
            <div class="advance-search-form-control">
                <?php
                noo_render_field($field, $field_id, $value, 'search'); ?>
            </div>
        </div>
        <?php
    }
endif;
if(!function_exists('jm_job_edit_render_field')):
    function jm_job_edit_render_field($field = array(), $disable_multiple_select = false,$job_alert_id=''){
        $field_id = jm_job_custom_fields_name($field['name'], $field);


        $field['required'] = ''; // no need for required fields in search form

        if ($disable_multiple_select) {
            $field['disable_multiple'] = true;
        }
        $value = noo_get_post_meta($job_alert_id,$field_id, '');
        $value = !is_array($value) ? trim($value) : $value;

        $params = apply_filters('jm_job_render_search_field_params', compact('field', 'field_id', 'value'));
        extract($params);

        $label = isset($field['label_translated']) ? $field['label_translated'] : $field['label'];
        ?>
        <div class="form-group" data-placeholder="<?php echo sprintf(esc_html__('All %s', 'noo'), $label); ?>">
            <label for="<?php echo  esc_attr($field_id) ?>" class="control-label col-sm-3">
                <?php echo($label); ?>
            </label>
            <div class="col-sm-9">
                <div class="advance-search-form-control">
                    <?php noo_render_field($field, $field_id, $value, 'search'); ?>
                </div>
            </div>

        </div>
        <?php
    }
endif;

if (!function_exists('jm_job_advanced_search_field')) :
    function jm_job_advanced_search_field($field_val = '', $disable_multiple_select = false)
    {
        if (empty($field_val) || $field_val == 'no') {
            return '';
        }
        $field_arr = explode('|', $field_val);
        $field_id = isset($field_arr[0]) ? $field_arr[0] : '';

        if (empty($field_id)) {
            return '';
        }

        $fields = jm_get_job_search_custom_fields();

        $field_prefix = jm_job_custom_fields_prefix();
        $field_id = str_replace($field_prefix, '', $field_id);

        foreach ($fields as $field) {
            $muliple_select = strpos($field['type'], 'multi') !== false;
            if (sanitize_title($field['name']) == str_replace($field_prefix, '', $field_id)) {

                $tax_fields = jm_get_job_taxonomies();
                if (in_array($field['name'], $tax_fields)) {
                    if ($muliple_select) {
                        $field['type'] = 'job_tax_multiple_select';
                    } else {
                        $field['type'] = 'job_tax_select';
                    }

                }
                jm_job_render_search_field($field, $disable_multiple_select);
                break;
            }
        }

        return '';
    }
endif;
if(!function_exists('jm_job_alert_advanced_field')):
    function jm_job_alert_advanced_field($field_val='',$disable_multiple_select=false,$job_alert_id=''){
        if (empty($field_val) || $field_val == 'no') {
            return '';
        }
        $field_arr = explode('|', $field_val);
        $field_id = isset($field_arr[0]) ? $field_arr[0] : '';

        if (empty($field_id)) {
            return '';
        }

        $fields = jm_get_job_search_custom_fields();

        $field_prefix = jm_job_custom_fields_prefix();
        $field_id = str_replace($field_prefix, '', $field_id);

        foreach ($fields as $field) {
            $muliple_select = strpos($field['type'], 'multi') !== false;
            if (sanitize_title($field['name']) == str_replace($field_prefix, '', $field_id)) {

                $tax_fields = jm_get_job_taxonomies();
                if (in_array($field['name'], $tax_fields)) {
                    if ($muliple_select) {
                        $field['type'] = 'job_tax_multiple_select';
                    } else {
                        $field['type'] = 'job_tax_select';
                    }

                }
                jm_job_edit_render_field($field, $disable_multiple_select,$job_alert_id);
                break;
            }
        }

        return '';
    }
endif;


if (!function_exists('jm_job_save_custom_fields')) :
    function jm_job_save_custom_fields($post_id = 0, $args = array())
    {
        if (empty($post_id)) {
            return;
        }

        // Update custom fields
        $fields = jm_get_job_custom_fields();
        if (!empty($fields)) {
            foreach ($fields as $field) {
                if (isset($field['is_tax']) && $field['is_tax']) {
                    continue;
                }
                $id = jm_job_custom_fields_name($field['name'], $field);
                if (isset($field['type']) && $field['type'] == 'location_picker') {
                    update_post_meta($post_id, $id . '_lat', $args[$id . '_lat']);
                    update_post_meta($post_id, $id . '_lon', $args[$id . '_lon']);
                }
                if (isset($args[$id])) {
                    noo_save_field($post_id, $id, $args[$id], $field);
                }
            }
        }
    }
endif;

if (!function_exists('jm_job_display_custom_fields')) :
    function jm_job_display_custom_fields()
    {
        $fields = jm_get_job_custom_fields();
        if (!empty($fields)) {
            $html = array();

            $user_per = noo_get_user_permission();
            foreach ($fields as $field) {
                // if( isset( $field['is_tax'] ) )
                // 	continue;
                if ($field['name'] == '_closing') // reserve the _closing field
                {
                    continue;
                }
                if ($field['name'] == '_cover_image') // reserve the _closing field
                {
                    continue;
                }
                if ($field['name'] == '_postalcode') // reserve the _postalcode field
                {
                    continue;
                }
                $id = jm_job_custom_fields_name($field['name'], $field);
                if (isset($field['is_tax'])) {
                    $value = jm_job_get_tax_value();
                    $value = implode(',', $value);
                } else {
                    $value = noo_get_post_meta(get_the_ID(), $id, '');
                }

                $icon = isset($field['icon']) ? $field['icon'] : '';
                $icon_class = str_replace("|", " ", $icon);
                $current_user_id = get_current_user_id();
                /* $current_user_permission = 'candidate';*/
                $permission = isset($field['permission']) ? $field['permission'] : '';

                $is_can_view = false;


                if (empty($permission) or 'public' == $permission or Noo_Member::is_employer()) {
                    $is_can_view = true;
                } elseif ($permission == $user_per) {
                    $is_can_view = true;
                }

                if ($is_can_view == false) {
                    continue;
                }
                /*  if (!empty($permission) && $permission == $current_user_permission) {
                      continue;
                  }*/

                if ($field['name'] == '_full_address') {

                    $is_using_company_address = noo_get_post_meta(get_the_ID(), '_use_company_address', '');

                    if ($is_using_company_address) {
                        $company_id = jm_get_job_company(get_the_ID());
                        $value = noo_get_post_meta($company_id, '_full_address', '');
                    } else {
                        $value = noo_get_post_meta(get_the_ID(), '_full_address', '');
                    }
                    if ($value != '') {
                        $html[] = '<li class="job-cf">' . noo_display_field($field, $id, $value, array(
                                'label_tag' => 'strong',
                                'label_class' => '',
                                'value_tag' => 'span'
                            ), false) . '</li>';
                    }

                } else {

                    if ($value != '') {
                        $html[] = '<li class="job-cf">' . noo_display_field($field, $id, $value, array(
                                'label_tag' => 'strong',
                                'label_class' => '',
                                'value_tag' => 'span'
                            ), false) . '</li>';
                    }
                    // add "itemprop="baseSalary"" bug structured data testing tool https://schema.org/JobPosting 
                    // if('_salary' == $field['name']){
                    //     var_dump($value);
                    //     $html[] = '<span class="hidden" itemprop="baseSalary">'.$value.'</span>';
                    // }

                    if('_salary' == $field['name']){
                        $is_schema = noo_get_option('noo_job_schema',false);
                        if($is_schema){
                            $html[] = '<span class="hidden" itemprop="baseSalary" itemscope itemtype="http://schema.org/MonetaryAmount">';
                            $html[] = '<span itemprop="currency">'.esc_html__('$','noo').'</span>';
                            $html[] = '<span itemprop="value" itemscope itemtype="http://schema.org/QuantitativeValue">';
                            $html[] = '<span itemprop="value">'.$value.'</span>';
                            $html[] = '<span itemprop="unitText">'.esc_html__('Month','noo').'</span>';
                            $html[] = '</span></span>';
                        }
                    }
                }


            }

            if (!empty($html) && count($html) > 0) : ?>
                <div class="job-custom-fields">
                    <h3><?php echo esc_html__('More Information', 'noo') ?></h3>
                    <div class="video-gallery-fields">
                        <ul>
                            <?php echo implode("\n", $html); ?>
                        </ul>
                    </div>
                </div>
            <?php endif;
        }
    }

    $field_pos = jm_get_job_custom_fields_option('display_position', 'after');
    add_action('jm_job_detail_content_' . $field_pos, 'jm_job_display_custom_fields', 5);
//	if( $field_pos == 'before' ) {
//	} else {
//		add_action( 'jm_job_detail_content_after', 'jm_job_display_custom_fields' );
//	}
endif;

if (!function_exists('jm_job_advanced_search_job_tax_field')) :
    function jm_job_advanced_search_job_tax_field($field, $field_id, $value, $form_type, $object)
    {
        $custom_multiple_select = strpos($field['type'], 'multi') !== false;

        $allow_multiple_select = isset($field['disable_multiple']) ? !$field['disable_multiple'] : $custom_multiple_select;

        $label = $field['label'];
        $tax = $field['name'];

        $field_name = $allow_multiple_select ? $field_id . '[]' : $field_id;
        $get_tax = get_queried_object();
        $selected = isset($_GET[$field_id]) ? $_GET[$field_id] : '';

        if($get_tax){
            if(isset($get_tax->taxonomy) && isset($field['name']) && $field['name'] ==  $get_tax->taxonomy){
                $selected = $get_tax->slug;
            }
        }
        $hide_empty_tax = jm_get_job_setting( 'hide_empty_tax','');
        $field_args = array(
            'hide_empty' => (int)$hide_empty_tax,
            'echo' => 1,
            'selected' => $selected,
            'hierarchical' => 1,
            'name' => $field_name,
            'id' => 'noo-field-' . $tax,
            'class' => 'form-control noo-select form-control-chosen',
            'depth' => 0,
            'taxonomy' => $tax,
            'value_field' => 'slug',
            'orderby' => 'name',
            'multiple' => $allow_multiple_select,
            'walker' => new Noo_Walker_TaxonomyDropdown(),
        );

        if (!$allow_multiple_select) {
            $field_args['show_option_none'] = sprintf(esc_html__('All %s', 'noo'), $label);
            $field_args['option_none_value'] = '';
        }

        wp_dropdown_categories($field_args);
    }

    add_action('noo_render_field_job_tax_select', 'jm_job_advanced_search_job_tax_field', 10, 5);
    add_action('noo_render_field_job_tax_multiple_select', 'jm_job_advanced_search_job_tax_field', 10, 5);
endif;

add_filter( 'wp_dropdown_cats', 'noo_dropdown_cats_multiple', 10, 2 );

function noo_dropdown_cats_multiple( $output, $r ) {

    if( isset( $r['multiple'] ) && $r['multiple'] ) {

        $output = preg_replace( '/^<select/i', '<select multiple', $output );

//        $output = str_replace( "name='{$r['name']}'", "name='{$r['name']}[]'", $output );
        $selected=(is_array($r['selected']))? $r['selected']: explode(",",$r['selected']);
        $new_array=array_map('trim',$selected);
        if(is_array($new_array)){
            foreach ($new_array as $value){
                $output = str_replace( "value=\"{$value}\"", "value=\"{$value}\" selected", $output );
            }
        }

    }

    return $output;
}