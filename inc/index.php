<?php
/* Don't load directly */
if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}
?>
	<div class="astrans-container">
		<div class="astrans-left">
			<div class="astrans-box">
				<section>
					<div class="astrans-content">
						<h2>Setting</h2>

						<div class="astrans_row">
							<div class="astrans_col_left">
								<label>Set Language</label>
								<i>Please select your language</i>
							</div>
							<div class="astrans_col_right">
								<?php global $language_suport; ?>
								<select name="astrans_language">									
									<?php
										$l = get_site_option('astrans_language');
										foreach ($language_suport as $key => $value) { ?>
									<option value="<?php echo $key; ?>" <?php echo $l == $key ? 'selected="selected"' : '';?>><?php echo $value; ?></option>
									<?php } ?>
								</select>
							</div>
						</div>

						<div class="astrans_row">
							<div class="astrans_col_left">
								<label>Automation Translate</label>
								<i>The plugin will automatically translate the slug when posting new posts</i>
							</div>
							<div class="astrans_col_right">
								<?php
									$checked = '';
									if(get_site_option('astrans_status') == '1'){
										$checked = 'checked';
									}
								?>
								<label class="astrans_switch">
									<input type="checkbox" name="astrans_enable" data-checkbox="astrans_enable" <?php echo $checked; ?> />
									<div class="astrans_slider astrans_round"></div>
									<div class="astrans_icon_round astrans_round"></div>
								</label>
								<div class="astrans-save">
									<button class="btn-astrans">Save</button>
								</div>
							</div>
						</div>
						
						<div id="astrans_enable">							

							<div class="astrans_row">
								<div class="astrans_col_left">
									<label>Translate slug for</label>
									<i>Automated translation slug work will be applied to</i>
								</div>
								<div class="astrans_col_right astrans_for">
									<?php
										if(get_site_option('astrans_trans_for') != '' ){									
											$astrans_trans_for = json_decode(str_replace('\"', '"',get_site_option('astrans_trans_for')), true);
										}
									?>
									<input type="checkbox" id="astrans_pages" <?php if(isset($astrans_trans_for['astrans_pages']) && $astrans_trans_for['astrans_pages'] == '1') echo 'checked'; ?>/><label for="astrans_pages" class="label_check">Pages</label>
									<input type="checkbox" id="astrans_posts" <?php if(isset($astrans_trans_for['astrans_posts']) && $astrans_trans_for['astrans_posts'] == '1') echo 'checked'; ?>/><label for="astrans_posts" class="label_check">Posts</label>
									<input type="checkbox" id="astrans_cate" <?php if(isset($astrans_trans_for['astrans_cate']) && $astrans_trans_for['astrans_cate'] == '1') echo 'checked'; ?>/><label for="astrans_cate" class="label_check">Category</label>
								</div>
							</div>
						</div>

					</div>
				</section>
			</div>
			<div id="astrans_loading">
				<img src="<?php echo astrans_plugin_url; ?>/assets/images/loading.png" alt="astrans loading"/>
			</div>
		</div>		
		<div class="astrans-right astrans">
			<div class="astrans-box">
				<div class="astrans-content">
					<?php do_action('astrans_sidebar'); ?>
				</div>
			</div>
			<div class="sidebar"></div>
		</div>
	</div>