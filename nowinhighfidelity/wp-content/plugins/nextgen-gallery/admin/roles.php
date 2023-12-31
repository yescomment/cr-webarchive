<?php

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

function nggallery_admin_roles()  {

if ( isset($_POST['update_cap']) ) {	

	check_admin_referer('ngg_addroles');

	// now set or remove the capability
	ngg_set_capability($_POST['general'],"NextGEN Gallery overview");
	ngg_set_capability($_POST['tinymce'],"NextGEN Use TinyMCE");
	ngg_set_capability($_POST['add_gallery'],"NextGEN Upload images");
	ngg_set_capability($_POST['manage_gallery'],"NextGEN Manage gallery");
	ngg_set_capability($_POST['manage_others'],"NextGEN Manage others gallery");
	ngg_set_capability($_POST['manage_tags'],"NextGEN Manage tags");
	ngg_set_capability($_POST['edit_album'],"NextGEN Edit album");
	ngg_set_capability($_POST['change_style'],"NextGEN Change style");
	ngg_set_capability($_POST['change_options'],"NextGEN Change options");
	
	nggGallery::show_message(__('Updated capabilities',"nggallery"));
}
	
?>
	<div class="wrap">
	<h2><?php _e('Roles / capabilities', 'nggallery') ;?></h2>
	<p><?php _e('Select the lowest role which should be able to access the follow capabilities. NextGEN Gallery supports the standard roles from WordPress.', 'nggallery') ?> <br />
	   <?php _e('For a more flexible user management you can use the', 'nggallery') ?> <a href="http://www.im-web-gefunden.de/wordpress-plugins/role-manager/" target="_blank">Role Manager</a>.</p>
	<form name="addroles" id="addroles" method="POST" accept-charset="utf-8" >
		<?php wp_nonce_field('ngg_addroles') ?>
			<table class="form-table"> 
			<tr valign="top"> 
				<th scope="row"><?php _e('Main NextGEN Gallery overview', 'nggallery') ;?>:</th> 
				<td><label for="general"><select name="general" id="general"><?php wp_dropdown_roles( ngg_get_role('NextGEN Gallery overview') ); ?></select></label></td>
			</tr>
			<tr valign="top"> 
				<th scope="row"><?php _e('Use TinyMCE Button / Upload tab', 'nggallery') ;?>:</th> 
				<td><label for="tinymce"><select name="tinymce" id="tinymce"><?php wp_dropdown_roles( ngg_get_role('NextGEN Use TinyMCE') ); ?></select></label></td>
			</tr>
			<tr valign="top"> 
				<th scope="row"><?php _e('Add gallery / Upload images', 'nggallery') ;?>:</th> 
				<td><label for="add_gallery"><select name="add_gallery" id="add_gallery"><?php wp_dropdown_roles( ngg_get_role('NextGEN Upload images') ); ?></select></label></td>
			</tr>
			<tr valign="top"> 
				<th scope="row"><?php _e('Manage gallery', 'nggallery') ;?>:</th> 
				<td><label for="manage_gallery"><select name="manage_gallery" id="manage_gallery"><?php wp_dropdown_roles( ngg_get_role('NextGEN Manage gallery') ); ?></select></label></td>
			</tr>
			<tr valign="top"> 
				<th scope="row"><?php _e('Manage others gallery', 'nggallery') ;?>:</th> 
				<td><label for="manage_others"><select name="manage_others" id="manage_others"><?php wp_dropdown_roles( ngg_get_role('NextGEN Manage others gallery') ); ?></select></label></td>
			</tr>
			<tr valign="top"> 
				<th scope="row"><?php _e('Manage tags', 'nggallery') ;?>:</th> 
				<td><label for="manage_tags"><select name="manage_tags" id="manage_tags"><?php wp_dropdown_roles( ngg_get_role('NextGEN Manage tags') ); ?></select></label></td>
			</tr>
			<tr valign="top"> 
				<th scope="row"><?php _e('Edit Album', 'nggallery') ;?>:</th> 
				<td><label for="edit_album"><select name="edit_album" id="edit_album"><?php wp_dropdown_roles( ngg_get_role('NextGEN Edit album') ); ?></select></label></td>
			</tr>
			<tr valign="top"> 
				<th scope="row"><?php _e('Change style', 'nggallery') ;?>:</th> 
				<td><label for="change_style"><select name="change_style" id="change_style"><?php wp_dropdown_roles( ngg_get_role('NextGEN Change style') ); ?></select></label></td>
			</tr>
			<tr valign="top"> 
				<th scope="row"><?php _e('Change options', 'nggallery') ;?>:</th> 
				<td><label for="change_options"><select name="change_options" id="change_options"><?php wp_dropdown_roles( ngg_get_role('NextGEN Change options') ); ?></select></label></td>
			</tr>
			</table>
			<div class="submit"><input type="submit" class="button-primary" name= "update_cap" value="<?php _e('Update capabilities', 'nggallery') ;?>"/></div>
	</form>
	</div>
<?php 

}

function ngg_get_role($capability){
	// This function return the lowest roles which has the capabilities
	$check_order = array("subscriber", "contributor", "author", "editor", "administrator");

	$args = array_slice(func_get_args(), 1);
	$args = array_merge(array($capability), $args);

	foreach ($check_order as $role) {
		$check_role = get_role($role);
		
		if ( empty($check_role) )
			return false;
			
		if (call_user_func_array(array(&$check_role, 'has_cap'), $args))
			return $role;
	}
	return false;
}

function ngg_set_capability($lowest_role, $capability){
	// This function set or remove the $capability
	$check_order = array("subscriber", "contributor", "author", "editor", "administrator");

	$add_capability = false;
	
	foreach ($check_order as $role) {
		if ($lowest_role == $role)
			$add_capability = true;
			
		$the_role = get_role($role);
		
		// If you rename the roles, the please use the role manager plugin
		
		if ( empty($the_role) )
			continue;
			
		$add_capability ? $the_role->add_cap($capability) : $the_role->remove_cap($capability) ;
	}
	
}

?>