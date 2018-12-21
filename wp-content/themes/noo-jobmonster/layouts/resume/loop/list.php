<?php
// List;
$google_ads = noo_get_option('noo_resume_google_ads');
$google_position = noo_get_option('noo_resume_google_ads_position', 'top');
wp_enqueue_style('noo-rating');
wp_enqueue_script('noo-rating');

global $wp_query;
$no_content = '';
$total = $wp_query->found_posts;
$paged = get_query_var('paged', 1);
$current = !empty($paged) ? $paged : 1;
$per_page = $wp_query->query_vars['posts_per_page'];

$display_type = 'list';


$settings_fields = get_theme_mod('noo_resume_list_fields', 'title,_job_location,_job_category');
$settings_fields = !is_array($settings_fields) ? explode(',', $settings_fields) : $settings_fields;
$display_fields = array();
foreach ($settings_fields as $index => $resume_field) {
    if ($resume_field == 'title') {
        $field = array('name' => 'title', 'label' => __('Resume Title', 'noo'));
    } else {
        $field = jm_get_resume_field($resume_field);
    }
    if (!empty($field)) {
        $display_fields[] = $field;
    }
}


$params = $_REQUEST;

unset($params['action']);
unset($params['live-search-nonce']);
unset($params['_wp_http_referer']);
unset($params['_wpnonce']);
$main_url = get_post_type_archive_link('noo_resume');
$current_url = add_query_arg($params, $main_url);
$feed = $main_url . 'feed/resume_feed';
$feed_url = add_query_arg($params, $feed);
$id_resume = uniqid('resume-id-');

?>

<?php
if (empty($_POST['action'])) {
    echo '<div class="resumes posts-loop resume-' . $paginate . '" data-paginate="' . $paginate . '">';
}
?>

<?php if (!$is_shortcode): ?>
    <div class="noo-resume-archive-before">
        <div class="pull-left noo-resume-list-tools">
            <div class="noo-display-type">
                <a class="noo-type-btn active"
                   href="<?php echo add_query_arg('display', 'list', $current_url); ?>">
                    <i class="fa fa-list"></i>
                </a>
                <a class="noo-type-btn"
                   href="<?php echo add_query_arg('display', 'grid', $current_url); ?>">
                    <i class="fa fa-th-large"></i>
                </a>
                <a class="noo-type-btn"
                   href="<?php echo $feed_url; ?>">
                    <i class="fa fa-rss"></i>
                </a>
            </div>
        </div>

        <div class="pull-right noo-resume-list-count">
            		<span><?php
                        $first = ($per_page * $current) - $per_page + 1;
                        $last = min($total, $per_page * $current);

                        printf(_nx('Showing the single result', 'Showing %1$d&ndash;%2$d of %3$d resumes', $total, 'with first and last result', 'noo'), $first, $last, $total);
                        ?></span>
        </div>
    </div>
