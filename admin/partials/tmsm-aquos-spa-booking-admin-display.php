<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://github.com/nicomollet/
 * @since      1.0.0
 *
 * @package    Tmsm_Aquos_Spa_Booking
 * @subpackage Tmsm_Aquos_Spa_Booking/admin/partials
 */
?>

	<h2><?php echo __( 'These products don’t have an Aquos ID meta data', 'tmsm-aquos-spa-booking' ); ?></h2>

<?php
$args = [
'status' => 'all',
'posts_per_page' => '-1',
];
$products = wc_get_products($args);
foreach($products as $product){

	if ($product->product_type === 'variable') {

		$output= '';
		$output .= '<a href="'.get_edit_post_link($product->get_id()).'">'.$product->get_title().'</a>';
		$output .= ' — '.$product->product_type;
		$output .= ' — '.$product->status;
		$output .= '<br>';
		$counter_variations_without_id = 0;
		foreach($product->get_available_variations() as $variation_data){

			$variation = wc_get_product($variation_data['variation_id']);
			if(empty($variation->get_meta('_aquos_id'))){
				$output .=  '&nbsp;&nbsp;&nbsp;- '.$variation->get_title();
				$output .=  ' ('.$variation->get_attribute_summary().')';
				$output .=  '<br>';
				$counter_variations_without_id++;
			}
		}
		$output .= '<br>';
		if($counter_variations_without_id > 0){
			echo $output;
		}
	}
	else{
		if(empty($product->get_meta('_aquos_id'))){
			echo '<a href="'.get_edit_post_link($product->get_id()).'">'.$product->get_title().'</a>';
			$output .= ' — '.$product->product_type;
			$output .= ' — '.$product->status;
			echo '<br>';
			echo '<br>';
		}
	}
}