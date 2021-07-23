<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<style type="text/css">
	 .mypref_lg_input {
	 	width: 400px;
	 }
	 input.mypref_lg_input{
	 	
	 }
	 textarea.mypref_lg_input{
	 	height: 250px;
	 	width: 550px;
	 }
	 .mypref_frm_group{
	 	padding: 10px 20px;
	 	margin: 0px 20px;
	 	margin-bottom: 15px;
	 	width: 60%;
	 	min-width: 600px;
	 }
</style>
<div class="wrap">
	<h2>My Preferences - General Settings</h2>
	<?php 
	if($savedMsg === true){
	?> 
	<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
		<p><strong>Settings Saved</strong></p>
	</div>
	<?php 
	}
	elseif($savedMsg === false){
	?>
	
	<div class="error below-h2">
		<p>
			<strong>Error Saving Settings.</strong>.
		</p>
	</div>
	<?php }?>

	<form action="" method="post" enctype="multipart/form-data">
		<input type="hidden" name="myprefs_save_settings" value="1"/>

		<h3>Images URL</h3>		
		<div class="mypref_frm_group">
			<label><strong><?php echo esc_url($home_url); ?></strong></label>
			<input  class="mypref_lg_input" type="text" name="images_url" value="<?php echo esc_url($images_url); ?>" />
		</div>

	<button type="submit" class="button button-primary"> Save Changes</button>
	</form>
</div>