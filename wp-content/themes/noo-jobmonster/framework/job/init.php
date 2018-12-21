<?php
if ( ! function_exists( 'jm_register_job_post_type' ) ) :
	function jm_register_job_post_type() {
		if ( post_type_exists( 'noo_job' ) ) {
			return;
		}

		$job_slug    = jm_get_job_setting( 'archive_slug', 'jobs' );
		$job_rewrite = $job_slug ? array(
			'slug'       => sanitize_title( $job_slug ),
			'with_front' => true,
			'feeds'      => true
		) : false;

		register_post_type(
			'noo_job',
			array(
				'labels' => array(
					'name'               => __( 'Jobs', 'noo' ),
					'singular_name'      => __( 'Job', 'noo' ),
					'add_new'            => __( 'Add New Job', 'noo' ),
					'add_new_item'       => __( 'Add Job', 'noo' ),
					'edit'               => __( 'Edit', 'noo' ),
					'edit_item'          => __( 'Edit Job', 'noo' ),
					'new_item'           => __( 'New Job', 'noo' ),
					'view'               => __( 'View', 'noo' ),
					'view_item'          => __( 'View Job', 'noo' ),
					'search_items'       => __( 'Search Job', 'noo' ),
					'not_found'          => __( 'No Jobs found', 'noo' ),
					'not_found_in_trash' => __( 'No Jobs found in Trash', 'noo' ),
					'parent'             => __( 'Parent Job', 'noo' ),
					'all_items'          => __( 'All Jobs', 'noo' ),
				),
				'description'         => __( 'This is a place where you can add new job.', 'noo' ),
				'public'              => true,
				'menu_icon'           => 'dashicons-portfolio',
				'show_ui'             => true,
				'capability_type'     => 'post',
				'map_meta_cap'        => true,
				'publicly_queryable'  => true,
				'exclude_from_search' => false,
				'hierarchical'        => false, // Hierarchical jobs memory issues - WP loads all records!
				'rewrite'             => apply_filters( 'jm_job_rewrite', $job_rewrite ),
				'query_var'           => true,
				'supports'            => noo_get_option( 'noo_job_comment', false ) ? array(
					'title',
					'editor',
					'comments'
				) : array( 'title', 'editor' ),
				'has_archive'         => true,
				'show_in_nav_menus'   => true,
				'delete_with_user'    => true,
				'can_export'          => true
			) );
		register_taxonomy(
			'job_category',
			array('noo_job'),
			array(
				'labels'       => array(
					'name'          => __( 'Job Category', 'noo' ),
					'add_new_item'  => __( 'Add New Job Category', 'noo' ),
					'new_item_name' => __( 'New Job Category', 'noo' )
				),
				'hierarchical' => true,
				'query_var'    => true,
				'rewrite'      => array( 'slug' => _x( 'job-category', 'slug', 'noo' ) )
			) );
		register_taxonomy(
			'job_type',
			'noo_job',
			array(
				'labels'       => array(
					'name'          => __( 'Job Type', 'noo' ),
					'add_new_item'  => __( 'Add New Job Type', 'noo' ),
					'new_item_name' => __( 'New Job Type', 'noo' )
				),
				'hierarchical' => true,
				'query_var'    => true,
				'rewrite'      => array( 'slug' => _x( 'job-type', 'slug', 'noo' ) )
			) );
		register_taxonomy(
			'job_tag',
			'noo_job',
			array(
				'labels'       => array(
					'name'          => __( 'Job Tag', 'noo' ),
					'add_new_item'  => __( 'Add New Job Tag', 'noo' ),
					'new_item_name' => __( 'New Job Tag', 'noo' )
				),
				'hierarchical' => false,
				'query_var'    => true,
				'rewrite'      => array( 'slug' => _x( 'job-tag', 'slug', 'noo' ) )
			) );
		register_taxonomy(
			'job_location',
			'noo_job',
			array(
				'labels'       => array(
					'name'          => __( 'Job Location', 'noo' ),
					'add_new_item'  => __( 'Add New Job Location', 'noo' ),
					'new_item_name' => __( 'New Job Location', 'noo' )
				),
				'hierarchical' => true,
				'query_var'    => true,
				'rewrite'      => array( 'slug' => _x( 'job-location', 'slug', 'noo' ) )
			) );

		register_post_status( 'expired', array(
			'label'                     => _x( 'Expired', 'Job status', 'noo' ),
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'noo' )
		) );
		register_post_status( 'pending_payment', array(
			'label'                     => _x( 'Pending Payment', 'Job status', 'noo' ),
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Pending Payment <span class="count">(%s)</span>', 'Pending Payment <span class="count">(%s)</span>', 'noo' )
		) );
		register_post_status( 'inactive', array(
			'label'                     => __( 'Inactive', 'noo' ),
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => false,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'Inactive <span class="count">(%s)</span>', 'Inactive <span class="count">(%s)</span>', 'noo' ),
		) );
	}

	add_action( 'init', 'jm_register_job_post_type', 0 );
