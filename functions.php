<?php

function custom_action_before_add_to_cart($cartid, $product_id, $quantity, $variation_id = 0, $variation = array(), $cart_item_data = array()) {
   
	if( $articleid = get_post_meta( $product_id, 'latakko-article', true ) ) {
		

         $data_to_send = array(
        'id' => $articleid,
      
         );
		
		 // Convert the data to JSON format
   		 $json_data = json_encode($data_to_send);

   		 // Your custom webhook URL
   		 $webhook_url = 'http://www.ipgarage.lv:5678/webhook/643152db-1c82-4546-9a32-d0a68e4e73bd';
    
   		 // Send the data to the webhook endpoint using wp_remote_post
   		 $response = wp_remote_post(
       		 $webhook_url,
       		 array(
           		 'headers' => array(
           		     'Content-Type' => 'application/json',
           		 ),
           		 'body' => $json_data,
					'sslverify' => false
        		)
   		 );
		
	}	
    
    
	return true;
}
add_action('woocommerce_add_to_cart', 'custom_action_before_add_to_cart', 10, 5);

// EP custom REST API endpiont to update product by SKU

add_action('rest_api_init', 'register_custom_endpoint');

function register_custom_endpoint() {
    register_rest_route('custom/v1', '/update-sku/', array(
        'methods' => 'POST',
        'callback' => 'update_product_by_sku',
        'permission_callback' => function () {
            return current_user_can('edit_posts');
        },
    ));
}


function update_product_by_sku(WP_REST_Request $request) {
    $sku = $request->get_param('sku');
    $data = $request->get_json_params(); // Assuming the data for update is sent as JSON

	
	
    // Find the product by SKU
    $product_id = wc_get_product_id_by_sku($sku);

		
	if (!$product_id) {
		
		$product_id = wc_get_product_id_by_sku($data["articleid"]);
		
		//change sku
		if ($product) {
			 update_post_meta($product_id, '_sku', $data["sku"]);	
		     if ($data["articleid"])  update_post_meta($product_id, 'latakko-article', $data["articleid"]);
			
		}
		
	}
	
	if ($product_id) {
	
        // Update the product data
       
		 wp_update_post( array( 'ID' => $product_id, 'post_status' => 'publish' ) );
    
		
        // Update the product price
        update_post_meta($product_id, '_regular_price', $data["price"]);
        update_post_meta($product_id, '_price', $data["price"]);
		if ($data["articleid"])  update_post_meta($product_id, 'latakko-article', $data["articleid"]);
		

        // Update the stock quantity
		update_post_meta($product_id, '_stock', $data["stock"]);
		update_post_meta($product_id, '_manage_stock', true);
		($data["stock"] > 0 ) ? update_post_meta($product_id, '_stock_status', 'instock') : update_post_meta($product_id, '_stock_status', 'outofstock');
		if ($data["articleid"])  update_post_meta($product_id, 'latakko-article', $data["articleid"]);
		if ($data["foreign_stock"])  update_post_meta($product_id, '_foreign_stock', $data["foreign_stock"]);
		if ($data["latakko"])  update_post_meta($product_id, 'latakko', 1);
			
		//Update categories
		//
	    // Add a new field called 'myNewField' to the JSON of the item

		$categs = [];
		switch($data["MainGroupId"]) {
  			//vasras auto   
  			case 10:  $categs = [  17  , 99698  , 99697   ] ;  break;  //parastās vasaras
  			case 13:  $categs = [  18 , 99698  ,  99697   ] ;  break;   //pārstrādātas vasaras
  			case 48:  $categs = [  33 , 99698  ,  99697   ] ;  break;    //collas vasaras

  			//ziemas auto  
  			case 15:  $categs = [  19 , 99697 ] ;  break;   //vissezona
  			case 20:  $categs = [  20 , 99697  ,  99699 ] ;  break;   //ziemas saķere vieglie
  			case 21:  $categs = [  21 , 99697  ,  99699 ] ;  break;   //ziemas ar radzēm vieglie  
  			case 22:  $categs = [  22 , 99697  ,  99699 ] ;  break;   //ziemas radžojamas vieglie     
  			case 23:  $categs = [  23 , 99697  ,  99699 ] ;  break;   //ziemas saķere pārstrādātas vieglie
  			case 24:  $categs = [  24 , 99697  ,  99699 ] ;  break;   //ziemas ar radzēm vieglie pārstrādātas
  			case 49:  $categs = [  34 , 99697  ,  99699 ] ;  break;   //ziemas collas vieglie

  			//busi
  			case 30:  $categs = [ 25  , 99702 , 99701 ] ;  break;   //busi parastas  
  			case 33:  $categs = [ 26  , 99702 , 99701 ] ;  break;   //busi parastas pārstrādātas
  			case 35:  $categs = [ 27  , 99701 ];  break;   //busi vissezonas
  			case 40:  $categs = [ 28  , 99703 , 99701 ] ;  break;   //busi ziemas saķeres
  			case 41:  $categs = [ 29  ,  99703 , 99701] ;  break;   //busi ziemas ar radzēm
  			case 42:  $categs = [ 30  ,  99703 , 99701] ;  break;   //busi ziemas radžojamas
  			
			//kravas
  			case 50:  $categs = [ 35 ] ;  break;   //kravas
    
  			//indagtruck
  			case 66:  $categs = [ 37  ,  99776 ] ;  break;   //traktoru 
  			case 80:  $categs = [ 38  ,  99776 ] ;  break;   //industriālas
  			case 88:  $categs = [ 40  , 99776  ] ;  break;   //industriālas  

			//moto  
  			case 85:  $categs = [ 39 ]  ;  break;   
    
  			//diski
  			case 220:  $categs = [  44  ,  99940 ] ;  break;   //lietie diski
  			case 250:  $categs = [  45 ,   99940 ] ;  break;   //oriģinālie lietie diski   
  			case 210:  $categs = [  41  ,  99706 ] ;  break;   //metāla diski auto  
  			case 215:  $categs = [  42 ,   99706 ] ;  break;   //metāla diski busi       
  			case 216:  $categs = [  43  ,  99706 ] ;  break;   //metāla diski agro    
          
		}
		
		$cats = wp_set_object_terms($product_ID, $categs, 'product_cat');

        return new WP_REST_Response(['success' => true, "id" => $product_id, "cats" => $cats], 200);
    } else 
		return new WP_REST_Response(['error' => 'Product not found', "articleid" => $data["articleid"]], 404);
		
}