<?php endif; ?>
<?php if (!empty($google_ads) && $google_position == 'top') {
    echo $google_ads;
} ?>
    <div class="noo-resumes-slider" id="<?php echo($id_resume); ?>">

        <?php if ($wp_query->have_posts()): ?>

            <ul class="row swiper-wrapper noo-resume-<?php echo esc_attr($display_type) ?>">

                <?php while ($wp_query->have_posts()): $wp_query->the_post();
                    global $post; ?>
                    <?php
                    $total_review = noo_get_total_review($post->ID);
                    $total_review_point = noo_get_total_point_review_resume($post->ID);
                    $candidate_avatar = '';
                    $candidate_name = '';
                    if (!empty($post->post_author)) :
                        $candidate_avatar = noo_get_avatar($post->post_author, 40);
                        $candidate = get_user_by('id', $post->post_author);
                        $candidate_name = !empty($candidate) ? $candidate->display_name : '';
                        $candidate_link = esc_url(apply_filters('noo_resume_candidate_link', get_the_permalink(), $post->ID, $post->post_author));
                        ?>
                        <li class="noo-resume-item swiper-slide col-md-12 <?php echo ('yes' == noo_get_post_meta($post->ID, '_featured', '')) ? 'featured-resume' : '' ?>">
                            <a class="resume-details-link" href="<?php the_permalink(); ?>"></a>
                            <div class="noo-resume-info">
                                <div class="item-featured">
                                    <a href="<?php echo $candidate_link; ?>">
                                        <?php echo $candidate_avatar; ?>
                                    </a>
                                </div>

                                <div class="item-content">
                                    <h5 class="item-author">
                                        <a href="<?php echo $candidate_link; ?>"
                                           title="<?php echo esc_html($candidate_name); ?>">
                                            <?php echo esc_html($candidate_name); ?>
                                        </a>
                                    </h5>
                                    <h4 class="item-title">
                                        <a href="<?php the_permalink() ?>" title="<?php echo get_the_title(); ?>">
                                            <?php echo get_the_title(); ?>
                                        </a>
                                    </h4>
                                    <?php if(jm_get_resume_setting('post_review_resume','1')): ?>
                                    <h5 class="noo-sub-title">
                                        <span class="total-review">
                                             <?php noo_box_rating($total_review_point, true) ?>
                                            <span style="font-size: 13px;">
                                              ( <?php echo sprintf(esc_html__('%s', 'noo'), $total_review) ?>
                                                )
                                            </span>
                                        </span>
                                    </h5>
                                    <?php endif; ?>
                                    <div class="item-meta">
                                        <?php foreach ($display_fields as $index => $field) : ?>
                                            <?php if (!isset($field['name']) || empty($field['name'])) {
                                                continue;
                                            } ?>
                                            <?php if ($field['name'] !== 'title') : ?>
                                                <?php $value = jm_get_resume_field_value($post->ID, $field); ?>
                                                <?php if (empty($value)) continue; ?>
                                                <span class="<?php echo esc_attr($field['name']) ?>">
                                                        <?php

                                                        if (!empty($value)) {
                                                            $html = array();
                                                            $value = noo_convert_custom_field_value($field, $value);
                                                            //                                                    if ( $index <= 1 || count( $display_fields ) <= 1 )
                                                            if (count($display_fields) <= 1) {
                                                                if (is_array($value)) {
                                                                    $value = implode(', ', $value);
                                                                }
                                                                $html[] = $value;
                                                            } else {
                                                                $icon = isset($field['icon']) ? $field['icon'] : '';
                                                                $icon_class = str_replace("|", " ", $icon);

                                                                $label = isset($field['label_translated']) ? $field['label_translated'] : $field['label'];
                                                                $html[] = '<span class="resume-' . $field['name'] . '" style="display: inline-block;">';
                                                                $html[] = '<i class="' . $icon_class . '">';
                                                                $html[] = '</i>';
                                                                $html[] = '<em>';
                                                                $html[] = is_array($value) ? implode(', ', $value) : $value;
                                                                $html[] = '</em></span>';
                                                            }
                                                            echo implode("\n", $html);
                                                        }
                                                        ?>
                                                    </span>
                                            <?php endif; ?>
                                        <?php endforeach;
                                        reset($display_fields); ?>
                                    </div>
                                </div>
                                <?php $can_shortlist_candidate = noo_can_shortlist_candidate() ?>
                                <?php if ($can_shortlist_candidate): ?>
                                    <?php if ('list' == $display_type) : ?>
                                        <div class="show-view-more">
                                            <a class="btn btn-primary noo-shortlist" href="#"
                                               data-resume-id="<?php echo esc_attr($post->ID) ?>"
                                               data-user-id="<?php echo get_current_user_id() ?>" data-type="text">
                                                <?php echo noo_shortlist_status($post->ID, get_current_user_id()) ?>
                                            </a>
                                            <div class="time-post">
                                                <?php echo sprintf(__("%s ago", 'noo'), human_time_diff(get_the_time('U'), current_time('timestamp'))); ?>
                                            </div>
                                        </div>
                                    <?php else : ?>
                                        <a class="noo-shortlist" href="#"
                                           data-resume-id="<?php echo esc_attr($post->ID) ?>"
                                           data-user-id="<?php echo get_current_user_id() ?>" data-type="icon">
                                            <?php echo noo_shortlist_icon($post->ID, get_current_user_id()) ?>
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endif; ?>
                <?php endwhile; ?>

            </ul>

            <?php if ($is_slider == 'true' && $show_pagination): ?>
                <div class="resume-pagination resume-slider-pagination text-center">
                    <a href="#" class="swiper-prev">
                        <i class="fa fa-chevron-left"></i>
                    </a>

                    <a href="#" class="swiper-next">
                        <i class="fa fa-chevron-right"></i>
                    </a>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="resume posts-loop ">
                <?php
                if ($no_content == 'text' || empty($no_content)) {
                    noo_get_layout('no-content');
                } elseif ($no_content != 'none') {
                    echo '<h3>' . $no_content . '</h3>';
                }
                ?>
            </div>
        <?php endif; ?>

    </div>
<?php if (!empty($google_ads) && $google_position == 'bottom') {
    echo $google_ads;
} ?>
<?php if ($is_slider == 'true'): ?>
    <?php
    wp_enqueue_script('noo-swiper');
    wp_enqueue_style('noo-swiper');
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function ($) {
            var mySwiper = new Swiper("#<?php echo esc_attr($id_resume) ?>", {
                speed: <?php echo absint($slider_speed) ?>,
                spaceBetween: 15,
                slidesPerView: <?php echo absint($column) ?>,
                slidesPerColumn: <?php echo absint($rows) ?>,
                autoplay: <?php echo esc_attr($autoplay) ?>,
                pagination: <?php echo esc_attr($pagination) ?>,
                preloadImages: false,
                lazy: true,
                navigation: {
                    nextEl: '.swiper-next',
                    prevEl: '.swiper-prev',
                },
            });

            if (mySwiper) {
                mySwiper.update();
                $('.vc_tta-tab').click(function () {
                    mySwiper.update();
                });
            }

        });
    </script>
<?php else: ?>

    <?php if ($paginate == 'resume_nextajax') : ?>

        <?php if (1 < $wp_query->max_num_pages) :

            $paged = isset($_POST['page']) ? absint($_POST['page']) : 1; ?>
            <div class="pagination list-center"
                 data-job-category="<?php echo esc_attr($job_category); ?>"
                 data-job-location="<?php echo esc_attr($job_location); ?>"
                 data-orderby="<?php echo esc_attr($orderby); ?>"
                 data-order="<?php echo esc_attr($order); ?>"
                 data-posts-per-page="<?php echo absint($posts_per_page) ?>"
                 data-current-page="<?php echo absint($paged) ?>"
                 data-style="list"
                 data-max-page="<?php echo absint($wp_query->max_num_pages) ?>">
                <a href="#" class="prev page-numbers disabled">
                    <i class="fa fa-long-arrow-left"></i>
                </a>

                <a href="#" class="next page-numbers">
                    <i class="fa fa-long-arrow-right"></i>
                </a>
            </div>
        <?php endif; ?>

    <?php else : ($live_search ? noo_pagination('', $wp_query, $live_search) : noo_pagination('', $wp_query)); endif; ?>

<?php endif; ?>

<?php
if (empty($_POST['action'])) {
    echo '</div>';
}
?>