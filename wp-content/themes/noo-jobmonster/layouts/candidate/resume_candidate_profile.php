<?php 
global $post;
$user_per = noo_get_user_permission();
$candidate_id = isset($_GET['candidate_id']) ? absint($_GET['candidate_id']) : '';
$enable_upload = (bool) jm_get_resume_setting('enable_upload_resume', '1');
$enable_print = (bool) jm_get_resume_setting('enable_print_resume', '1');
if( get_the_ID() == Noo_Member::get_member_page_id() || jm_is_resume_posting_page() ) {
	$candidate_id = get_current_user_id();
	$resume_id = 0;
} else {
	$resume_id = isset( $_GET['resume_id'] ) ? $_GET['resume_id'] : get_the_ID();
	if( 'noo_resume' == get_post_type( $resume_id ) ) {
		$candidate_id = get_post_field( 'post_author', $resume_id);
	}
}

$file_cv = noo_json_decode( noo_get_post_meta( $post->ID, '_noo_file_cv' ) );
$slogan = noo_get_post_meta( $post->ID, '_slogan' );

$candidate = !empty($candidate_id) ? get_userdata($candidate_id) : false;

if( $candidate ) :
	$fields = jm_get_candidate_custom_fields();
	$all_socials = noo_get_social_fields();
	$socials = jm_get_candidate_socials();
	$email = $candidate ? $candidate->user_email : '';

