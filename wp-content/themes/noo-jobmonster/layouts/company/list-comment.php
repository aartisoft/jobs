<?php

if (!Noo_Company::review_is_enable()) {
    return;
}

/**
 * list-comment.php
 *
 * @author  : NooTheme
 * @since   : 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
wp_enqueue_style('noo-rating');
wp_enqueue_script('noo-rating');
$company_id = get_the_ID();
$total_review = noo_get_total_review($company_id);
$total_review_point = noo_get_total_point_review($company_id);
?>
<div class="noo-company-review">
    <h3 class="noo-title">
        <span><?php echo sprintf(esc_html__('%s %s'), $total_review, ($total_review > 1 ? esc_html__('Reviews', 'noo') : esc_html__('Review', 'noo'))) ?></span>
    </h3>
    <h3 class="noo-sub-title">
        <span class="label-review"><?php echo esc_html__('Rate This Company', 'noo') ?></span>
        <span class="total-review">
            <?php noo_box_rating($total_review_point, true) ?>
            <span style="font-size: 13px;">
            <?php if ($total_review > 0): ?>
                ( <?php echo sprintf(esc_html__('%s average based on %s %s', 'noo'), $total_review_point, $total_review, ($total_review > 1 ? esc_html__('Reviews', 'noo') : esc_html__('Review', 'noo'))) ?>
                )
            <?php
            else:
                echo esc_html__('( No reviews yet )', 'noo');
            endif;
            ?>
                </span>
        </span>
    </h3>
    <ol class="noo-list-comment">
        <?php
        $comments = get_comments(array(
            'post_id' => get_the_ID(),
            'number' => '15'
        ));

        foreach ($comments as $comment) :

            $review_id = $comment->comment_ID;

            $user_rating1 = get_comment_meta($comment->comment_ID, 'user_rating1', true);
            $user_rating2 = get_comment_meta($comment->comment_ID, 'user_rating2', true);
            $user_rating3 = get_comment_meta($comment->comment_ID, 'user_rating3', true);
            $user_rating4 = get_comment_meta($comment->comment_ID, 'user_rating4', true);
            $total_rating = (float)(($user_rating1 + $user_rating2 + $user_rating3 + $user_rating4) / 4);

            $subject = get_comment_meta($comment->comment_ID, 'subject', true);

            ?>
            <li class="comment-item">
                <div class="comment-head">
                    <?php if (!empty($subject)) : ?>
                        <h4 class="subject">
                            <?php echo esc_html($subject); ?>
                        </h4>

                    <?php endif; ?>
                    <time datetime="<?php echo get_comment_date('d-m-Y', $comment->comment_ID); ?>">
                        <?php echo get_comment_date('M d, Y', $comment->comment_ID); ?>
                    </time>
                </div>

                <div class="comment-info">
                    <span class="er-rate-count"><?php noo_box_rating($total_rating, true) ?></span>
                    <span class="noo-box-reviewed">
                        <span class="reviewed-box-icon"><i class="fa fa-caret-down"></i></span>
                        <div class="noo-review-voted">
                             <div class="line-vote"><span><?php echo esc_html__('Work/Life Balance', 'noo'); ?></span><?php noo_box_rating($user_rating1, true) ?></div>
                             <div class="line-vote"><span><?php echo esc_html__('Comp & Benefits', 'noo'); ?></span><?php noo_box_rating($user_rating2, true) ?></div>
                             <div class="line-vote"><span><?php echo esc_html__('Senior Management', 'noo'); ?></span><?php noo_box_rating($user_rating3, true) ?></div>
                             <div class="line-vote"><span><?php echo esc_html__('Culture & Value', 'noo'); ?></span><?php noo_box_rating($user_rating4, true) ?></div>
                        </div>
                     </span>
                    <span class="user-name"><?php echo esc_html__('By ', 'noo') ?><?php echo esc_html__($comment->comment_author) ?></span>
                </div>
                <p class="comment-content">
                    <?php echo esc_html($comment->comment_content) ?>
                </p>
            </li>
        <?php
        endforeach;
        ?>
    </ol>

    <form id="myform" name="myform" class="noo-form-comment row">
        <?php if (!is_user_logged_in()) : ?>
            <div class="noo-comment-item col-md-6">
                <input type="text" name="user_name" placeholder="<?php echo esc_html__('Name *', 'noo') ?>" value=""/>
            </div>
            <div class="noo-comment-item col-md-6">
                <input type="text" name="user_email" placeholder="<?php echo esc_html__('Your email *', 'noo') ?>"/>
            </div>
        <?php else : ?>
            <?php
            $current_user = wp_get_current_user();
            $name = !empty($current_user) ? $current_user->display_name : '';
            ?>
            <input type="hidden" name="user_name" value="<?php echo esc_attr($current_user->display_name) ?>"/>
            <input type="hidden" name="user_email" value="<?php echo esc_attr($current_user->user_email) ?>"/>
        <?php endif; ?>
        <div class="noo-comment-item col-md-3">
            <input type="text" name="subject" placeholder="<?php echo esc_html__('Subject', 'noo') ?>"/>
            <input class="hide" type="text" name="email_rehot" autocomplete="off"/>
        </div>
        <div class="noo-comment-item col-md-9">
            <div class="row">
                <div class="container-rating col-sm-6">
                    <span class="label-rating"><?php echo esc_html__('Work/Life Balance', 'noo') ?></span>
                    <?php noo_box_rating(5, false, true, 'wlb'); ?>
                </div>
                <div class="container-rating col-sm-6">
                    <span><?php echo esc_html__('Comp & Benefits', 'noo') ?></span>
                    <?php noo_box_rating(5, false, true, 'cb') ?>
                </div>
            </div>
            <div class="row">
                <div class="container-rating col-sm-6">
                    <span><?php echo esc_html__('Senior Management', 'noo') ?></span>
                    <?php noo_box_rating(5, false, true, 'sm') ?>
                </div>
                <div class="container-rating col-sm-6">
                    <span><?php echo esc_html__('Culture & Value', 'noo') ?></span>
                    <?php noo_box_rating(5, false, true, 'cv') ?>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <textarea name="message" placeholder="<?php echo esc_html__('Your message *', 'noo') ?>"></textarea>
        </div>
        <?php do_action('noo_company_review');?>
        <div class="col-md-12" id="button" value="Reset Form">
            <button class="noo-submit btn btn-primary" data-url="<?php echo esc_url(get_permalink()); ?>">
                <?php echo esc_html__('Post a Review', 'noo') ?>
            </button>
            <p class="notice"></p>    
        </div>
        <input type="hidden" name="action" value="noo_company_review"/>
        <input type="hidden" name="company_id" value="<?php the_ID() ?>"/>
    </form>
</div>
