<?php
    /**
     * @package ArafatPluginAwesome
     */
   /*
   Plugin Name: Awesome Plugin
   Plugin URI: http://my-awesomeness-emporium.com
   description: custom post type plugin, creates a custom post type in admin panel
   Version: 1.2
   Author: Mollik
   Author URI: http://mrtotallyawesome.com
   License: GPL2
   */
  defined('ABSPATH') or die('Error');


      
      
     function activate(){
          custom_post_type();
          flush_rewrite_rules( ); //when deactivated the plugin disappears
     } 
     function deactivate(){
          flush_rewrite_rules( );
     }
     //Custom post type function
     function custom_post_type(){
        $labels = array(
          'name'               => _x( 'Products', 'post type general name' ),
          'singular_name'      => _x( 'Product', 'post type singular name' ),
          'add_new'            => _x( 'Add New', 'book' ),
          'add_new_item'       => __( 'Add New Product' ),
          'edit_item'          => __( 'Edit Product' ),
          'new_item'           => __( 'New Product' ),
          'all_items'          => __( 'All Products' ),
          'view_item'          => __( 'View Product' ),
          'search_items'       => __( 'Search Products' ),
          'not_found'          => __( 'No products found' ),
          'not_found_in_trash' => __( 'No products found in the Trash' ), 
          
          'menu_name'          => 'Products'
        );
        $args = array(
          'labels'        => $labels,
          'description'   => 'Holds our products and product specific data',
          'public'        => true,
          'menu_position' => 5,
          'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments' ),
          'has_archive'   => true,
        );
        register_post_type( 'product', $args );
    }

    //messeges for displaying in different actions
    function my_updated_messages( $messages ) {
        global $post, $post_ID;
        $messages['product'] = array(
          0 => ’, 
          1 => sprintf( __('Product updated. <a href="%s">View product</a>'), esc_url( get_permalink($post_ID) ) ),
          2 => __('Custom field updated.'),
          3 => __('Custom field deleted.'),
          4 => __('Product updated.'),
          5 => isset($_GET['revision']) ? sprintf( __('Product restored to revision from %s'), wp_post_revision_title( (int) $_GET['revision'], false ) ) : false,
          6 => sprintf( __('Arafat Published a product. <a href="%s">View product</a>'), esc_url( get_permalink($post_ID) ) ),
          7 => __('Product saved.'),
          8 => sprintf( __('Product submitted. <a target="_blank" href="%s">Preview product</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
          9 => sprintf( __('Product scheduled for: <strong>%1$s</strong>. <a target="_blank" href="%2$s">Preview product</a>'), date_i18n( __( 'M j, Y @ G:i' ), strtotime( $post->post_date ) ), esc_url( get_permalink($post_ID) ) ),
          10 => sprintf( __('Product draft updated. <a target="_blank" href="%s">Preview product</a>'), esc_url( add_query_arg( 'preview', 'true', get_permalink($post_ID) ) ) ),
        );
        return $messages;
    
    }
    add_filter( 'post_updated_messages', 'my_updated_messages' );
    /*  //Deprecated: contextual_help is deprecated since version 3.3.0! 
    Its not usefull anymore.

    ////////////////////////////////////////////////////////////////////
    function my_contextual_help( $contextual_help, $screen_id, $screen ) { 
        if ( 'product' == $screen->id ) {
      
          $contextual_help = '<h2>Products</h2>
          <p>Products show the details of the items that we sell on the website. You can see a list of them on this page in reverse chronological order - the latest one we added is first.</p> 
          <p>You can view/edit the details of each product by clicking on its name, or you can perform bulk actions using the dropdown menu and selecting multiple items.</p>';
      
        } elseif ( 'edit-product' == $screen->id ) {
      
          $contextual_help = '<h2>Editing products</h2>
          <p>This page allows you to view/modify product details. Please make sure to fill out the available boxes with the appropriate details (product image, price, brand) and <strong>not</strong> add these details to the product description.</p>';
      
        }
        return $contextual_help;
      }
      add_action( 'contextual_help', 'my_contextual_help', 10, 3 );
    
      */
      ////////////////////////////////////////////////////////////////////////
      //Taxonomoy
      function my_taxonomies_product() {
        $labels = array(
          'name'              => _x( 'Product Categories', 'taxonomy general name' ),
          'singular_name'     => _x( 'Product Category', 'taxonomy singular name' ),
          'search_items'      => __( 'Search Product Categories' ),
          'all_items'         => __( 'All Product Categories' ),
          'parent_item'       => __( 'Parent Product Category' ),
          'parent_item_colon' => __( 'Parent Product Category:' ),
          'edit_item'         => __( 'Edit Product Category' ), 
          'update_item'       => __( 'Update Product Category' ),
          'add_new_item'      => __( 'Add New Product Category' ),
          'new_item_name'     => __( 'New Product Category' ),
          'menu_name'         => __( 'Product Categories' ),
        );
        $args = array(
          'labels' => $labels,
          'hierarchical' => true,
        );
        register_taxonomy( 'product_category', 'product', $args );
      }
      add_action( 'init', 'my_taxonomies_product', 0 );
      //Add meta box for price 
      add_action( 'add_meta_boxes', 'product_price_box' );
    function product_price_box() {
    add_meta_box( 
        'product_price_box',
        __( 'The Product Price', 'myplugin_textdomain' ),
        'product_price_box_content',
        'product',
        'side',
        'high'
    );
    }
    function product_price_box_content( $post ) {
        wp_nonce_field( plugin_basename( __FILE__ ), 'product_price_box_content_nonce' );
        echo '<label for="product_price"></label>';
        echo '<input type="text" id="product_price" name="product_price" placeholder="enter a price" />';
    }
    add_action( 'save_post', 'product_price_box_save' );
    function product_price_box_save( $post_id ) {

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
        return;

        if ( !wp_verify_nonce( $_POST['product_price_box_content_nonce'], plugin_basename( __FILE__ ) ) )
        return;

        if ( 'page' == $_POST['post_type'] ) {
            if ( !current_user_can( 'edit_page', $post_id ) )
            return;
        } else {
            if ( !current_user_can( 'edit_post', $post_id ) )
            return;
        }
        $product_price = $_POST['product_price'];
        update_post_meta( $post_id, 'product_price', $product_price );
    }
      

  
  register_activation_hook( __FILE__, 'custom_post_type' );
  register_deactivation_hook( __FILE__, 'custom_post_type' );
  add_action( 'init', 'custom_post_type' );
  
  

?>