<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<style type="text/css">
	 .import_form .section_head {
	 	margin-top: 30px;
	 	margin-bottom: 0px;
	 }
	 .import_form div{
	 	margin: 10px 0px;
	 	padding: 0px 10px;
	 }
	 .log_head{
	 	font-weight: bold;
	 }
	 .import_frm_wrap{
	 

	 }
	 .import_img_wrap img{
	 	border: 1px solid #ccc;
	 	padding: 10px;
	 	margin: 20px;
	 }
	 .import_img_wrap div{
	 	text-align: center;
	 	font-size: large;
	 	padding: 8px;
	 	border-bottom: 1px solid #ccc;
	 }
	 #pref_screenshots{
	 	display: none;
	 }
	 .clear{
	 	clear: both;
	 }
</style>
<script type="text/javascript">
	jQuery(function(){
		jQuery('#pref_screenshots_btn').click(function(){
			jQuery('#pref_screenshots').toggle();
		});
		jQuery('.import_form button').click(function(){
			var frm = jQuery('#' + jQuery(this).attr('data-form'));
			if(frm.find('.clear_data:checked').length > 0){
				var okay = confirm('Are you sure you want to clear item data? This will also delete existing user preferences.');
				if(okay){
					frm.submit();
				}
			}
			else{
				frm.submit();
			}
		});
	});
</script>
<div class="wrap">
	<h2>My Preferences - Import Items </h2>
	<div class="update-nag">
		Column headings ['name', 'type', 'image', 'category'] must be included as shown in the screenshots below.
		Columns order not necessary.
		<a href="#" id="pref_screenshots_btn">Examples</a>
	</div>
	<?php 
	if($savedMsg === true){
	?> 
	<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
		<p><strong>Items imported successfully.</strong></p>
		<?php if($log){ echo _e($log); } ?>
	</div>
	<?php 
	}
	elseif($savedMsg === false){
	?>
	
	<div class="error below-h2">
		<p>
			<strong>Error Saving Settings.</strong>.
			<?php if($log){ echo _e($log); } ?>
		</p>
	</div>
	<?php }?>

	<div id="pref_screenshots">
		<h3>Sample CSV (screenshots) </h3>
		<div class="import_img_wrap">
			<div>On Spreadsheet</div>
			<img title="Categories" src="<?php echo esc_url($imgUrl); ?>/cats_sheet.png">
			<img title="Items" src="<?php echo esc_url($imgUrl); ?>/items_sheet.png">
		</div>
		<div class="import_img_wrap">
			<div>On A Text Editor</div>
			<img title="Categories" src="<?php echo esc_url($imgUrl); ?>/cats_txt.png">
			<img title="Items" src="<?php echo esc_url($imgUrl); ?>/items_txt.png">
		</div>
	</div>

	<form action="" id="import_cats_form" class="import_form" method="post" enctype="multipart/form-data">
		<input type="hidden" name="myprefs_import_cats" value="1"/>
		<h3 class="section_head">Import Categories</h3>
		<div class="import_frm_wrap">
			<div>
				<label>
					<input type="checkbox" value="1" class="clear_data" name="clear_data" />
					Clear all existing data before importing categories.
				</label>
			</div>
			<div>
				<input type="file" name="csv" />
			</div>
			<div>
				<button type="button" data-form="import_cats_form" class="button button-primary">Import Categories</button>
			</div>
		</div>
		
		<div class="clear"></div>
	</form>

	<form action="" id="import_items_form" class="import_form" method="post" enctype="multipart/form-data">
		<input type="hidden" name="myprefs_import_items" value="1"/>
		<h3 class="section_head">Import Items</h3>
		<div>
			<label>
				<input type="checkbox" value="1" class="clear_data" name="clear_data" />
				Clear all existing items before importing.
			</label>
		</div>
		<div>
			<input type="file" name="csv" />
		</div>
		<div>
			<button type="button" data-form="import_items_form" class="button button-primary">Import Items</button>
		</div>
	</form>
	
</div>