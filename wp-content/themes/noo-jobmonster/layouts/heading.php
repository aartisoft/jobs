<?php
list( $heading, $sub_heading ) = get_page_heading();
$noo_enable_parallax = noo_get_option( 'noo_enable_parallax', 1 );

$can_shortlist_candidate = noo_can_shortlist_candidate();

if ( ! empty( $heading ) ) :
	$heading_image = get_page_heading_image(); ?>

	<?php if ( is_post_type_archive( 'noo_job' ) or is_tax( 'job_category' ) or is_tax( 'job_location' ) ): ?>
		<?php noo_get_layout( 'job/heading-job' ); ?>
		<?php
		return;
	endif; ?>
	<?php if ( ! empty( $heading_image ) ) : ?>
		<?php if ( is_singular( 'noo_company' ) && Noo_Company::get_layout() == 'two' ) : ?>
			<header class="noo-page-heading noo-page-heading-company-2"
		        style="<?php echo ( ! $noo_enable_parallax ) ? 'background: url(' . esc_url( $heading_image ) . ') no-repeat center center; -webkit-background-size: cover; -moz-background-size: cover; -o-background-size: cover; background-size: cover;' : 'background: rgba(67, 67, 67, 0.55);'; ?>">
		<?php else: ?>
			<header class="noo-page-heading"
			        style="<?php echo ( ! $noo_enable_parallax ) ? 'background: url(' . esc_url( $heading_image ) . ') no-repeat center center; -webkit-background-size: cover; -moz-background-size: cover; -o-background-size: cover; background-size: cover;' : 'background: rgba(67, 67, 67, 0.55);'; ?>">
		<?php endif; ?>
	<?php else : ?>
		<header class="noo-page-heading <?php echo strtolower( preg_replace( '/\s+/', '-', $sub_heading ) ); ?>">
			<?php endif; ?>
			<div class="container-boxed max" style="position: relative; z-index: 1;">
				<?php
				$page_temp = get_page_template_slug();
				?>
				<?php if ( 'page-post-resume.php' === $page_temp || 'page-post-job.php' === $page_temp || ( is_user_logged_in() && get_the_ID() == Noo_Member::get_member_page_id() ) ): ?>
					<div class="member-heading-avatar">
						<?php echo noo_get_avatar( get_current_user_id(), 100 ); ?>
					</div>
					<div class="page-heading-info ">
						<?php if ( Noo_Member::is_employer() ):   ?>
							<?php
							$user_employer = get_current_user_id();
							$company_employer = get_user_meta( $user_employer, 'employer_company', true );
							$url_employer = get_permalink($company_employer);
							?>
							<h1 class="page-title" ><a href="<?php echo esc_url($url_employer); ?>" style="color:#fff;" ><?php echo ( $heading ); ?></a></h1>
						<?php elseif ( Noo_Member::is_candidate() ): ?>
							<h1 class="page-title"><a href="<?php echo Noo_Member::get_candidate_profile_url();  ?>" style="color:#fff;" ><?php echo ( $heading ); ?></a></h1>
						<?php endif; ?>
					</div>
				<?php elseif ( is_singular( 'noo_company' ) ) : ?>
					<?php
					$company_name   = get_post_field( 'post_title', get_the_ID() );
					$logo_company   = Noo_Company::get_company_logo( get_the_ID() );
					$post_view      = noo_get_post_views( get_the_ID() );
					$slogan         = noo_get_post_meta( $post->ID, '_slogan' );
					$layout_company = Noo_Company::get_layout();
					?>
					<div class="noo-company-heading">
						<div class="noo-company-info">
							<div class="noo-company-avatar">
								<a href="<?php echo get_permalink(); ?>"><?php echo $logo_company; ?></a>
							</div>
							<div class="noo-company-info">
								<h1 class="noo-company-name" <?php noo_page_title_schema(); ?>>
									<?php echo ( $heading ); ?>
									<?php
									if ( $post_view > 0 ) {
										echo '<span class="count">' . sprintf( _n( '%d view', '%d views', $post_view, 'noo' ), $post_view ) . '</span>';
									}
									?>
								</h1>
								<?php if ( ! empty( $slogan ) ) : ?>
									<div class="slogan">
										<?php echo esc_html( $slogan ); ?>
									</div>
								<?php endif; ?>

								<?php if ( 'two' == $layout_company ) : ?>
									<div class="company-meta">
										<?php
										$all_socials = noo_get_social_fields();
										$socials     = jm_get_company_socials();
										$html        = array();

										foreach ( $socials as $social ) {
											if ( ! isset( $all_socials[ $social ] ) ) {
												continue;
											}
											$data  = $all_socials[ $social ];
											$value = get_post_meta( get_the_ID(), "_{$social}", true );
											if ( ! empty( $value ) ) {
												$url    = $social == 'email_address' ? 'mailto:' . $value : esc_url( $value );
												$html[] = '<a title="' . sprintf( esc_attr__( 'Connect with us on %s', 'noo' ), $data[ 'label' ] ) . '" class="noo-icon fa ' . $data[ 'icon' ] . '" href="' . $url . '" target="_blank"></a>';
											}
										}

										if ( ! empty( $html ) && count( $html ) > 0 ) : ?>
											<div class="company-social">
												<?php echo implode( "\n", $html ); ?>
											</div>
										<?php endif; ?>

										<?php if ( Noo_Company::review_is_enable() ): ?>

											<span class="total-review">
		                                        <?php noo_box_rating( noo_get_total_point_review( get_the_ID() ), true ) ?>
												<span><?php echo '(' . noo_get_total_review( get_the_ID() ) . ')' ?></span>
		                                    </span>

										<?php endif; ?>
									</div>
								<?php endif; ?>
							</div>
						</div>
						<div class="noo-company-action">
                            <?php
                                $can_follow_company = noo_can_follow_company();
                            ?>
                            <?php if($can_follow_company): ?>
		                    <span class="noo-follow-company" data-company-id="<?php echo get_the_ID() ?>"
		                          data-user-id="<?php echo get_current_user_id(); ?>">
		                        <?php echo noo_follow_status( get_the_ID(), get_current_user_id() ) ?>
		                    </span>
		                    <span class="total-follow">
		                        <?php echo sprintf( __( '<span>%s</span> Follow', 'noo' ), noo_total_follow( get_the_ID() ) ) ?>
		                    </span>
                            <?php endif; ?>
						</div>
					</div>

				<?php else: ?>
					<div class="page-heading-info">
						<?php if ( is_singular( 'noo_resume' ) ) : ?>
							<?php
							$layout      = noo_get_option( 'noo_resumes_detail_layout', 'style-1' );
							$layout = ! empty( $_GET[ 'layout' ] ) ? sanitize_text_field( $_GET[ 'layout' ] ) : $layout;
							if ( 'style-1' == $layout ) {
							?>
								<h1 class="page-title">
									<span <?php noo_page_title_schema(); ?>><?php echo ( $heading ); ?></span>
									
									<?php if( $can_shortlist_candidate ):?>
										<a class="noo-shortlist" 
											href="#"
										   	data-resume-id="<?php echo esc_attr( $post->ID ) ?>"
										   	data-user-id="<?php echo get_current_user_id() ?>" data-type="text">
											<?php echo noo_shortlist_icon( $post->ID, get_current_user_id() ) ?>
											<?php echo noo_shortlist_status( $post->ID, get_current_user_id() ) ?>
										</a>
									<?php endif; ?>
									<?php
									global $post;
									$post_view = noo_get_post_views( $post->ID );
									if ( $post_view > 0 ) {
										echo '<span class="count">' . sprintf( _n( '%d view', '%d views', $post_view, 'noo' ), $post_view ) . '</span>';
									}
									?>
									
							<?php } elseif ( 'style-2' == $layout ) {
								$candidate_avatar   = '';
								$candidate_name     = '';
								if ( ! empty( $post->post_author ) ) :
									$candidate_avatar = noo_get_avatar( $post->post_author, 85 );
									$candidate      = get_user_by( 'id', $post->post_author );
									$candidate_name = $candidate->display_name;
									$candidate_link = esc_url( apply_filters( 'noo_resume_candidate_link', get_the_permalink(), $post->ID, $post->post_author ) );
									$slogan         = noo_get_post_meta( $post->ID, '_slogan' );
									$enable_upload  = (bool) jm_get_resume_setting( 'enable_upload_resume', '1' );
									$file_cv        = noo_json_decode( noo_get_post_meta( $post->ID, '_noo_file_cv' ) );
									?>
										<div class="noo-resume-info-heading">
											<div class="resume-avatar">
												<a href="<?php echo $candidate_link; ?>">
													<?php echo $candidate_avatar; ?>
												</a>
											</div>
											<div class="resume-info">
		                                        <?php $resume_id=$post->ID;
		                                            $can_view_resume=jm_can_view_heading_resume($resume_id);
		                                        ?>
												<?php if ( $enable_upload && ! empty( $file_cv ) && isset( $file_cv[ 0 ] ) && ! empty( $file_cv[ 0 ] ) ) : ?>
		                                            <?php if($can_view_resume==true ): ?>
													<a class="btn btn-primary resume-download pull-right"
													   href="<?php echo noo_get_file_upload( $file_cv[ 0 ] ); ?>"
													   title="<?php echo esc_attr__( 'Download CV', 'noo' ); ?>">
														<i class="fa fa-download"></i>
														<?php echo esc_html__( 'Download CV', 'noo' ); ?>
													</a>
		                                            <?php endif; ?>
												<?php endif; ?>

												<h1 class="item-author">
													<a href="<?php echo $candidate_link; ?>"
													   title="<?php echo esc_html( $candidate_name ); ?>">
														<?php echo esc_html( $candidate_name ); ?>
													</a>
													<?php
													$post_view = noo_get_post_views( $post->ID );
													if ( $post_view > 0 ) {
														echo '<span class="count">' . sprintf( _n( '(%d views)', '(%d views)', $post_view, 'noo' ), $post_view ) . '</span>';
													}
													?>
												</h1>
												<?php if ( ! empty( $slogan ) ) : ?>
													<h2 class="resume-slogan">
														<?php echo esc_html( $slogan ) ?>
													</h2>
												<?php endif; ?>

												<?php
												// Job's social info
												$all_socials = noo_get_social_fields();
												$socials     = jm_get_resume_socials();
												$enable_socials =noo_get_option('noo_resume_social','1');
												$enable_print = (bool) jm_get_resume_setting('enable_print_resume','1'); 
												$html         = array();

												foreach ( $socials as $social ) {
													if ( ! isset( $all_socials[ $social ] ) ) {
														continue;
													}
													$data  = $all_socials[ $social ];
													$value = get_post_meta( $post->ID, $social, true );
													if ( ! empty( $value ) ) {
														$url    = esc_url( $value );
														$html[] = '<a title="' . sprintf( esc_attr__( 'Connect with us on %s', 'noo' ), $data[ 'label' ] ) . '" class="noo-icon fa ' . $data[ 'icon' ] . '" href="' . $url . '" target="_blank"></a>';
													}
												}
												?>
												<div class="candidate-social">
													<?php if ( $enable_socials && ! empty( $html ) && count( $html ) > 0 ) : ?>
														<?php echo implode( "\n", $html ); ?>
													<?php endif; ?>
													<?php if ( $enable_print ) : ?>
                                                        <a data-invoice="<?php echo esc_attr($post->ID); ?>"
                                                           data-total-review="<?php echo (noo_get_total_review($post->ID)) ?>"
                                                           data-layout ="style-2"
                                                           data-post-review = "disable"
                                                           class=" btn-print-invoice print-resume noo-icon" href="#"
                                                           title="<?php echo esc_attr__('Print', 'noo'); ?>">
                                                            <i class="fa fa-print"></i>
                                                        </a>
                                                        <?php endif; ?>
													<?php if( $can_shortlist_candidate ):?>
														<a class="noo-shortlist" href="#"
														   data-resume-id="<?php echo esc_attr( $post->ID ) ?>"
														   data-user-id="<?php echo get_current_user_id() ?>" data-type="text">
															<?php echo noo_shortlist_icon( $post->ID, get_current_user_id() ) ?>
															<?php echo noo_shortlist_status( $post->ID, get_current_user_id() ) ?>
														</a>
													<?php endif; ?>
												</div>
											</div>
										</div>
										<?php
									endif;
								}
							?>
						<?php else : ?>
							<h1 class="page-title" <?php noo_page_title_schema(); ?>>
								<?php echo ( $heading ); ?>

								<?php
								if ( is_singular( 'noo_job' ) ) {
									global $post;
									$post_view = noo_get_post_views( $post->ID );
									$layout =  noo_get_option('noo_job_detail_layout','style-1');
									$layout = isset($_GET['layout']) ? sanitize_text_field( $_GET['layout'] ) : $layout;
									if ( $layout == 'style-1' ):
										if ( $post_view > 0 ) {
											echo '<span class="count">' . sprintf( _n( '%d view', '%d views', $post_view, 'noo' ), $post_view ) . '</span>';
										}
										$applications_count = noo_get_job_applications_count( $post->ID );
										if ( $applications_count > 0 ) {
											echo '<span class="count applications">' . sprintf( _n( '%d application', '%d applications', $applications_count, 'noo' ), $applications_count ) . '</span>';
										}
									endif;
								}
								?>
							</h1>
						<?php endif; ?>
						<?php if ( is_singular( 'post' ) ): ?>
							<?php noo_content_meta(); ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>
				<div class="page-sub-heading-info">
				
					<?php if ( is_singular( 'noo_job' ) ) :
						$is_schema = noo_get_option( 'noo_job_schema', false );
						$layout =  noo_get_option('noo_job_detail_layout','style-1');
						$layout = isset($_GET['layout']) ? sanitize_text_field( $_GET['layout'] ) : $layout;
						if ($layout == 'style-1') {
							jm_the_job_meta( array(
								'show_company' => true,
								'fields'       => array(
									'job_type',
									'_full_address',
									'job_location',
									'job_date',
									'_closing',
									'job_category',
								),
								'schema'       => $is_schema ? true : false,
							) );	
						} else {
							jm_the_job_meta( array(
								'show_company' => true,
								'fields'       => false,
								'schema'       => $is_schema ? true : false,
							) );
						}

						
					elseif ( is_singular( 'noo_resume' ) ) :
						echo '';
					elseif ( is_singular( 'noo_company' ) ) :
						echo '';
					elseif ( is_single( 'post' ) ) :
						noo_content_meta();
					elseif ( ! empty( $sub_heading ) ) :
						echo $sub_heading;
					endif; ?>
				</div>
			</div><!-- /.container-boxed -->
			<?php if ( ! empty( $heading_image ) ) : ?>
				<?php if ( $noo_enable_parallax ) : ?>
					<div class=" parallax" data-parallax="1" data-parallax_no_mobile="1" data-velocity="0.1"
					     style="background-image: url(<?php echo esc_url( $heading_image ); ?>); background-position: 50% 0; background-repeat: no-repeat;"></div>
				<?php endif; ?>
			<?php endif; ?>
		</header>
	<?php endif; ?>
	<?php if ( is_user_logged_in() && get_the_ID() == Noo_Member::get_member_page_id() ): ?>
		<div class="member-heading">
			<div class="container-boxed max">

				<div class="member-heading-nav">
					<?php

					$employer_heading_values  = jm_get_member_menu( 'employer_heading', array() );
					$candidate_heading_values = jm_get_member_menu( 'candidate_heading', array() );

					?>
					<ul>
						<?php if ( Noo_Member::is_employer() ) : ?>

							<?php if ( in_array( 'manage-job', $employer_heading_values ) or empty( $employer_heading_values ) ) : ?>
								<li class="<?php echo esc_attr( Noo_Member::get_actice_enpoint_class( array(
									'manage-job',
									'preview-job',
									'edit-job',
								) ) ) ?>"><a href="<?php echo Noo_Member::get_endpoint_url( 'manage-job' ) ?>"><i
											class="fa fa-file-text-o"></i> <?php _e( 'Jobs', 'noo' ) ?></a>
								</li>
							<?php endif; ?>

							<?php if ( in_array( 'manage-application', $employer_heading_values ) or empty( $employer_heading_values ) ) : ?>
								<li class="<?php echo esc_attr( Noo_Member::get_actice_enpoint_class( 'manage-application' ) ) ?>">
									<a href="<?php echo Noo_Member::get_endpoint_url( 'manage-application' ) ?>"
									   style="white-space: nowrap;">
										<i class="fa fa-newspaper-o"></i>
										<?php _e( 'Applications', 'noo' ) ?>
										<?php echo unseen_applications_number(); ?>
									</a>
								</li>
							<?php endif; ?>
							<?php if ( in_array( 'viewed-resume', $employer_heading_values ) or empty( $employer_heading_values ) ) : ?>
								<?php
								if ( jm_is_enabled_job_package_view_resume() ) : ?>
									<li class="<?php echo esc_attr( Noo_Member::get_actice_enpoint_class( array( 'viewed-resume' ) ) ) ?>">
										<a href="<?php echo Noo_Member::get_endpoint_url( 'viewed-resume' ) ?>"><i
												class="fa fa-file-text-o"></i> <?php _e( 'Viewed Resumes', 'noo' ) ?>
										</a>
									</li>
								<?php endif; ?>
							<?php endif; ?>

							<?php do_action( 'noo-member-employer-heading' ); ?>

							<?php if ( in_array( 'manage-plan', $employer_heading_values ) or empty( $employer_heading_values ) ) : ?>
								<?php if ( jm_is_woo_job_posting() ) : ?>
									<li class="<?php echo esc_attr( Noo_Member::get_actice_enpoint_class( 'manage-plan' ) ) ?>">
										<a href="<?php echo Noo_Member::get_endpoint_url( 'manage-plan' ) ?>"><i
												class="fa fa-file-text-o"></i> <?php _e( 'Manage Plan', 'noo' ) ?>
										</a>
									</li>
								<?php endif; ?>
							<?php endif; ?>
                        <?php $can_follow_company = noo_can_follow_company(); ?>
                        <?php if($can_follow_company): ?>
							<?php if ( in_array( 'manage-follow', $employer_heading_values ) or empty( $employer_heading_values ) ) : ?>
								<li class="<?php echo esc_attr( Noo_Member::get_actice_enpoint_class( 'manage-follow' ) ) ?>">
									<a href="<?php echo Noo_Member::get_endpoint_url( 'manage-follow' ) ?>">
										<i class="fa fa-plus"></i>
										<?php _e( 'Manage Follow', 'noo' ) ?>
									</a>
								</li>
							<?php endif; ?>
							<?php if ( in_array( 'job-follow', $employer_heading_values ) or empty( $employer_heading_values ) ) : ?>
								<li class="<?php echo esc_attr( Noo_Member::get_actice_enpoint_class( 'job-follow' ) ) ?>">
									<a href="<?php echo Noo_Member::get_endpoint_url( 'job-follow' ) ?>">
										<i class="fa fa-plus"></i>
										<?php _e( 'Job Follow', 'noo' ) ?>
									</a>
								</li>
							<?php endif; ?>
                        <?php endif; ?>
                            <?php if(in_array('resume-suggest',$employer_heading_values)or empty($employer_heading_values)): ?>
                            <li class="<?php echo esc_attr(Noo_Member::get_actice_enpoint_class('resume-suggest')) ?>">
                                <a href="<?php echo Noo_Member::get_endpoint_url('resume-suggest') ?>">
                                    <i class="fa fa-plus"></i>
                                    <?php _e('Resume Suggest','noo') ?>
                                    <?php echo noo_get_resume_suggest_count(); ?>
                                </a>
                            </li>
                           <?php endif; ?>
                        <?php $can_shortlist_candidate=noo_can_shortlist_candidate() ?>
                        <?php if($can_shortlist_candidate): ?>
							<?php if ( in_array( 'shortlist', $employer_heading_values ) or empty( $employer_heading_values ) ) : ?>
								<li class="<?php echo esc_attr( Noo_Member::get_actice_enpoint_class( 'shortlist' ) ) ?>">
									<a href="<?php echo Noo_Member::get_endpoint_url( 'shortlist' ) ?>">
										<i class="fa fa-heart"></i>
										<?php _e( 'Shortlist', 'noo' ) ?>
									</a>
								</li>
							<?php endif; ?>
                        <?php endif; ?>
							<?php if ( in_array( 'company_profile', $employer_heading_values ) or empty( $employer_heading_values ) ) : ?>
								<li>
									<a href="<?php echo Noo_Member::get_company_profile_url() ?>"><i
											class="fa fa-users"></i> <?php _e( 'Company Profile', 'noo' ) ?></a>
								</li>
							<?php endif; ?>
							<?php if ( in_array( 'signout', $employer_heading_values ) or empty( $employer_heading_values ) ) : ?>
								<li>
									<a href="<?php echo Noo_Member::get_logout_url() ?>">
										<i class="fa fa-sign-out"></i> <?php _e( 'Sign Out', 'noo' ) ?>
									</a>
								</li>
							<?php endif; ?>

							<?php

						// Candidate Menu

						elseif ( Noo_Member::is_candidate() ) : ?>,

							<?php if ( in_array( 'manage-resume', $candidate_heading_values ) or empty( $candidate_heading_values ) ) : ?>
								<?php if ( jm_resume_enabled() ) : ?>
									<li class="<?php echo esc_attr( Noo_Member::get_actice_enpoint_class( array(
										'manage-resume',
										'preview-resume',
										'edit-resume',
									) ) ) ?>"><a
											href="<?php echo Noo_Member::get_endpoint_url( 'manage-resume' ) ?>"><i
												class="fa fa-file-text-o"></i> <?php _e( 'Resumes', 'noo' ) ?></a>
									</li>
								<?php endif; ?>
							<?php endif; ?>

							<?php if ( in_array( 'manage-job-applied', $candidate_heading_values ) or empty( $candidate_heading_values ) ) : ?>
								<li class="<?php echo esc_attr( Noo_Member::get_actice_enpoint_class( 'manage-job-applied' ) ) ?>">
									<a href="<?php echo Noo_Member::get_endpoint_url( 'manage-job-applied' ) ?>"
									   style="white-space: nowrap;"><i
											class="fa fa-newspaper-o"></i> <?php _e( 'Applications', 'noo' ) ?></a>
								</li>
							<?php endif; ?>

							<?php if ( in_array( 'job-alert', $candidate_heading_values ) or empty( $candidate_heading_values ) ) : ?>
								<?php if ( Noo_Job_Alert::enable_job_alert() ) : ?>
									<li class="<?php echo esc_attr( Noo_Member::get_actice_enpoint_class( array(
										'job-alert',
										'add-job-alert',
										'edit-job-alert',
									) ) ) ?>"><a href="<?php echo Noo_Member::get_endpoint_url( 'job-alert' ) ?>"><i
												class="fa fa-bell-o"></i> <?php _e( 'Job Alerts', 'noo' ) ?></a>
									</li>
								<?php endif; ?>

							<?php endif; ?>


							<?php do_action( 'noo-member-candidate-heading' ); ?>

							<?php if ( in_array( 'manage-plan', $candidate_heading_values ) or empty( $candidate_heading_values ) ) : ?>
								<?php if ( jm_is_woo_resume_posting() ) : ?>
									<li class="<?php echo esc_attr( Noo_Member::get_actice_enpoint_class( 'manage-plan' ) ) ?>">
										<a href="<?php echo Noo_Member::get_endpoint_url( 'manage-plan' ) ?>"><i
												class="fa fa-file-text-o"></i> <?php _e( 'Manage Plan', 'noo' ) ?>
										</a></li>
								<?php endif; ?>
							<?php endif; ?>
                            <?php if ( in_array( 'bookmark-job', $candidate_heading_values ) or empty( $candidate_heading_values ) ) : ?>

                                <?php if ( jm_is_enabled_job_bookmark() ): ?>
                                    <li class="<?php echo esc_attr( Noo_Member::get_actice_enpoint_class( 'bookmark-job' ) ) ?>">
                                        <a href="<?php echo Noo_Member::get_endpoint_url( 'bookmark-job' ) ?>">
                                            <i class="fa fa-heart"></i>
                                            <?php _e( 'Bookmarked', 'noo' ) ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                        <?php $can_follow_company = noo_can_follow_company(); ?>
                        <?php if($can_follow_company): ?>
							<?php if ( in_array( 'manage-follow', $candidate_heading_values ) or empty( $candidate_heading_values ) ) : ?>
								<li class="<?php echo esc_attr( Noo_Member::get_actice_enpoint_class( 'manage-follow' ) ) ?>">
									<a href="<?php echo Noo_Member::get_endpoint_url( 'manage-follow' ) ?>">
										<i class="fa fa-plus"></i>
										<?php _e( 'Manage Follow', 'noo' ) ?>
									</a>
								</li>
							<?php endif; ?>
							<?php endif; ?>
							<?php if ( in_array( 'job-follow', $candidate_heading_values ) or empty( $candidate_heading_values ) ) : ?>
								<li class="<?php echo esc_attr( Noo_Member::get_actice_enpoint_class( 'job-follow' ) ) ?>">
									<a href="<?php echo Noo_Member::get_endpoint_url( 'job-follow' ) ?>">
										<i class="fa fa-plus"></i>
										<?php _e( 'Job Follow', 'noo' ) ?>
									</a>
								</li>
							<?php endif; ?>
                        <?php endif; ?>
                            <?php if(in_array('job-suggest',$candidate_heading_values)or empty($candidate_heading_values)): ?>
                                <li class="<?php echo esc_attr(Noo_Member::get_actice_enpoint_class('job-suggest')) ?>">
                                    <a href="<?php echo Noo_Member::get_endpoint_url('job-suggest') ?>">
                                        <i class="fa fa-plus"></i>
                                        <?php _e('Job Suggest','noo') ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php $can_shortlist_candidate=noo_can_shortlist_candidate() ?>
                        <?php if($can_shortlist_candidate): ?>
							<?php if ( in_array( 'shortlist', $candidate_heading_values ) or empty( $candidate_heading_values ) ) : ?>
								<li class="<?php echo esc_attr( Noo_Member::get_actice_enpoint_class( 'shortlist' ) ) ?>">
									<a href="<?php echo Noo_Member::get_endpoint_url( 'shortlist' ) ?>">
										<i class="fa fa-heart"></i>
										<?php _e( 'Shortlist', 'noo' ) ?>
									</a>
								</li>
							<?php endif; ?>
                        <?php endif; ?>

							<?php if ( in_array( 'candidate_profile', $candidate_heading_values ) or empty( $candidate_heading_values ) ) : ?>
								<li>
									<a href="<?php echo Noo_Member::get_candidate_profile_url() ?>"><i
											class="fa fa-user"></i> <?php _e( 'My Profile', 'noo' ) ?></a>
								</li>
							<?php endif; ?>

							<?php if ( in_array( 'signout', $candidate_heading_values ) or empty( $candidate_heading_values ) ) : ?>
								<li>
									<a href="<?php echo Noo_Member::get_logout_url() ?>">
										<i class="fa fa-sign-out"></i> <?php _e( 'Sign Out', 'noo' ) ?>
									</a>
								</li>
							<?php endif; ?>


						<?php endif; ?>


					</ul>
				</div>
			</div>
		</div>
	<?php endif; ?>
<?php
do_action( 'after_heading' );
?>
