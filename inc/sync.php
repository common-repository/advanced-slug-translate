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
						<h2>Sync Slug</h2>
						<?php
							//echo convert_uuencode('1');
							//echo convert_uudecode("!,0`` `");
						?>
						<div class="astrans_row">
							<div class="astrans_col_left">
								<label>Select type to translate slug</label>
								<i>Note : Post and Category only of Post Type <b>POST</b></i>
							</div>
							<div class="astrans_col_right">
								<select name="astrans_sync_type">
									<option value="0">Select</option>
									<option value="page">Pages</option>
									<option value="post">Posts</option>
									<option value="category">Category</option>
								</select>
							</div>
						</div>
						<div class="astrans_row">
							<div class="astrans_col_left">
								<label>Filter</label>
							</div>
							<div class="astrans_col_right astrans_for">
								<div class="astrans_row">
									<input type="radio" name="astran_filter" value="0" id="astran_filter_0" checked/><label for="astran_filter_0">Slug not translated</label>
									<input type="radio" name="astran_filter" value="1" id="astran_filter_1"/><label for="astran_filter_1">Slug translated</label>
									<input type="radio" name="astran_filter" value="2" id="astran_filter_2"/><label for="astran_filter_2">All</label>
									</div>																
							</div>
						</div>
						<div class="astrans_row">
							<div class="astrans_row">
								<div class="astrans_search">
									<input type="text" placeholder="Search Title"/>
									<button class="btn-astrans">Search</button>
									<select name="astrans_paged">
										<option value="10">10</option>
										<option value="20">20</option>
										<option value="30">30</option>
										<option value="50">50</option>
										<option value="100">100</option>
										<option value="0">All</option>
									</select>
								</div>
							</div>							
							<table class="widefat" id="astrans_sync">
								<thead>
									<tr>
										<th width="3%"><input type="checkbox" class="astrans_tr_all"></th>
										<th width="18%">Title</th>
										<th width="30%">Slug Old</th>
										<th >Slug New</th>
										<th width="2%"></th>
										<th width="10%"></th>
									</tr>
								</thead>
								<tbody>
									<tr class="astrans_loading">
							            <td colspan="10">
							                <div id="astrans_loading2">
							                    <img src="<?php echo astrans_plugin_url; ?>/assets/images/loading.png" alt="astrans loading"/>
							                </div>
							            </td>
							        </tr>
								</tbody>									
								<tfoot>
									<tr>
										<th><input type="checkbox" class="astrans_tr_all"></th>
										<th>Title</th>
										<th>Slug Old</th>
										<th>Slug New</th>
										<th></th>
										<th></th>
									</tr>
								</tfoot>
							</table>
							<div class="astrans_row">
								<button class="astrans_save_all btn-astrans">Apply</button>
							</div>							
						</div>

						<div class="astrans_row">
							<div id="astrans_navi">
								
							</div>
						</div>
					</div>
				</section>
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