?>
	<div class="resume-candidate-profile">
		<div class="row">
			<div class="col-sm-3 profile-avatar">
				<?php echo noo_get_avatar( $candidate_id, 160); ?>
			</div>
			<div class="col-sm-9 candidate-detail">
				<div class="candidate-title clearfix">
					<div class="pull-left">
                    <h2>
                        <?php echo esc_html( $candidate->display_name ); ?>
						<?php if( $candidate_id == get_current_user_id() ) : ?>
                            <a class="pull-right resume-action" href="<?php echo esc_url( Noo_Member::get_candidate_profile_url('candidate-profile') ); ?>" title="<?php echo esc_attr__('Edit Profile', 'noo'); ?>">
                                <i class="fa fa-pencil"></i>
                            </a>
						<?php endif; ?>
                    </h2>
                    <?php if ( !empty( $slogan ) ) : ?>
                        <h3 class="resume-slogan">
                            <?php echo esc_html( $slogan ); ?>
                        </h3>
                    <?php endif; ?>
                    </div>
                    <?php   $resume_id=$post->ID;
                            $can_view_resume=jm_can_view_heading_resume($resume_id);?>

					<?php if ( $enable_upload && !empty( $file_cv ) && isset( $file_cv[0] ) && !empty( $file_cv[0] ) ) : ?>
                        <?php if($can_view_resume==true): ?>
                        <a class="btn btn-primary resume-download pull-right" href="<?php echo noo_get_file_upload( $file_cv[0] ); ?>" title="<?php echo esc_attr__('Download CV', 'noo'); ?>">
                            <i class="fa fa-download"></i>
                            <?php echo esc_html__('Download CV', 'noo'); ?>
                        </a>
                        <?php endif; ?>
					<?php endif; ?>
				</div>
				<?php do_action( 'noo_resume_candidate_profile_before', $resume_id ); ?>

				<?php
				// Job's social info
				$socials = jm_get_resume_socials();
                $enable_socials =noo_get_option('noo_resume_social','1');
				$html = array();

				foreach ($socials as $social) {
					if (!isset($all_socials[$social])) continue;
					$data = $all_socials[$social];
					$value = get_post_meta($resume_id, $social, true);
					if (!empty($value)) {
						$url = esc_url($value);
						$html[] = '<a title="' . sprintf(esc_attr__('Connect with us on %s', 'noo'), $data['label']) . '" class="noo-icon fa ' . $data['icon'] . '" href="' . $url . '" target="_blank"></a>';
					}
				}

			 ?>
                    <div class="candidate-social">
                    <?php 	if ( $enable_socials && !empty($html) && count($html) > 0) : ?>
						<?php echo implode("\n", $html); ?>
                    <?php endif; ?>
                        <?php if ( $enable_print ) : ?>
                            <a data-invoice="<?php echo esc_attr($post->ID); ?>"
                               data-total-review="<?php echo (noo_get_total_review($post->ID)) ?>"
                               data-layout ="style-1"
                               data-post-review = "disable"
                               class=" btn-print-invoice print-resume noo-icon" href="#"
                               title="<?php echo esc_attr__('Print', 'noo'); ?>">
                                <i class="fa fa-print"></i>
                            </a>
                        <?php endif; ?>
                    </div>

				<?php if( apply_filters( 'jm_resume_show_candidate_contact', true, $resume_id ) ) : ?>
					<div class="candidate-info">
						<div class="row">
							<?php if( !empty( $fields ) ) : ?>
								<?php foreach ( $fields as $field ) :
									if( isset( $field['is_default'] ) ) {
										if( in_array( $field['name'], array( 'first_name', 'last_name', 'full_name', 'email') ) )
											continue; // don't display WordPress default user fields
									}
									$field_id = jm_candidate_custom_fields_name( $field['name'], $field );
									
									$value = get_user_meta( $candidate->ID, $field_id, true );
									$value_date = noo_convert_custom_field_value( $field, $value );
                                    $icon = isset($field['icon']) ? $field['icon'] : '';
                                    $icon_class = str_replace("|", " ", $icon);

									if( is_array( $value ) ) {
										$value = implode(', ', $value);
									}
                                    $permission = isset($field['permission']) ? $field['permission'] : '';
                                    $is_can_view = false;


                                    if (empty($permission) or 'public' == $permission ) {
                                        $is_can_view = true;
                                    } elseif($permission ==  $user_per){
                                        $is_can_view = true;
                                    }

                                    if ($is_can_view== false) {
                                        continue;
                                    }
									if( !empty( $value ) ) : ?>

										<div class="<?php echo esc_attr( $field_id ); ?> col-sm-6">
											<?php if($field['type'] == 'datepicker' ):
											$date = date('d/M/Y',$value_date);
											?>
												<div class="<?php echo esc_attr( $field_id ); ?>">
													<span class="candidate-field-icon"><i class="<?php echo esc_attr($icon_class) ?>"></i></span>
													 <?php echo $date;?>
												</div>
											<?php else : ?>
												<div class="<?php echo esc_attr( $field_id ); ?>">
													<span class="candidate-field-icon"><i class="<?php echo esc_attr($icon_class) ?>"></i></span>
													 <?php echo $value;  ?>
												</div>
											<?php endif; ?>
										</div>
									<?php endif; ?>

								<?php endforeach; ?>
							<?php endif; ?>
							<?php if( !empty( $email ) ) : ?>
								<div class="email col-sm-6">
                                              <a href="mailto:<?php echo esc_attr($email); ?>">
                                              <span class="candidate-field-icon"><i class="fa fa-envelope text-primary"></i></span><?php echo esc_html($email); ?>
                                        </a>

								</div>
							<?php endif; ?>
						</div>
					</div>
					<?php if( !empty( $candidate->description ) ) : ?>
						<div class="candidate-desc">
							<?php echo $candidate->description; ?>
						</div>
					<?php endif; ?>
				<?php else : ?>
					<?php 
						$private_message = '<strong>' . __('The Candidate\'s contact information is private', 'noo') . '</strong>';
						echo apply_filters( 'noo_resume_candidate_private_message', $private_message, $resume_id );
					?>
				<?php endif; ?>
				<?php do_action( 'noo_resume_candidate_profile_after', $resume_id ); ?>
			</div>
		</div>
	</div>
<?php else: 
	echo '<h2 class="text-center" style="min-height:200px">'.__('Can not find this Candidate !','noo').'</h2>';
endif; ?>
<hr/>