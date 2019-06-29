<?php
/**
*Plugin Name: Custom Labels for WordPress Posts
*Plugin Uri: https://github.com/narendr11/custom_labels_for_wp_posts
*Author: Narendra Sishodiya(narenin)
*Author Uri: https://profiles.wordpress.org/narenin/
*Version: 1.0.0
*License: GPLv2 or later
*License URI: http://www.gnu.org/licenses/gpl-2.0.html
*Description: This pluign can be used for adding Custom Labels to Posts, Pages, Projects
*Tags: Custom Tag for Projects, Custom Tag for Pages, Custom Tag for Posts, Label to Admin Screen, Label
*
*/
defined('ABSPATH')||die('You are trying to enter restricted file!');

add_action('admin_init','plugin_custom_label_meta_box');
//Add Meta Box to Posts/Pages/Projects
function plugin_custom_label_meta_box(){
add_meta_box('my_custom_field', "Custom Label", 'plugin_custom_label_meta_box_fun',['post','page','project'], 'side','high');
}
function plugin_custom_label_meta_box_fun($post){
$_metabox_input = get_post_meta($post->ID,'_input_meta_box',true) ? get_post_meta($post->ID,'_input_meta_box',true) : '';
	 ?>
	<h2 style="padding-left:0px">Add a Custom Label here : </h2> 
	<input type="text" id="" name="_input_meta_box" value="<?php echo $_metabox_input; ?>"/>

<?php
}
add_action('save_post','save_meta_box');
function save_meta_box($post_id){
if(array_key_exists('_input_meta_box', $_POST)){
update_post_meta($post_id,'_input_meta_box',$_POST['_input_meta_box']);
}
}
//Add Columns to All Post/Pages/Projects Screen
add_filter( 'manage_posts_columns', 'custom_label_add_id_column', 5 );
add_action( 'manage_posts_custom_column', 'custom_label_id_column_content', 5, 2 );

function custom_label_add_id_column( $columns ) {
   $columns['custom_label'] = 'Custom Label';
   return $columns;
}

function custom_label_id_column_content( $column, $post_id ) {
  if( 'custom_label' == $column ) {
 echo get_post_meta($post_id,'_input_meta_box',true); 
  }
}