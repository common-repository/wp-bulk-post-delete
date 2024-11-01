<?php
  /*
    Plugin Name:WP Bulk Post Delete
    Version: 1.0
    Author: Numetriclabz
    Author URI: http://www.numetriclabz.com/
    Description: This plugin delete posts according to your choice.
   */
  
  add_action('admin_menu', 'bulk_post_delete');
  function bulk_post_delete(){
          add_menu_page( 'Detail', 'WP Post Delete', 'manage_options', 'bulk_post_delete-plugin', 'html_info' );
  }

  function html_info() {
    global $wpdb, $wp_version, $post;
    $theme_data = wp_get_theme();
    $theme      = $theme_data->Name . ' ' . $theme_data->Version;
  ?>

<div class="wrap">
  <h2>Wordpress Delete by Category and Author</h2>
</div>

<h4><?php
  _e('Select the categories from which you want to delete posts', 'bulk-delete');
  ?></h4>

<p><?php
  _e('Note: The post count below for each category is the total number of posts in that category, irrespective of post type', 'bulk-delete');
  ?></p>

<?php
  $categories = get_categories(array(
      'hide_empty' => false
  ));
  ?>

<form method="post">
  <?php
    $counter = 1;
    ?>
  <?php 
    
    foreach ($categories as $category) { 
    ?>
      <input onclick="getValue(this.value)" type="checkbox" name="menu[];
      ?>"  class="menu<?php
            echo $counter;
      ?>"  id="menu<?php
              echo $counter;
      ?>"  value="<?php
            echo $category->cat_ID;
      ?>"><?php
            echo $category->cat_name, ' (', $category->count, ' ', __('Posts', 'bulk-delete'), ')';     
    ?></input>

  <?php
    $counter++; 
    }
    ?>

  <div class="form-status">
    <h2>Post status</h2>
    <input type="checkbox" name="publish" > Published &nbsp;&nbsp;<input type="checkbox" name="future"> Scheduled &nbsp;&nbsp; <input type="checkbox" name="pending"> Pending &nbsp;&nbsp; <input type="checkbox" name="draft"> Draft &nbsp;&nbsp; <input type="checkbox" name="private"> Private
  </div>
  <?php
    if (version_compare($wp_version, '2.9', '>=')) {
    
    ?>
  <div class="wpdropdown">
  <h2>Select Author</h2>
  <?php 
  $authors = wp_dropdown_users(array('name' => 'author'));?>
  <p><?php
  _e('Select the Author whose posts you want to delete', 'bulk-delete');
  ?></p>
  </div>

  <div class="form-trash">
    <h2>Bypass trash</h2>
    <input type="checkbox" name="force_delete"> 
    <p class="description">Enable this option to completely delete the specified posts.</p>
  </div>

  <?php
    }
    $token1 = rand();          
    ?>

  <input type="hidden" name="wpmd_token1" value="<?php
    echo $token1;
    ?>" />
  <input type="submit" id="sub" class="sub button-primary" value="Delete the posts"  onclick="func();" />&nbsp;&nbsp;
  <script>
    function func()
    {
      var retVal = confirm("Delete? , This action cannot be undone");
    if( retVal == true ){         
      document.getElementById("sub").setAttribute("name", "delete");
      return true;
    }
    else{
      e.preventdefault();
      return false;
    }
    
    }
  </script>
   

  <input type="button" name="cancel" value="Cancel" class="button" onclick="javascript:history.go(-1)" />
</form>

<?php
  if (isset($_POST['delete']) && ($_POST['wpmd_token1'] == get_option('wpmd_token1'))) {
      $type = array();
      $type = implode(",", $_POST["menu"]);
  
  $status = array();
   if (@$_POST ['publish'] == "on") {
       $status [] .= "'publish'";
   }
   if (@$_POST ['pending'] == "on") {
       $status [] .= "'pending'";
   }
   if (@$_POST ['draft'] == "on") {
       $status [] .= "'draft'";
   }
   if (@$_POST ['private'] == "on") {
       $status [] .= "'private'";
   }
   if (@$_POST ['future'] == "on") {
       $status [] .= "'future'";
   }

  if ($_POST ['author'] != "") {
      $author = $wpdb->escape($_POST ['author']);
  }
  
  $query = new WP_Query( array( 'cat' => $type , 'posts_per_page' => -1, 'post_status' => $status, 'author'=> $author ) );
  // print_r($query);
  $jset = array();
  $json =  json_encode( array_values( $query->get_posts()));
  $jset = json_decode( $json, true );
  
  $a = array();
  $i =0;
  while ( $query->have_posts() ) { 
  $query->the_post();
  
  $a[] = $jset[$i]['ID'];
  
  $i++;
  }
  
  
  
  if ((count($type) >=  "1" ) || count($status) >= "1" ) {
     
  
       $post_ids = $a;
  
  
   
  
       $cnt = count($post_ids);
    
       if ($cnt) {
           echo "<br \><div id=\"message\" class=\"updated fade\">Deleting <strong>$cnt</strong> items...";
           foreach ($post_ids as $id) {
               if (($_POST['force_delete'])) {
                   wp_delete_post($id, @$_POST['force_delete'] == "on");
               } else {
                   wp_delete_post($id);
               }
           }
           echo "Done!</div><br \>";
           return;
       }
       
   }
   echo "<br \><div id=\"message\" class=\"updated fade\">Nothing to Delete!</div><br \>";
   
  }
  
  update_option('wpmd_token1', $token1);
  
  }
  
?>