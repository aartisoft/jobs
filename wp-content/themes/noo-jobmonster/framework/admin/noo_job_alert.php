<?php
if ( ! class_exists( 'Noo_Job_Alert' ) ):
	class Noo_Job_Alert {

		public static function set_alert_schedule( $job_alert_id = null, $frequency = '' ) {
			if ( ! self::enable_job_alert() ) {
				return;
			}

			if ( empty( $job_alert_id ) ) {
				return;
			}

			wp_clear_scheduled_hook( 'noo-job-alert-notify', array( $job_alert_id ) );

			$alert = get_post( $job_alert_id );

			if ( ! $alert || $alert->post_status !== 'publish' || $alert->post_type !== 'noo_job_alert' ) {
				return;
			}

			// Update the schedule time
			update_post_meta( $alert->ID, '_start_schedule_time', time() );

			// Reschedule next alert
			$frequency = empty( $frequency ) ? noo_get_post_meta( $alert->ID, '_frequency', 'weekly' ) : $frequency;
			switch ( $frequency ) {
				case 'daily' :
					$next = strtotime( '+1 day' );
					break;
				case 'hourly' :
					$next = strtotime( '+1 hour' );
					break;
				case 'weekly' :
					$next = strtotime( '+1 week' );
					break;
				case 'fortnight' :
					$next = strtotime( '+1 fortnight' );
					break;
				case 'monthly' :
					$next = strtotime( '+1 month' );
					break;
				default:
					$next = strtotime( '+1 week' );
			}

			// Create cron
			return wp_schedule_single_event( $next, 'noo-job-alert-notify', array( $alert->ID ) );
		}

		public static function enable_job_alert() {
			return self::get_setting( 'enable_job_alert', 'yes' ) == 'yes';
		}

		public static function get_setting( $id = null, $default = null ) {
			global $noo_job_alert_setting;
			if ( ! isset( $noo_job_alert_setting ) || empty( $noo_job_alert_setting ) ) {
				$noo_job_alert_setting = get_option( 'noo_job_alert' );
			}
			if ( isset( $noo_job_alert_setting[ $id ] ) ) {
				return $noo_job_alert_setting[ $id ];
			}

			return $default;
		}

		public function __construct() {
			if ( self::enable_job_alert() ) {
				add_action( 'init', array( $this, 'register_post_type' ), 20 );
				add_action( 'noo-job-alert-notify', array( $this, 'notify' ) );
			}

			if ( is_admin() ) {
				add_action( 'admin_init', array( &$this, 'admin_init' ) );

				add_filter( 'noo_job_settings_tabs_array', array( &$this, 'add_seting_job_alert_tab' ), 20 );
				add_action( 'noo_job_setting_job_alert', array( &$this, 'setting_page' ) );
			}

			add_action( 'wp_ajax_noo_job_alert_popup', array( $this, 'new_job_alert_popup' ) );
			add_action( 'wp_ajax_nopriv_noo_job_alert_popup', array( $this, 'new_job_alert_popup' ) );
		}

		public function register_post_type() {
			register_post_type( 'noo_job_alert', array(
				'labels'              => array(
					'name'          => __( 'Job Alerts', 'noo' ),
					'singular_name' => __( 'Job Alert', 'noo' ),
					// 'add_new'            => __( 'Add New Alert', 'noo' ),
					// 'add_new_item'       => __( 'Add Alert', 'noo' ),
					// 'edit'               => __( 'Edit', 'noo' ),
					// 'edit_item'          => __( 'Edit Alert', 'noo' ),
					// 'new_item'           => __( 'New Alert', 'noo' ),
					// 'view'               => __( 'View', 'noo' ),
					// 'view_item'          => __( 'View Alert', 'noo' ),
					// 'search_items'       => __( 'Search Alert', 'noo' ),
					// 'not_found'          => __( 'No Alerts found', 'noo' ),
					// 'not_found_in_trash' => __( 'No Alerts found in Trash', 'noo' ),
					// 'parent'             => __( 'Parent Alert', 'noo' ),
					'all_items'     => __( 'All Alerts', 'noo' ),
				),
				'public'              => false,
				'show_ui'             => false,
				'capability_type'     => 'post',
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'hierarchical'        => false,
				'rewrite'             => false,
				'query_var'           => false,
				'supports'            => false,
				'has_archive'         => false,
				'show_in_nav_menus'   => false,
			) );
		}

		public function admin_init() {
			register_setting( 'noo_job_alert', 'noo_job_alert' );
		}

		public function add_seting_job_alert_tab( $tabs ) {
			$tabs['job_alert'] = __( 'Job Alert', 'noo' );

			return $tabs;
		}

		public function setting_page() {
			if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) {
				flush_rewrite_rules();
			}


			$default                =array(
			        'r_pos1'        =>'',
                    'r_pos2'        =>'',
                    'r_pos3'        =>'',
                    'r_pos4'        =>'',
                    'r_pos5'        =>'',
                    'r_pos6'        =>'',
                    'r_pos7'        =>'',
                    'r_pos8'        =>'',
            );
			$custom_fields =jm_get_job_search_custom_fields();
			$search_fields =array(
			        'no'    => __('None','noo'),
            );
			foreach ($custom_fields as $k => $field){
			    if(isset($field['is_default'])){
			        $label = isset($field['label'])? $field['label'] : $k ;
			        $id    =   $field['name'];
			        $search_fields[$id] = $label;
                }else{
                    $label                = __( 'Custom Field: ', 'noo' ) . ( isset( $field['label_translated'] ) ? $field['label_translated'] : ( isset( $field['label'] ) ? $field['label'] : $k ) );
                    $id                   = jm_job_custom_fields_name( $field['name'], $field );
                    $search_fields[ $id ] = $label;
                }
            }

			?>
			<?php settings_fields( 'noo_job_alert' ); ?>
            <h3><?php echo __( 'Job Alert Options', 'noo' ) ?></h3>
            <table class="form-table" cellspacing="0">
                <tbody>
                <tr>
                    <th>
						<?php esc_html_e( 'Enable Job Alert', 'noo' ) ?>
                    </th>
                    <td>
						<?php
						$enable_job_alert = self::get_setting( 'enable_job_alert', 'yes' );
						?>
                        <input type="hidden" name="noo_job_alert[enable_job_alert]" value="no">
                        <input type="checkbox" name="noo_job_alert[enable_job_alert]"
                               value="yes" <?php checked( $enable_job_alert, 'yes' ) ?>>
                    </td>
                </tr>
                <tr>
                    <th>
						<?php esc_html_e( 'Max Jobs for each Email', 'noo' ) ?>
                    </th>
                    <td>
						<?php
						$max_job_count_email = self::get_setting( 'max_job_count_email', 5 );
						?>
                        <input type="text" name="noo_job_alert[max_job_count_email]"
                               value="<?php echo( $max_job_count_email ? $max_job_count_email : '5' ) ?>">
                        <p>
                            <small><?php echo __( 'The maximum number of jobs included in each email. It helps make sure the email has reasonable length. If there are more jobs, a read more link will be added to the end of email.', 'noo' ); ?></small>
                        </p>
                    </td>
                </tr>
                <?php for($po =1; $po <=8; $po++): ?>
                    <?php  $r_pos= jm_get_job_alert_setting('job_alert'.$po.'',5); ?>
                <tr>
                    <th>
                        <?php _e( 'Job Alert Query Position #' . $po, 'noo' ); ?>
                    </th>
                    <td>
                        <select class=" job-alert-position" name="<?php echo 'noo_job_alert[job_alert'. $po .']'?>">
                            <?php foreach ( $search_fields as $key => $value ) {
                                $selected = ( $r_pos == $key ) || strpos( $r_pos, $key . '|' ) !== false;
                                echo "<option value='{$key}'" . ( $selected ? ' selected' : '' ) . ">{$value}</option>";
                            } ?>
                        </select>
                    </td>
                </tr>

                <?php endfor; ?>
				<?php do_action( 'noo_setting_job_alert_fields' ); ?>
                </tbody>
            </table>
			<?php
		}

		public function notify( $alert_id ) {
			$alert = get_post( $alert_id );

			if ( ! $alert || $alert->post_status !== 'publish' || $alert->post_type !== 'noo_job_alert' ) {
				return;
			}

			$user = get_user_by( 'id', $alert->post_author );

			$jobs = $this->_get_alert_jobs( $alert );
			if ( $jobs && $jobs->found_posts > 0 ) {
				$site_name = get_bloginfo( 'name' );
				$email   = $this->_format_email( $alert, $user, $jobs );
				$subject = sprintf( __( '%d+ New Jobs - Job Alert from %s', 'noo' ), $jobs->found_posts, $site_name );
				$subject = apply_filters( 'noo_job_alert_email_subject', $subject, $alert, $jobs );

                $to =  $user->user_email;

				if ( $email ) {
				    if(empty($to)){
				        $to = get_post_meta($alert_id, '_email', true);
                    }
					noo_mail( $to, $subject, $email, array(), 'noo_notify_job_alert_candidate' );
				}

				// Count
				update_post_meta( $alert->ID, '_notify_count', 1 + absint( noo_get_post_meta( $alert->ID, '_notify_count', 0 ) ) );
			}

			self::set_alert_schedule( $alert->ID );
		}

		public function _get_alert_jobs( $alert ) {
			global $wpdb;

			$alert_id = $alert->ID;

			$meta_query = array();
			$tax_query  = array();
			$date_query = array();

			$keywords        = noo_get_post_meta( $alert_id, '_keywords', '' );
			$search_keywords = array_map( 'trim', explode( ',', $keywords ) );
			$keywords_where  = array();

			if ( ! empty( $search_keywords ) && count( $search_keywords ) ) :
				foreach ( $search_keywords as $keyword ) {
					$keywords_where[] = 'post_title LIKE \'%' . esc_sql( $keyword ) . '%\' OR post_content LIKE \'%' . esc_sql( $keyword ) . '%\'';
				}

				$where    = implode( ' OR ', $keywords_where );
				$post__in = array_merge( $wpdb->get_col( "
				    SELECT DISTINCT ID FROM {$wpdb->posts}
				    WHERE ( {$where} )
				    AND post_type = 'noo_job'
				    AND post_status = 'publish'" ), array( 0 ) ); // add 0 value to make sure there's no result if no job matchs keywords

			endif;

			$location = noo_get_post_meta( $alert_id, '_job_location');

            if ( ! empty( $location ) ) {
			$location = noo_json_decode( $location );
				if(!empty($location)){
					$tax_query['relation'] = 'AND';
					$location_query = array(
						'taxonomy' => 'job_location',
						'field'    => 'term_id',
						'terms'    => $location,
					);
					$tax_query[]    = $location_query;
				}
			}

			$category = noo_get_post_meta( $alert_id, '_job_category');

            if ( ! empty( $category ) ) {
			$category = noo_json_decode( $category );
				if(!empty($location)){
					$category_query = array(
						'taxonomy' => 'job_category',
						'field'    => 'term_id',
						'terms'    => $category,
					);
					$tax_query[]    = $category_query;
				}
			}

			$type = noo_get_post_meta( $alert_id, '_job_type' );
            if ( ! empty( $type ) ) {
			$type = noo_json_decode($type);
				if(!empty($type)){
					$type_query  = array(
						'taxonomy' => 'job_type',
						'field'    => 'term_id',
						'terms'    => $type,
					);
					$tax_query[] = $type_query;
				}
			}
			$tag = noo_get_post_meta($alert_id,'_job_tag');
            if(!empty($tag)){
			$tag = noo_json_decode($tag);
				if(!empty($tag)){
				    $tag_query = array(
				         'taxonomy' => 'job_tag',
	                     'field'    => 'term_id',
	                     'terms'    => $tag,
	                );
				    $tax_query[] = $tag_query;
				}
            }
            $fields=array();
            for($po=1;$po<=8;$po++){
                $fields[]= jm_get_job_alert_setting('job_alert'.$po.'',5);
            }
            foreach ($fields as $key => $value ){
                if($value== '_closing'){
                    $value_start= noo_get_post_meta($alert_id,'_closing_start','');
                    $value_start = ! empty( $value_start ) ? strtotime( "midnight", $value_start ) : 0;
                    $value_end  =  noo_get_post_meta($alert_id,'_closing_end','');
                    $value_end   = ! empty( $value_end ) ? strtotime( "tomorrow", strtotime( "midnight", $value_end ) ) - 1 : strtotime( '2090/12/31' );
                    $meta_query[] = array(
                        'key'     => $value,
                        'value'   => array( $value_start, $value_end ),
                        'compare' => 'BETWEEN',
                        'type'    => 'NUMERIC',
                    );
                } elseif($value!=='job_category' || $value !=='job_tag' || $value !=='job_type' || $value !== 'job_location'){
                    $meta_value = noo_get_post_meta($alert_id,$value, '');
                    if(!empty($meta_value)){
                       	if(is_array($meta_value)){
                           	$temp_meta_query = array( 'relation' => 'OR' );
                           	foreach ( $meta_value as $v ) {
                               	if ( empty( $v ) ) {
                                   continue;
                               	}
                               	$temp_meta_query[] = array(
                                   'key'     => $value,
                                   'value'   => '"' . $v . '"',
                                   'compare' => 'LIKE',
                               	);
                           	}
                           	$meta_query[] = $temp_meta_query;
                       	} else {
                           $meta_query[] = array(
                               'key'   => $value,
                               'value' => $meta_value,
                           );
                       }
                    }
                }
            }


//			$last_schedule_time = noo_get_post_meta( $alert_id, '_start_schedule_time', '' );
//			if ( ! empty( $last_schedule_time ) ) {
//				$date_query['after'] = get_date_from_gmt( date( 'Y-m-d H:i:s', absint( $last_schedule_time ) ), 'Y-m-d H:i:s' );
//			} else {
				$frequency = noo_get_post_meta( $alert_id, '_frequency', '' );
				switch ( $frequency ) {
					case 'monthly':
						$date_query['after'] = '-1 month';
						break;
					case 'fortnight':
						$date_query['after'] = '-1 fortnight';
						break;
					case 'daily':
						$date_query['after'] = '-1 day';
						break;
					case 'hourly':
						$date_query['after'] = '-1 hour';
						break;
					default: // weekly
						$date_query['after'] = '-1 week';
						break;
				}

			$args = array(
				'post_type'      => 'noo_job',
				'post_status'    => 'publish',
				'posts_per_page' => - 1,
                'nopaging'       => true,
                'post__in'		 => $post__in,
				'tax_query'      => $tax_query,
				'meta_query'     => $meta_query,
				'date_query'     => $date_query,
			);

			do_action( 'before_get_job_alert', $args );

			$result = new WP_Query( $args );

			do_action( 'after_get_job_alert', $args );

			return $result;
		}

		private function _format_email( $alert, $user, $jobs ) {
			$max_alert_job_count = self::get_setting( 'max_job_count_email', 5 );
			$site_name           = get_bloginfo( 'name' );
			$dear=$user->display_name;
            if(empty($dear)){
               $dear=$alert->post_title;
            }
			$message = sprintf( __( 'Dear %s,', 'noo' ), $dear ) . '<br/><br/>';
			$message .= sprintf( __( 'We found %d new jobs that match your criteria.', 'noo' ), $jobs->found_posts ) . '<br/><br/>';

			if ( $jobs && $jobs->have_posts() ) {
				$count = 0;
				while ( $jobs->have_posts() && $count <= $max_alert_job_count ) :
					$jobs->the_post();
					global $post;
					$count ++;
					$locations  = wp_get_post_terms( $post->ID, 'job_location', array( 'fields' => 'names' ) );
					$categories = wp_get_post_terms( $post->ID, 'job_category', array( 'fields' => 'names' ) );
					$types      = wp_get_post_terms( $post->ID, 'job_type', array( 'fields' => 'names' ) );
					$tag        = wp_get_post_terms( $post->ID, 'job_tag', array('fields' =>'names'));

					$message .= sprintf( __( '%s: <a href="%s">%s</a>', 'noo' ), get_the_title( $post ), get_permalink( $post->ID ), get_permalink( $post->ID ) ) . '<br/>';
					$message .= sprintf( __( '** Location: %s', 'noo' ), implode( ', ', $locations ) ) . '<br/>';
					$message .= sprintf( __( '** Job Category: %s', 'noo' ), implode( ', ', $categories ) ) . '<br/>';
					$message .= sprintf( __( '** Job Type: %s', 'noo' ), implode( ', ', $types ) ) . '<br/>';
					$message .= sprintf( __( '**  Job Tag: %s','noo'),implode(', ',$tag)).'<br/>';
					$message .= __( '------', 'noo' ) . '<br/>';

				endwhile;

				if ( $jobs->found_posts > $max_alert_job_count ) {
					// @TODO: add search link
					$message .= sprintf( __( 'View more jobs: %s', 'noo' ), get_home_url() ) . '<br/>';
				}
			}

			$message .= '<br/>' . __( 'Best regards,', 'noo' ) . '<br/>';
			$message .= $site_name;

			return apply_filters( 'noo_job_alerts_email_content', $message );
		}

		public static function get_frequency() {
			$frequency = array(
				'daily'     => __( 'Daily', 'noo' ),
				'weekly'    => __( 'Weekly', 'noo' ),
				'fortnight' => __( 'Fortnightly', 'noo' ),
				'monthly'   => __( 'Monthly', 'noo' ),
			);

			return apply_filters( 'get_frequency', $frequency );
		}

		public function new_job_alert_popup() {

			if ( Noo_Member::is_employer() ) {
				$result = array(
					'success' => false,
					'message' => '<span class="error-response">' . __( 'Please login as a job seeker.', 'noo' ) . '</span>',
				);

				wp_send_json( $result );
			}

			if ( ! check_ajax_referer( 'noo-job-alert-form', 'security', false ) ) {
				$result = array(
					'success' => false,
					'message' => '<span class="error-response">' . __( 'Your session has expired or you have submitted an invalid form.', 'noo' ),
				);
				wp_send_json( $result );
			}

			$candidate_id = get_current_user_id();
			$fields=array();
			for($po=1;$po<=8;$po++){
                $fields[]= jm_get_job_alert_setting('job_alert'.$po.'',5);
            }

            $location = isset($_POST['location']) ? ($_POST['location']) : array();
            $category = isset($_POST['category']) ? ($_POST['category']) : array();
            $type = isset($_POST['type']) ? ($_POST['type']) : '';
            $address = isset($_POST['_full_address']) ? sanitize_text_field($_POST['_full_address']) : '';
            $tags = isset($_POST['tag']) ? ($_POST['tag']) : '';
            $closing_start= isset($_POST['_closing_start']) ? ($_POST['_closing_start']) : '';
            $closing_end = isset($_POST['_closing_end']) ? ($_POST['_closing_end']) : '' ;
            $name = isset($_POST['job_alert_name']) ? sanitize_text_field($_POST['job_alert_name']) : '';
            $keywords = isset($_POST['job_alert_keywords']) ? sanitize_text_field($_POST['job_alert_keywords']) : '';
            $frequency = isset($_POST['job_alert_frequency']) ? sanitize_text_field($_POST['job_alert_frequency']) : '';
            $email = isset($_POST['job_alert_email']) ? sanitize_email($_POST['job_alert_email']) : '';
			if(!is_user_logged_in()){

                if(empty($email)){
                    $result=array(
                        'success'=>false,
                        'message'=>'<span class="error-response">' . __( 'Your job alert needs an email', 'noo' ),
                    );
                    wp_send_json($result);
                }

                $job_alert = array(
                    'post_title'  => $email,
                    'post_type'   => 'noo_job_alert',
                    'post_status' => 'publish',
                );

            }else{
                $job_alert = array(
                    'post_title'  => $name,
                    'post_type'   => 'noo_job_alert',
                    'post_status' => 'publish',
                    'post_author' => $candidate_id,
                );
                if ( empty( $name ) ) {
                    $result = array(
                        'success' => false,
                        'message' => '<span class="error-response">' . __( 'Your job alert needs a name.', 'noo' ),
                    );

                    wp_send_json( $result );
                }

            }

            $post_id = wp_insert_post( $job_alert );
			if(!empty($post_id)){
                foreach ($fields as $key => $value){
                    switch ($value)
                    {
                        case 'job_category':
                            {
                                $cat_save = array();
                                foreach ( (array) $category as $cat ) {
                                    $term = get_term_by( 'slug', $cat, 'job_category' );
                                    $cat_save[] = $term->term_id;
                                }
                                update_post_meta( $post_id, '_job_category', json_encode( $cat_save ) );
                                break;
                            }
                        case 'job_type':
                            {
                                $type_save=array();
                                foreach ((array)$type as $type){
                                    $term = get_term_by( 'slug', $type, 'job_type' );
                                    $type_save[] = $term->term_id;
                                }
                                update_post_meta( $post_id, '_job_type',json_encode($type_save)  );
                                break;
                            }
                        case 'job_location':
                            {
                                $loc_save = array();
                                foreach ((array)$location as $loc) {
                                    $term = get_term_by('slug', $loc, 'job_location');
                                    $loc_save[] = $term->term_id;
                                }
                                update_post_meta( $post_id, '_job_location', json_encode( $loc_save ) );
                                break;
                            }
                        case 'job_tag':
                            {
                                $tag_save=array();
                                foreach ((array)$tags as $tag){
                                    $term = get_term_by('slug',$tag,'job_tag');
                                    $tag_save[] = $term->term_id;
                                }
                                update_post_meta($post_id,'_job_tag',json_encode($tag_save));
                            }
                        case '_closing':
                            {
                                update_post_meta($post_id,'_closing_start',$closing_start);
                                update_post_meta($post_id,'_closing_end',$closing_end);
                            }
                        default:
                            {
                                update_post_meta($post_id,$value,$_POST[$value]);
                            }
                    }
                }
                update_post_meta( $post_id, '_keywords', $keywords );
                update_post_meta( $post_id, '_frequency', $frequency );
                update_post_meta( $post_id, '_email', $email );
                Noo_Job_Alert::set_alert_schedule( $post_id, $frequency );

                do_action( 'noo_save_job_alert', $post_id );

                $result = array(
                    'success' => true,
                    'message' => '<span class="error-response">' . __( 'New job alert successfully added.', 'noo' ),
                    'id' => $post_id,
                );

                wp_send_json( $result );

            } else {

                $result = array(
                    'success' => false,
                    'message' => '<span class="error-response">' . __( 'There\'s an unknown error. Please retry or contact Administrator.', 'noo' ),
                );

                wp_send_json( $result );
            }
		}

	}

	new Noo_Job_Alert();
endif;
