<?php
$job_alert_id = isset($_GET['job_alert_id']) ?absint($_GET['job_alert_id']) : 0;
$job_alert = $job_alert_id ? get_post($job_alert_id) : '';
?>
<?php do_action('noo_post_job_alert_before'); ?>
<div class="job_alert-form">
	<div class="job_alert-form row">
		<div class="form-group required-field">
			<label for="title" class="col-sm-3 control-label"><?php _e('Alert Name','noo')?></label>
			<div class="col-sm-9">
		    	<input type="text" value="<?php echo ($job_alert ? $job_alert->post_title : '')?>" class="form-control jform-validate" id="title"  name="title" autofocus required placeholder="<?php _e('Your alert name', 'noo'); ?>">
		    </div>
		</div>
		<div class="form-group">
			<label for="keywords" class="col-sm-3 control-label"><?php _e('Keywords','noo')?></label>
			<div class="col-sm-9">
		    	<input type="text" value="<?php echo ($job_alert ? noo_get_post_meta($job_alert_id,'_keywords') : '')?>" class="form-control" id="keywords"  name="keywords" placeholder="<?php _e('Enter keywords to match jobs', 'noo'); ?>">
		    </div>
		</div>
        <div class="form-group">
            <label for="frequency" class="col-sm-3 control-label"><?php _e('Email Frequency','noo')?></label>
            <div class="col-sm-9">
                <?php
                $frequency = $job_alert ? noo_get_post_meta($job_alert_id,'_frequency','weekly') : 'weekly';
                $frequency_arr = Noo_Job_Alert::get_frequency();
                ?>
                <select class="form-control" name="frequency" id="frequency" >
                    <?php foreach ($frequency_arr as $key=>$label ):?>
                        <option value="<?php echo esc_attr($key)?>" <?php selected( $frequency, $key ); ?>><?php echo $label ?></option>
                    <?php endforeach;?>
                </select>
            </div>
        </div>
        <?php for($po=1;$po<=8;$po++):
                $field= jm_get_job_alert_setting('job_alert'.$po.'',5);?>
                <?php if($field=='job_location'): ?>
                     <div class="form-group">
                <label for="job_location" class="col-sm-3 control-label"><?php _e('Job Location','noo')?></label>
                <div class="col-sm-9 <?php if ( is_rtl() ) echo ' chosen-rtl'; ?>">
                    <select id="job_location" name="job_location[]" multiple class="form-control form-control-chosen">
                        <option value=""></option>
                        <?php
                        $locations = get_terms('job_location',array('hide_empty'=>0));
                        if($locations):
                            $value = $job_alert ? noo_get_post_meta($job_alert_id,'_job_location') : '';
                            $value = noo_json_decode($value);
                            ?>
                            <?php
                            foreach ($locations as $location) : ?>
                                <option value="<?php echo esc_attr($location->term_id)?>" <?php if(!empty($value) && in_array($location->term_id, $value)):?> selected="selected"<?php endif;?>><?php echo esc_html($location->name)?></option>
                            <?php endforeach;?>
                        <?php endif; ?>
                    </select>
                </div>
            </div>
                <?php elseif($field=='job_category'): ?>
                    <div class="form-group">
                <label for="job_category" class="col-sm-3 control-label"><?php _e('Job Category','noo')?></label>
                <div class="col-sm-9 <?php if ( is_rtl() ) echo ' chosen-rtl'; ?>">
                    <select class="form-control form-control-chosen ignore-valid" name="job_category[]" id="job_category" multiple >
                        <option value=""></option>
                        <?php
                        $categories = get_terms('job_category',array('hide_empty'=>0));
                        if($categories):
                            $value = $job_alert ? noo_get_post_meta($job_alert_id,'_job_category', '') : '';
                            $value = noo_json_decode($value);
                            ?>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?php echo esc_attr($category->term_id)?>" <?php if(!empty($value) && in_array($category->term_id, $value)):?> selected="selected"<?php endif;?>><?php echo esc_html($category->name)?></option>
                        <?php endforeach;?>
                        <?php endif;?>
                    </select>
                </div>
            </div>

                <?php elseif($field=='job_type'): ?>
                     <div class="form-group">
                <label for="job_type" class="col-sm-3 control-label"><?php _e('Job Type','noo')?></label>
                <div class="col-sm-9 <?php if ( is_rtl() ) echo ' chosen-rtl'; ?>">
                    <select class="form-control form-control-chosen ignore-valid" name="job_type[]" id="job_type" multiple >
                        <option value=""></option>
                        <?php
                        $types = get_terms('job_type',array('hide_empty'=>0));
                        if($types):
                            $value = $job_alert ? noo_get_post_meta($job_alert_id,'_job_type', '') : '';
                            $value = noo_json_decode($value);
                            ?>
                            <?php foreach ($types as $type): ?>
                            <option value="<?php echo esc_attr($type->term_id)?>" <?php if(!empty($type) && in_array($type->term_id, $value)):?> selected="selected"<?php endif;?>><?php echo esc_html($type->name)?></option>
                        <?php endforeach;?>
                        <?php endif;?>
                    </select>
                </div>
            </div>

                <?php elseif($field=='job_tag'): ?>
                     <div class="form-group">
                <label for="job_tag" class="col-sm-3 control-label"><?php _e('Job Tag','noo')?></label>
                <div class="col-sm-9 <?php if ( is_rtl() ) echo ' chosen-rtl'; ?>">
                    <select class="form-control form-control-chosen ignore-valid" name="job_tag[]" id="job_tag" multiple >
                        <option value=""></option>
                        <?php
                        $tags = get_terms('job_tag',array('hide_empty'=>0));
                        if($tags):
                            $value = $job_alert ? noo_get_post_meta($job_alert_id,'_job_tag', '') : '';
                            $value = noo_json_decode($value);
                            ?>
                            <?php foreach ($tags as $tag): ?>
                            <option value="<?php echo esc_attr($tag->term_id)?>" <?php if(!empty($tag) && in_array($tag->term_id, $value)):?> selected="selected"<?php endif;?>><?php echo esc_html($tag->name)?></option>
                        <?php endforeach;?>
                        <?php endif;?>
                    </select>
                </div>
            </div>
                <?php else:
                     jm_job_alert_advanced_field($field,'',$job_alert_id);
            ?>
                <?php endif; ?>
        <?php endfor; ?>




	</div>
</div>
<?php do_action('noo_post_job_alert_after'); ?>