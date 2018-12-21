<?php
$heading_img  = noo_get_option( 'noo_job_heading_image', '' );
$heading_text = noo_get_option( 'noo_job_heading_text', '' );

$search_keyword = isset( $_GET[ 's' ] ) ? $_GET[ 's' ] : '';
$hide_empty_tax = jm_get_job_setting( 'hide_empty_tax','');

$hide_search_location = jm_get_job_setting( 'job_search_location','');
$hide_search_category = jm_get_job_setting( 'job_search_category','');

$location_args = array(
	'show_option_all' => __( 'All locations', 'noo' ),
	'hide_empty'      => (int)$hide_empty_tax,
	'echo'            => 1,
	'selected'        => isset( $_GET[ 'location' ] ) ? $_GET[ 'location' ] : '',
	'hierarchical'    => 1,
	'name'            => 'location',
	'id'              => 'noo-field-job_location',
	'class'           => 'noo-form-control noo-select form-control',
	'depth'           => 0,
	'taxonomy'        => 'job_location',
	'value_field'     => 'slug',
    'orderby' => 'name',
    'walker'          => new Noo_Walker_TaxonomyDropdown(),
);
$categories_args = array(
     'show_option_all'  => __('All categories','noo'),
    'hide_empty'        => (int)$hide_empty_tax,
    'echo'              => 1,
    'selected'          => isset($_GET['category']) ? $_GET['category'] : '',
    'hierarchical'    => 1,
    'name'            => 'category',
    'id'              => 'noo-field-job_category',
    'class'           => 'noo-form-control noo-select form-control',
    'depth'           => 0,
    'taxonomy'        => 'job_category',
    'value_field'     => 'slug',
    'orderby' => 'name',
    'walker'          => new Noo_Walker_TaxonomyDropdown(),
);

$noo_enable_parallax = noo_get_option( 'noo_enable_parallax', 1 );
$heading_image = get_page_heading_image();
?>
<header class="noo-page-heading noo-job-heading"
        style="background-image: url('<?php echo esc_url( $heading_img ); ?>');
        <?php echo ( ! $noo_enable_parallax ) ? 'background: url(' . esc_url( $heading_image ) . ') no-repeat center center; -webkit-background-size: cover; -moz-background-size: cover; -o-background-size: cover; background-size: cover;' : 'background: rgba(67, 67, 67, 0.55);'; ?> ">
	<div class="container-boxed max">
		<?php if ( noo_get_option( 'noo_jobs_show_search', 1 ) ) : ?>
			<div class="noo-heading-search">
				<form id="noo-heading-search-form" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<div class="row">
						<div class="col-sm-4">
							<label class="noo-form-label">
								<?php echo esc_html__( 'Search Job Now:', 'noo' ); ?>
							</label>
							<input type="text" name="s" class="noo-form-control"
							       value="<?php echo esc_attr( $search_keyword ); ?>"
							       placeholder="<?php echo esc_html__( 'Enter keywords...', 'noo' ); ?>">
						</div>
						<?php if(!$hide_search_location):?>
							<div class="col-sm-3">
								<label class="noo-form-label">
									<?php echo esc_html__( 'Location:', 'noo' ); ?>
								</label>
								<?php wp_dropdown_categories( $location_args ); ?>
							</div>
						<?php endif;?>
						<?php if(!$hide_search_category):?>
		                    <div class="col-sm-3">
		                        <label class="noo-form-label">
		                            <?php echo esc_html__('Category','noo'); ?>
		                        </label>
		                        <?php wp_dropdown_categories($categories_args) ?>
		                    </div>
		                <?php endif;?>
						<div class="col-sm-2">
							<label>&nbsp;</label>
							<button style="display: block;" type="submit"
							        class="btn btn-primary noo-btn-search"><?php echo esc_html__( 'Search', 'noo' ); ?></button>
						</div>
					</div>
					<input type="hidden" name="post_type" value="noo_job">
				</form>
				
				<?php if ( ! empty( $heading_text ) ): ?>
					<div class="noo-search-html">
						<?php echo $heading_text; ?>
					</div>
				<?php endif; ?>
			</div>
			<?php else: list( $heading ) = get_page_heading();?>
				<h1 class="page-title"><?php echo esc_html($heading);?></h1>
		<?php endif; ?>
	</div><!-- /.container-boxed -->
	<?php if ( ! empty( $heading_image ) ) : ?>
				<?php if ( $noo_enable_parallax ) : ?>
					<div class="parallax" data-parallax="1" data-parallax_no_mobile="1" data-velocity="0.1"
					     style="background-image: url(<?php echo esc_url( $heading_image ); ?>); background-position: 50% 0; background-repeat: no-repeat;"></div>
				<?php endif; ?>
			<?php endif; ?>
</header>