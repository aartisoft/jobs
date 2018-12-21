<?php
/**
 * Created by PhpStorm.
 * Date: 11/24/2018
 * Time: 10:00 AM
 */

function noo_create_print_invoice()
{
    $_POST = wp_kses_post_deep($_POST);
    global $post;
    $post->ID = (!empty($_POST['invoice'])) ? $_POST['invoice'] : '';
    ?>
    <script src="http://code.jquery.com/jquery-latest.min.js" type="text/javascript"></script>
    <link rel="stylesheet" href="https://unpkg.com/@icon/dashicons/dashicons.css" type="text/css"
          media="all" >
    <link rel="stylesheet" id="noo-indeed-css" href="<?php echo NOO_ASSETS_URI . '/css/noo.css' ?>" type="text/css"
          media="all">
    <link rel="stylesheet" id="noo-awesome-css"
          href="<?php echo NOO_FRAMEWORK_URI . '/vendor/fontawesome/css/font-awesome.min.css' ?>" type="text/css"
          media="all">
    <link rel="stylesheet" id="noo-boostrap-css"
          href="<?php echo NOO_FRAMEWORK_URI . '/vendor/bootstrap-multiselect/bootstrap-multiselect.css', null, null ?>"
          type="text/css" media="all">
    <link rel="stylesheet" id="noo-indeed-css"
          href="<?php echo NOO_FRAMEWORK_URI . '/vendor/genericons/genericons.css' ?>" type="text/css" media="all">

    <script src="<?php echo NOO_ASSETS_URI . '/vendor/rating/jquery.raty.js' ?>"></script>
    <script src="<?php echo NOO_ASSETS_URI . 'js/noo.js' ?>"></script>
    <link rel="stylesheet" id="noo-indeed-css" href="<?php echo  NOO_ASSETS_URI . '/vendor/rating/jquery.raty.css' ?>" type="text/css"
          media="all">
    <script>


        jQuery(window).load(function () {
            window.print();
        });
    </script>
    <style>
        @media print {
            .progress {
                position: relative;
            }

            .progress:before {
                display: block;
                content: '';
                position: absolute;
                top: 0;
                right: 0;
                bottom: 0;
                left: 0;
                z-index: 0;
                border-bottom: 1em solid #eeeeee;
                border-radius: 10px;
            }

            .progress-bar {
                border-radius: 10px;
                position: absolute;
                top: 0;
                bottom: 0;
                left: 0;
                z-index: 1;
                border-bottom: 1em solid #f5d006;;
            }

            .progress-bar-success {
                border-bottom-color: #67c600;
            }

            .progress-bar-info {
                border-bottom-color: #5bc0de;
            }

            .progress-bar-warning {
                border-bottom-color: #f0a839;
            }

            .progress-bar-danger {
                border-bottom-color: #ee2f31;
            }
        }
    </style>

    <?php
    $layout = noo_get_option('noo_resumes_detail_layout', 'style-1');
    $layout = !empty($_POST['layout']) ? sanitize_text_field($_POST['layout']) : $layout;
    if ('style-1' == $layout) {
        noo_get_layout("resume/single/detail");
    } elseif ('style-2' == $layout) {
        noo_get_layout('candidate/resume_candidate_profile');
        noo_get_layout("resume/single/detail-style-2");
    }
}

add_action('wp_ajax_noo_create_print_invoice', 'noo_create_print_invoice');
add_action('wp_ajax_nopriv_noo_create_print_invoice', 'noo_create_print_invoice');
