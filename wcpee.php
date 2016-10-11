<?php
/*
Plugin Name: WooCommerce Product External Extanshion
Plugin URI: https://github.com/systemo-biz/wcpee
Description: This is not just a plugin, it symbolizes the hope and enthusiasm of an entire generation summed up in two words sung most famously by Louis Armstrong: Hello, Dolly. When activated you will randomly see a lyric from <cite>Hello, Dolly</cite> in the upper right of your admin screen on every page.
Author: Systemo.biz
Version: 1.7
Author URI: http://systemo.biz/
*/



function wpcee_add_rewrite_endpoint() {
  add_rewrite_endpoint( $name = 'gurl', EP_PERMALINK );
}
add_action('init', 'wpcee_add_rewrite_endpoint');


function wcpee_change_url($url, $product){

  //$post=get_post();
  //var_dump('sdfsdfsdfsdfsdf');
  //var_dump($product);

  if(! empty($url)){
    //$url = add_query_arg( array('gourl' => 'go-url'));

    $url = get_permalink($product->id) . 'gurl/';
  }

  return $url;

}
add_filter('woocommerce_product_add_to_cart_url', 'wcpee_change_url', 10, 2);



function wcpee_redirect_url(){

  if( ! is_singular( $post_types = 'product' ))
    return;

  global $wp;

  if( ! isset($wp->query_vars["gurl"]))
    return;

  //Получаем исходные данные для работы
  $post = get_post();
  //$pf = new WC_Product_Factory();
  $product = wc_get_product();
  $url = $product->get_product_url();

  //Уточняем число переходов в метаполе если адрес есть и будет переход
  if(isset($url)){
    $count = get_post_meta($product->id, 'wcpee_count', true);
    if(isset($count)) {
      $count = $count +1;
      update_post_meta($product->id, 'wcpee_count', $count);
    } else {
      update_post_meta($product->id, 'wcpee_count', 1);
    }
  }

  //Переходим по ссылке
  wp_redirect($url, 301);
  die;

  return;

}
add_action( 'template_redirect', 'wcpee_redirect_url');




//Добавляем метабокс, который показывает число переходов
add_action('add_meta_boxes', function(){
  add_meta_box( 'wcpee_count', 'Переходы по ссылке', 'wcpee_count_metabox_cb', 'product', 'side' );
});

function wcpee_count_metabox_cb(){
  $post = get_post();
  echo "Число переходов по ссылке продукта: " . get_post_meta($post->ID, 'wcpee_count', true);
}




//Делаем ссылку с атрибутом target=_blank
function wcpee_url_blank(){

    global $product;

    if ( ! $product->add_to_cart_url() ) {
        return;
    }

    $product_url = $product->add_to_cart_url();
    $button_text = $product->single_add_to_cart_text();

    do_action( 'woocommerce_before_add_to_cart_button' ); ?>
    <p class="cart">
        <a href="<?php echo esc_url( $product_url ); ?>" target="_blank" rel="nofollow" class="single_add_to_cart_button button alt">
          <?php echo esc_html( $button_text ); ?>
        </a>
    </p>
    <?php do_action( 'woocommerce_after_add_to_cart_button' );
}
add_action( 'woocommerce_external_add_to_cart', 'wcpee_url_blank', 30 );

function wcpee_remove_ext_url(){
  if(is_singular('product')) remove_action( 'woocommerce_external_add_to_cart', 'woocommerce_external_add_to_cart', 30 );
}
add_action('template_redirect', 'wcpee_remove_ext_url');
