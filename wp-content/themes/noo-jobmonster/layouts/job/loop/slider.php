<?php if ( $wp_query->have_posts() ): ?>
    <div class="jobs posts-loop slider <?php  if ( isset( $slider_style ) && ! empty( $slider_style ) ) {
		echo $slider_style;
	} ?>">
		<?php if ( ! empty( $title ) ): ?>
            <div class="posts-loop-title<?php if ( is_singular( 'noo_job' ) )
				echo ' single_jobs' ?>">
                <h3><?php echo $title; ?></h3>
            </div>
		<?php endif; ?>
		<?php $id_slider = uniqid( 'slider_post' ); ?>
        <div id="<?php echo $id_slider; ?>" style="direction: ltr;" class="slider-wrapper">

			<?php while ( $wp_query->have_posts() ) : $wp_query->the_post();
				global $post; ?>
				<?php
				$company_name     = '';
				$logo_company     = '';
				$company_featured = false;
				$type             = jm_get_job_type( $post );
				// $cover_image      = '';

				$company_id = jm_get_job_company( $post );

				$locations = get_the_terms( get_the_ID(), 'job_location' );
				if ( ! empty( $company_id ) ) {
					$company_name     = get_the_title( $company_id );
					$company_featured = noo_get_post_meta( $company_id, '_company_featured' ) == 'yes';
					if ( noo_get_option( 'noo_jobs_show_company_logo', true ) ) {
						$logo_company = Noo_Company::get_company_logo( $company_id );
					}
				}
				// ===== Cover Images
				// $cover_image_id = noo_get_post_meta( get_the_ID(), '_cover_image' );
				// $cover_image    = wp_get_attachment_image( $cover_image_id, 'noo-thumbnail-square', false );

				?>
                <div <?php post_class(); ?>>
					<!-- <?php if ( ! empty( $cover_image ) ) : ?>
                        <div class="img-thumb">
                            <a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>">
								<?php echo $cover_image; ?>
                            </a>
                        </div>
					<?php endif; ?> -->
                    <div class="loop-item-wrap">
                        <div class="item-title-bar">
							<?php if ( ! empty( $logo_company ) ) : ?>
                                <div class="item-featured <?php echo $company_featured ? 'featured-company' : ''; ?> ">
                                    <a href="<?php the_permalink() ?>" title="<?php the_title(); ?>">
										<?php echo $logo_company; ?>
                                    </a>
                                </div>
							<?php endif; ?>
                            <div class="items">
                                <h4 class="item-title">
                                    <a href="<?php the_permalink() ?>" title="<?php the_title(); ?>">
										<?php
										if ( function_exists( 'mb_strlen' ) && function_exists( 'mb_substr' ) ):
											echo ( mb_strlen( get_the_title() ) >= 70 ) ? mb_substr( get_the_title(), 0,
													70 ) . '...' : get_the_title();
										else:
											echo ( strlen( get_the_title() ) >= 70 ) ? substr( get_the_title(), 0,
													70 ) . '...' : get_the_title();
										endif;
										?>
                                    </a>
                                </h4>
                                <h4 class="item-company">
                                    <a href="<?php echo esc_url( get_permalink( $company_id ) ); ?>"
                                       title="<?php echo $company_name; ?>">
										<?php echo $company_name; ?>
                                    </a>
                                </h4>
                            </div>
                        </div>
                        <div class="item-info">
							<?php if ( ! empty( $type ) ) : ?>
                                <?php foreach ($type as $typ): ?>
                                <span class="job-type">
									<a href="<?php echo get_term_link( $typ, 'job_type' ); ?>"
                                       style="color: <?php echo $typ->color; ?>">
										<i class="fa fa-bookmark"></i>
										<?php echo $typ->name; ?>
									</a>
								</span>
                            <?php endforeach; ?>
							<?php endif; ?>
							<?php
							$locations_html = '';
							$separator      = ', ';
							if ( ! empty( $locations ) ) {
								foreach ( $locations as $location ) {
									$locations_html .= '<a href="' . get_term_link( $location->term_id,
											'job_location' ) . '"><em>' . $location->name . '</em></a>' . $separator;
								}
								$html = '<span>';
								$html .= '<i class="fa fa-map-marker"></i> ';
								$html .= trim( $locations_html, $separator );
								$html .= '</span>';
								echo $html;
							}
							?>
                        </div>
                        <div class="item-excerpt">
							<?php echo get_the_excerpt(); ?>
                        </div>
                        <div class="item-view-more">
                            <a class="btn btn-primary" href="<?php echo get_permalink( $post->ID ) ?>">
								<?php _e( 'View more', 'noo' ) ?>
                            </a>
                        </div>
                    </div>
                </div>
			<?php endwhile; ?>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function ($) {
                $("#<?php echo $id_slider; ?>").owlCarousel({
					<?php echo ( isset( $show_autoplay ) && $show_autoplay == 'on' ) ? 'autoPlay: ' . $slider_time . ', slideSpeed : ' . $slider_speed . ',' : '' ?>
                    singleItem: true,
                    navigation: true,
                    navigationText: ["", ""],
                    pagination: false,
                    autoHeight: false,
                });
            });
        </script>
    </div>
<?php endif; ?>