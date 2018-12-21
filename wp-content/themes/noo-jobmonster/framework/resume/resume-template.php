<?php

if( !function_exists('jm_resume_template_loader') ) :
	function jm_resume_template_loader( $template ) {
		global $wp_query;
		if( is_post_type_archive( 'noo_resume' ) ){
			$template       = locate_template( 'archive-noo_resume.php' );
		}
		return $template;
	}

	add_action( 'template_include', 'jm_resume_template_loader' );
endif;

if( !function_exists('jm_resume_loop') ) :
	function jm_resume_loop( $args = '' ) {
		$defaults = array( 
			'query'          => '', 
			'title'          => '',
			'rows'          => '2',
			'column'          => '2',
			'autoplay'          => 'false',
			'slider_speed'          => '800',
			'pagination'     => 'true',
			'resume_style'   => 'list',
			'paginate'       => 'normal',
			'ajax_item'		 => false,
			'excerpt_length' => 30,
			'posts_per_page'  => 3,
			'is_slider'   => false,
			'is_shortcode'   => false,
			'job_category'    => 'all',
			'job_location'    => 'all',
			'orderby'         => 'date',
			'order'           => 'desc',
			'live_search'     => false
		);
		$p = wp_parse_args($args,$defaults);
		extract($p);
		global $wp_query;
		if(!empty($query))
			$wp_query = $query;

		ob_start();

        $arr_type = array( 'list', 'grid' );

        if($is_shortcode){
            $dl_default = $resume_style;
        } else{
            $dl_default = noo_get_option('noo_resume_display_type', 'list');

        }
        $type = isset( $_GET[ 'display' ] ) && in_array( $_GET[ 'display' ], $arr_type ) ? $_GET[ 'display' ] : $dl_default;
        
        $display_style = 'layouts/resume/loop/' . esc_attr( $type ) .'.php';
		include(locate_template( $display_style ));
		echo ob_get_clean();

		wp_reset_postdata();

	}
endif;

if( !function_exists('jm_resume_detail') ) :
	function jm_resume_detail( $query = null, $hide_profile = false ) {
		if(empty($query)){
			global $wp_query;
			$query = $wp_query;
		}
		$layout = noo_get_option( 'noo_resumes_detail_layout', 'style-1' );
		$layout = !empty( $_GET['layout'] ) ? sanitize_text_field( $_GET['layout'] ) : $layout;
		while ($query->have_posts()): $query->the_post(); 

			global $post;
			$resume_id			= $post->ID;
			ob_start();
			if( jm_can_view_single_resume( $resume_id ) ) {
				if ( 'style-1' == $layout ) {
					include( locate_template( "layouts/resume/single/detail.php" ) );
				} elseif ( 'style-2' == $layout ) {
					include( locate_template( "layouts/resume/single/detail-style-2.php" ) );
				}
			} else {
				// include(locate_template("layouts/resume/cannot-view-resume.php"));
				// 
				// Fix Unregister Employer Who uses company ID to view a resume
                $company_id =(isset($_COOKIE['jm_ga_company_id'])) ? $_COOKIE[ 'jm_ga_company_ids' ] : '';
                $paged = (isset($paged)) ? $paged : '';

                $job_list = Noo_Company::get_company_jobs( $company_id );
 
                $status_filter = isset( $_REQUEST[ 'status' ] ) ? esc_attr( $_REQUEST[ 'status' ] ) : '';
                $all_statuses  = Noo_Application::get_application_status();
                unset( $all_statuses[ 'inactive' ] );
 
                $app_check_args = array(
                    'post_type'       => 'noo_application',
                    'paged'           => $paged,
                    'post_parent__in' => array_merge( $job_list, array( 0 ) ),
                    // make sure return zero application if there's no job.
                    'post_status'     => ! empty( $status_filter ) ? array( $status_filter ) : array(
                        'publish',
                        'pending',
                        'rejected',
                    ),
                );
 
                if ( ! empty( $job_filter ) && in_array( $job_filter, $job_ids ) ) {
                    $app_check_args[ 'post_parent__in' ] = array( $job_filter );
                }
 
                $check_applications = new WP_Query( $app_check_args );
               
                $check_application_status = false; //by Default false unless a application is applied
                foreach($check_applications->posts as $check_post){
                    if(Noo_application::has_applied($post->post_author,$check_post->post_parent)){
                        $check_application_status =  true; //Allows access to resume if even a single application is applied by that candidate
                    }
                }
               
                if ( ! jm_ga_check_logged() ):
                	include(locate_template("layouts/resume/cannot-view-resume.php"));
                elseif($check_application_status):
                	include(locate_template("layouts/resume/single/detail.php"));
                else:
                	include(locate_template("layouts/resume/cannot-view-resume.php"));
                endif;
				
			}
			echo ob_get_clean();
		
		endwhile;
		wp_reset_query();
	}
endif;


if ( ! function_exists( 'noo_resume_list_display_type' ) ) :

	function noo_resume_list_display_type() {

		$arr_type = array( 'list', 'grid' );

		$default = noo_get_option('noo_resume_display_type', 'list');

		$type = isset( $_GET[ 'display' ] ) && in_array( $_GET[ 'display' ], $arr_type ) ? $_GET[ 'display' ] : $default;

		return $type;
	}

endif;

if ( ! function_exists( 'noo_resume_get_location' ) ) :

    function noo_resume_get_location( $resume_id = '' ) {

        if ( empty( $resume_id ) ) {
        	return false;
        }

        $location = get_post_meta( $resume_id, '_job_location', true );
        if ( !empty( $location ) && is_array( $location ) ) {
	        $location = implode(', ', $location);
        }

        return $location;

    }
endif;
if( !function_exists('noo_can_post_resume_review')):
    function noo_can_post_resume_review($resume_id = null){
        if( empty( $resume_id ) )
            return false;
        // Resume's author can view his/her resume
        $candidate_id = get_post_field( 'post_author', $resume_id );
        if( $candidate_id == get_current_user_id() ) {
            return true;
        }

        $can_view_resume = false;
        // Administrator can post  review all resumes
        if( 'administrator' == Noo_Member::get_user_role(get_current_user_id()) ) {
            $can_view_resume = true;
        }elseif( isset($_GET['application_id'] ) && !empty($_GET['application_id']) ) {
            // Employers can view resumes from their applications

            $job_id = get_post_field( 'post_parent', $_GET['application_id'] );

            $employer_id = get_post_field( 'post_author', $job_id );
            if( $employer_id == get_current_user_id() ) {
                $attachement_resume_id = noo_get_post_meta( $_GET['application_id'], '_resume', '' );
                $can_view_resume = $resume_id == $attachement_resume_id;
            }
        }
        return $can_view_resume;
    }
endif;