endif;

if ( ! function_exists( 'jm_job_switch_theme_hook' ) ) :
	function jm_job_switch_theme_hook( $newname = '', $newtheme = '' ) {
		_job_insert_default_data();
	}

	add_action( 'after_switch_theme', 'jm_job_switch_theme_hook' );

	if ( ! function_exists( '_job_insert_default_data' ) ) :
		function _job_insert_default_data() {
			if ( get_option( 'noo_job_insert_default_data' ) == '1' ) {
				return;
			}
			$taxonomies     = array(
				'job_type' => array(
					'Full Time',
					'Part Time',
					'Freelance',
					'Contract'
				)
			);
			$default_colors = array( '#f14e3b', '#458cce', '#e6b707', '#578523' );

			foreach ( $taxonomies as $taxonomy => $terms ) {
				foreach ( $terms as $index => $term ) {
					if ( ! get_term_by( 'slug', sanitize_title( $term ), $taxonomy ) ) {
						$result = wp_insert_term( $term, $taxonomy );
						if ( ! is_wp_error( $result ) && $taxonomy == 'job_type' ) {
							if ( function_exists( 'update_term_meta' ) ) {
								update_term_meta( $result['term_id'], '_color', $default_colors[ $index ] );
							}
						}
					}
				}
			}

			delete_option( 'noo_job_insert_default_data' );
			update_option( 'noo_job_insert_default_data', '1' );
		}
	endif;
endif;

/**
 * Custom column Listing type
 *
 * @package       Jobmonster
 * @author        NooTeam <suppport@nootheme.com>
 * @version       1.0
 */

if ( ! function_exists( 'noo_add_table_columns_job_category' ) ) :

	function noo_add_table_columns_job_category( $columns ) {

		unset( $columns[ 'name' ] );
		unset( $columns[ 'description' ] );
		unset( $columns[ 'slug' ] );
		unset( $columns[ 'posts' ] );

		$columns[ 'icon' ]        = esc_html__( 'Icon', 'noo' );
		$columns[ 'name' ]        = esc_html__( 'Type', 'noo' );
		$columns[ 'description' ] = esc_html__( 'Description', 'noo' );
		$columns[ 'posts' ]       = esc_html__( 'Count', 'noo' );

		return apply_filters( 'noo_add_table_columns_job_category', $columns );
	}

	add_filter( 'manage_edit-job_category_columns', 'noo_add_table_columns_job_category' );

endif;

/**
 * Show custom column to Listing type
 *
 * @package       Jobmonster
 * @author        NooTeam <suppport@nootheme.com>
 * @version       1.0
 */

if ( ! function_exists( 'rp_show_table_columns_job_category' ) ) :

	function rp_show_table_columns_job_category( $c, $column_name, $term_id ) {

		global $post;
		switch ( $column_name ) {

			case 'icon':

				$icon = get_term_meta( $term_id, 'icon_type', true );
				if ( empty( $icon ) ) {
					$icon = 'fa fa-home';
				}
				echo '<i class="fa ' . esc_attr( $icon ) . '"></i>';
				break;
		}
	}

	add_action( 'manage_job_category_custom_column', 'rp_show_table_columns_job_category', 10, 3 );

endif;

/**
 * Add custom field to Listing type
 *
 * @package       Jobmonster
 * @author        NooTeam <suppport@nootheme.com>
 * @version       1.0
 */

if ( ! function_exists( 'noo_add_field_job_category' ) ) :

	function noo_add_field_job_category() {
		wp_enqueue_style( 'vendor-fontawesome', NOO_FRAMEWORK_URI . '/vendor/fontawesome/css/font-awesome.min.css');
		wp_enqueue_script( 'noo-iconpicker', NOO_FRAMEWORK_ADMIN_URI . '/assets/js/noo-iconpicker.js', null, null, false );
		add_action( 'admin_footer', function() {
		    ?>
            <script>
				jQuery(document).ready(function ( $ ) {
					$('.noo-iconpicker-input').iconpicker({
						placement: 'top'
					});
				});
            </script>
            <?php
        }, 999 );
		?>
		<div id="noo-item-icon_type-wrap" class="noo-item-wrap" style="margin-bottom: 30px;">
            <label><?php echo esc_html__( 'Icon', 'noo' ) ?></label>
			<div class="noo-iconpicker">
				<div class="noo-iconpicker-group">
					<input data-placement="bottomRight" class="noo-iconpicker-input" type="text" name="icon_type" id="noo-field-item-icon_type" value=""/>
					<span class="input-group-addon"></span>
				</div>
			</div>
		</div>
		<?php
	}

	add_action( apply_filters( 'rp_property_job_category', 'job_category' ) . '_add_form_fields', 'noo_add_field_job_category' );

