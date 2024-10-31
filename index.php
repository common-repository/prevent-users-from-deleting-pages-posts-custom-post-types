<?php  
/* 
Plugin Name: Disable Pages & Posts Delete
Plugin URI: 
Version: 0.1 
Author: Ravi Shakya
Description: This plugin disable posts and pages delete all users excepts administrator.
*/  

// create custom plugin settings menu
add_action('admin_menu', 'disable_menu');

function disable_menu() {

	//create new top-level menu
	add_options_page('Disable Delete Posts & Pages', 'Disable Delete', 'administrator','disable_delete', 'disable_delete_for_users');

	//call register settings function
	add_action( 'admin_init', 'disable_mysettings' );
}

function disable_mysettings() {
	//register our settings
	register_setting( 'disable-settings-group', 'disable_pages' );
	register_setting( 'disable-settings-group', 'disable_message' );
	register_setting( 'disable-settings-group', 'disable_admin' );
	
}

function disable_delete_for_users(){ 

	$selected = get_option('disable_pages');
	$post_type = get_post_types(); 
	
	// *************************************************************************************
	// Remove Post Type From Array
	// *************************************************************************************
	
		unset($post_type['attachment']);
		unset($post_type['revision']);
		unset($post_type['nav_menu_item']); ?>
        
        <div class="wrap">
            <h2>Disable Posts & Pages Delete For Users</h2>
            <form method="post" action="options.php">
				<?php settings_fields( 'disable-settings-group' ); ?>
            	<?php do_settings_sections( 'disable-settings-group' ); ?>
                <table class="form-table">
                    <?php foreach($post_type as $post_type): 
					
					// Get all post type attributes (eg. name, labels etc......)
					$post_type_attr = get_post_type_labels(get_post_type_object( $post_type )); ?>   
                     
                    <tr valign="top">
                        <th scope="row">Select <?php echo $post_type_attr->name; ?> To Disable Delete
                        <br><br>
                        <a class="select_all" href="javascript:void(0)">Select all <?php echo $post_type_attr->name; ?></a>
                        <br><br>
                        <a class="unselect_all" href="javascript:void(0)">Unselect all <?php echo $post_type_attr->name; ?></a>
                        
                        </th>
                        <td>
                            <?php 
                            $args = array(
                                'post_type' => $post_type,
                                'order' => 'ASC',
                                'orderby' => 'title',
                                'showposts' => -1
                            );
                            $query = new WP_Query($args);
                            if($query->have_posts()):
                                echo '<ul class="' . $post_type . '">';
                                while($query->have_posts()): $query->the_post();
                                    $page_id = get_the_ID();
                                    
									if($selected):
										if(in_array($page_id,$selected)){
											$check = 'checked';
											}
										else{
											$check = '';
											}
									endif;
                                    
                                    echo '<li>';
									echo '<label><input name="disable_pages[]" type="checkbox" value="'. get_the_ID() .'"'. $check .'>' ; the_title(); echo '</label>';
								
									echo '</li>';
                                endwhile;
                                echo '</ul>';
                            endif;
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <table class="form-table">
                	<tr valign="top">
                    	<th scope="row">
                        	Custom Error Message
                        </th>
                        <td>
                        	<textarea type="text" name="disable_message" name="disable_message" /><?php echo get_option('disable_message'); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                    	<th scope="row">
                        	Enable This Plugin For The Administrators Also
                        </th>
                    	<td>
                        
							<label>
                             <input name="disable_admin" type="checkbox" value="enable" <?php checked( get_option('disable_admin'), enable ); ?> > 
                            </label>
                        	<br><br>
                            <div class="admin_disable_notice">
                           	 <strong>NOTE</strong> : Users with administrator roles can delete everything. If <i><span style="color:#f00;">Checked</span></i>, administrators also cannot delete anything selected above. If administrator wants to delete posts/pages then he/she should firstly unchecked this or disable this plugin. 
                             </div>
                        <td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <script>
			$ = jQuery.noConflict();
			$(document).on('click','.select_all',function(){
				//console.log($(this).closest('th').next().find('ul li'));
				var select_all = $(this).closest('th').next().find('ul li');
				select_all.each(function(index, element) {
                    console.log($(this).find('input:checkbox'));
					$(this).find('input:checkbox').attr('checked','checked');
                });
			});
			
			$(document).on('click','.unselect_all',function(){
				//console.log($(this).closest('th').next().find('ul li'));
				var select_all = $(this).closest('th').next().find('ul li');
				select_all.each(function(index, element) {
                    console.log($(this).find('input:checkbox'));
					$(this).find('input:checkbox').removeAttr('checked');
                });
			});
			
		</script>
        <style>
		
			.admin_disable_notice {
				background: none repeat scroll 0 0 #fff;
				border-left: 5px solid #48D1CC;
				font-size: 12px;
				padding: 10px 15px;
			}
			a.select_all, a.unselect_all {
				font-size: 12px;
				font-weight: normal;
				text-decoration: none;
			}
			.form-table tr {
				border: 1px solid #ccc;
			}
			th {
				border-right: 1px solid #ccc;
				padding-left: 20px !important;
			}
			td{padding-left: 30px !important;}
			
			.form-table textarea {
				width: 100%;
			}

		</style>
<?php }

function restrict_post_deletion($post_ID){
	
	//*************************************
	// Start Get administrators
	//*************************************
	
		$args = array(
			'role' => 'administrator'
		);
		$blogusers = get_users($args);
		
	//*************************************
	// End Get administrators
	//*************************************
	
	//********************************************
	// Start Get administrator ID's into an array
	//********************************************
	
		$all_blog_users = array();
		foreach($blogusers as $alluser){
			$all_blog_users[] = $alluser->ID;
		}
		
	//********************************************
	// End Get administrator ID's into an array
	//********************************************
	
	//*******************************************
	//  Start Get all users except administrator
	//*******************************************
		
		if(!get_option('disable_admin')){ // If has value the it will include administrator as well
			$args = array(
				'exclude' => $all_blog_users
			);
		}
		
		$blogusers = get_users($args);
		$users = array();
		foreach($blogusers as $user_id){
			$users[] = $user_id->ID ;
			}
			
	//*******************************************
	//  Start Get all users except administrator
	//*******************************************

    $user = get_current_user_id();
    $restricted_users = $users; // Get all users excepts administrator
    $restricted_pages = get_option('disable_pages'); // Cannot delete these pages
    if(in_array($user, $restricted_users) && in_array($post_ID, $restricted_pages)){
		
		if(get_option('disable_message')){
			echo '<h1>' . get_option('disable_message') . '</h1>';
			}
		else {
        	echo "<h1>You are not authorized to delete this page. Please Contact your administrator</h1>";
		}
        exit;
    }
}
add_action('wp_trash_post', 'restrict_post_deletion', 10, 1);
add_action('before_delete_post', 'restrict_post_deletion', 10, 1);