//EP form hooks for main filter form with redirects to shop

function redirect_filter_form_auto($form, $args) {
    
	
	$url='/shop?tips=auto-riepas';
	if ($args["diameter"] > 0  )  $url.='&diameter='.$args["diameter"];
	if ($args["aspectratio"] >0 ) $url.='&height='.$args["aspectratio"];
	if ($args["width"] > 0 )   $url.='&width='.$args["width"];
	
	switch  ($args["snowgrip"]) {
		case 0 :  $url.='&sezona=vasaras-riepas'; break;
	    case 1 :  $url.='&sezona=ziemas-riepas'; break;
	    case 2 :  $url.='&sezona=15';  break;  
	}			
	
	//print_r($args);
	//exit;
	
	wp_redirect($url);
	exit;
    return true;
}

add_filter( 'jet-form-builder/custom-filter/sanitize-filter-form', 'redirect_filter_form_auto' , 2, 10);

function redirect_filter_form_busi($form, $args) {
    
	
	$url='/shop?tips=busini';
	if ($args["diameter"] > 0  )  $url.='&diameter='.$args["diameter"];
	if ($args["aspectratio"] >0 ) $url.='&height='.$args["aspectratio"];
	if ($args["width"] > 0 )   $url.='&width='.$args["width"];

	switch  ($args["snowgrip"]) {
		case 0 :  $url.='&sezona=vasaras-businu-riepas';break;
	    case 1 :  $url.='&sezona=ziemas-businu-riepas'; break;
	    case 2 :  $url.='&sezona=35';break;   
	}			
		
	wp_redirect($url);
	exit;
    return true;
}

add_filter( 'jet-form-builder/custom-filter/redirect-filter-form-busi', 'redirect_filter_form_busi' , 2, 10);

function redirect_filter_form_moto($form, $args) {
    
	
	$url='/shop?tips=85';
	if ($args["diameter"] > 0  )  $url.='&diameter='.$args["diameter"];
	if ($args["aspectratio"] >0 ) $url.='&height='.$args["aspectratio"];
	if ($args["width"] > 0 )   $url.='&width='.$args["width"];
	
		
	wp_redirect($url);
	exit;
    return true;
}

add_filter( 'jet-form-builder/custom-filter/redirect-filter-form-moto', 'redirect_filter_form_moto' , 2, 10);

function redirect_filter_form_kravas($form, $args) {
    
	
	$url='/shop?tips=50';
	if ($args["diameter"] > 0  )  $url.='&diameter='.$args["diameter"];
	if ($args["aspectratio"] >0 ) $url.='&height='.$args["aspectratio"];
	if ($args["width"] > 0 )   $url.='&width='.$args["width"];

	
		
	wp_redirect($url);
	exit;
    return true;
}

add_filter( 'jet-form-builder/custom-filter/redirect-filter-form-kravas', 'redirect_filter_form_kravas' , 2, 10);

function redirect_filter_form_diski($form, $args) {
    
	
	$url='/shop?tips=diski';
	if ($args["diameter"] > 0  )  $url.='&diameter='.$args["diameter"];
	if ($args["width"] > 0 )   $url.='&width='.$args["width"];
	if ($args["bolts"] > 0 )   $url.='&bolts='.$args["bolts"];
	if ($args["boltcircle"] > 0 )   $url.='&pcd='.$args["boltcircle"];


	
		
	wp_redirect($url);
	exit;
    return true;
}

add_filter( 'jet-form-builder/custom-filter/redirect-filter-form-diski', 'redirect_filter_form_diski' , 2, 10);

function redirect_filter_form_traktori($form, $args) {
    
	
	$url='/shop?tips=indagtrak';
	if ($args["diameter"] > 0  )  $url.='&diameter='.$args["diameter"];
	if ($args["aspectratio"] >0 ) $url.='&height='.$args["aspectratio"];
	if ($args["width"] > 0 )   $url.='&width='.$args["width"];

	
		
	wp_redirect($url);
	exit;
    return true;
}

add_filter( 'jet-form-builder/custom-filter/redirect-filter-form-traktori', 'redirect_filter_form_traktori' , 2, 10);


function redirect_filter_form_atv($form, $args) {
    
	
	$url='/shop?tips=88';
	if ($args["diameter"] > 0  )  $url.='&diameter='.$args["diameter"];
	if ($args["aspectratio"] >0 ) $url.='&height='.$args["aspectratio"];
	if ($args["width"] > 0 )   $url.='&width='.$args["width"];

	
		
	wp_redirect($url);
	exit;
    return true;
}

add_filter( 'jet-form-builder/custom-filter/redirect-filter-form-atv', 'redirect_filter_form_atv' , 2, 10);