endif;

/**
 * Process data when edit custom field to Listing type
 *
 * @package       Jobmonster
 * @author        NooTeam <suppport@nootheme.com>
 * @version       1.0
 */

if ( ! function_exists( 'rp_edit_field_job_category' ) ) :

	function rp_edit_field_job_category( $term, $taxonomy ) {
		/**
		 * VAR
		 */
		$transient_name = 'rp_edit_field_job_category_' . $term->term_id;
		if ( false === ( $icon = get_transient( $transient_name ) ) ) {

			$icon = get_term_meta( $term->term_id, 'icon_type', true );

			if ( empty( $icon ) ) {
				$icon = 'fa-home';
			}

			set_transient( $transient_name, $icon, YEAR_IN_SECONDS );
		}

		$args_icon = array(
			'name' => 'icon_type',
			'type' => 'icon',
		);

		wp_enqueue_style( 'vendor-fontawesome', NOO_FRAMEWORK_URI . '/vendor/fontawesome/css/font-awesome.min.css');
		wp_enqueue_script( 'noo-iconpicker', NOO_FRAMEWORK_ADMIN_URI . '/assets/js/noo-iconpicker.js', null, null, false );
		add_action( 'admin_footer', function() {
			?>
            <script>
				jQuery(document).ready(function ( $ ) {
					$('.noo-iconpicker-input').iconpicker({
						placement: 'top'
					});
				});
            </script>
			<?php
		}, 999 );
		?>
		<tr class="form-field">
			<th scope="row" valign="top"><label><?php echo esc_html__( 'Icon', 'noo' ); ?></label></th>
			<td>
                <div class="noo-iconpicker">
                    <div class="noo-iconpicker-group">
                        <input data-placement="bottomRight" class="noo-iconpicker-input" type="text" name="icon_type" id="noo-field-item-icon_type" value="<?php echo esc_attr( $icon ) ?>"/>
                        <span class="input-group-addon"></span>
                    </div>
                </div>
			</td>
		</tr>
		<?php
	}

	add_action( apply_filters( 'rp_property_job_category', 'job_category' ) . '_edit_form_fields', 'rp_edit_field_job_category', 10, 2 );

endif;

/**
 * Process data when save custom field to Listing type
 *
 * @package       Jobmonster
 * @author        NooTeam <suppport@nootheme.com>
 * @version       1.0
 */

if ( ! function_exists( 'rp_save_field_job_category' ) ) :

	function rp_save_field_job_category( $term_id, $tt_id, $taxonomy ) {

		if ( isset( $_POST[ 'icon_type' ] ) ) {

			$transient_name = 'rp_edit_field_job_category_' . $term_id;

			delete_transient( $transient_name );

			update_term_meta( $term_id, 'icon_type', esc_attr( $_POST[ 'icon_type' ] ) );
		}
	}

	add_action( 'created_term', 'rp_save_field_job_category', 10, 3 );
	add_action( 'edit_term', 'rp_save_field_job_category', 10, 3 );

endif;

if ( ! function_exists( 'noo_get_list_job_category' ) ) :

	function noo_get_list_job_category() {

		$job_categorys = get_categories( array( 'orderby' => 'NAME', 'order' => 'ASC', 'taxonomy' => 'job_category' ) );
		$list = array();
		foreach ( (array)$job_categorys as $job_category ) {
			$list[ $job_category->name ] = $job_category->term_id;
		}

		return $list;
	}

endif;

if ( ! function_exists( 'noo_heding_shortcode' ) ) :
    
    function noo_heding_shortcode( $title = '', $sub_title = '', $align = 'text-center' ) {
        ?>
        <div class="noo-heading-sc <?php echo esc_attr( $align ) ?>">
            <?php if ( !empty( $title ) ) : ?>
                <h3 class="noo-title-sc">
                    <?php echo $title ?>
                </h3>
            <?php endif; ?>

	        <?php if ( !empty( $title ) ) : ?>
                <p class="noo-subtitle-sc">
			        <?php echo esc_html( $sub_title ) ?>
                </p>
	        <?php endif; ?>
        </div>
        <?php
    }
    
endif;

if ( !function_exists( 'noo_job_category_query' ) ) {
	function noo_job_category_query($query){
        if(is_tax('job_category') && $query->is_main_query()){
            $query->set('post_type', 'noo_job');
        }
    }
    add_action( 'pre_get_posts', 'noo_job_category_query' );
}
