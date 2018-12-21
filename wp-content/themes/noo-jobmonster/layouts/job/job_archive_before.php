<?php

global $wp_query;

$total = $wp_query->found_posts;
$paged = get_query_var('paged', 1);
$current = !empty($paged) ? $paged : 1;

$per_page = $wp_query->query_vars['posts_per_page'];

$display_type = noo_job_list_display_type();


$params = $_REQUEST;

unset($params['action']);
unset($params['live-search-nonce']);
unset($params['_wp_http_referer']);
unset($params['_wpnonce']);

$main_url = get_post_type_archive_link('noo_job');
//if (is_tax(jm_get_job_taxonomies())) {
//    $tax_id = get_queried_object()->term_id;
//    $main_url = get_term_link($tax_id);
//}

$url = $main_url.'feed/job_feed';
$current_url = add_query_arg($params, $main_url);
$feed_url = add_query_arg($params,$url);


?>
<div class="noo-job-archive-before">

    <div class="pull-left noo-job-list-tools">
        <div class="noo-display-type">
            <a class="noo-type-btn <?php echo($display_type == 'list' ? 'active' : ''); ?>"
               href="<?php echo add_query_arg('display', 'list', $current_url); ?>">
                <i class="fa fa-list"></i>
            </a>
            <a class="noo-type-btn <?php echo($display_type == 'grid' ? 'active' : ''); ?>"
               href="<?php echo add_query_arg('display', 'grid', $current_url); ?>">
                <i class="fa fa-th-large"></i>
            </a>
            <a class="noo-type-btn"
               href="<?php echo $feed_url; ?>">
                <i class="fa fa-rss"></i>
            </a>
        </div>
        <div class="noo-btn-job-alert-form">
            <i class="fa fa-bell"></i><span><?php echo esc_html__('Email Me Jobs Like These', 'noo'); ?></span>
        </div>

        <?php noo_get_layout('forms/job_alert_form_popup'); ?>
    </div>

    <div class="pull-right noo-job-list-count">
		<span>
		<?php
        $first = ($per_page * $current) - $per_page + 1;
        $last = min($total, $per_page * $current);

        printf(_nx('Showing the single result', 'Showing %1$d&ndash;%2$d of %3$d jobs', $total, 'with first and last result', 'noo'), $first, $last, $total);
        ?>
		</span>
    </div>
</div>
