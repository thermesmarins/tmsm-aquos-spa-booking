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

	<h2><?php echo __( 'These published products don’t have an Aquos ID meta data', 'tmsm-aquos-spa-booking' ); ?></h2>

<?php
$args = [
'status' => 'publish',
'posts_per_page' => '-1',
];
$products = wc_get_products($args);

$counter_products = 0;
foreach($products as $product){

	if ($product->get_type() === 'variable') {

		$output= '';
		$output .= '<a href="'.get_edit_post_link($product->get_id()).'">'.$product->get_title().'</a>';
		$output .= ' — '.$product->get_type();
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
			$counter_products ++;
			echo $output;
		}
	}
	else{
		if(empty($product->get_meta('_aquos_id'))){
			$counter_products ++;
			echo '<a href="'.get_edit_post_link($product->get_id()).'">'.$product->get_title().'</a>';
			echo ' — '.$product->get_type();
			echo '<br>';
			echo '<br>';
		}
	}
}
if($counter_products === 0){
	echo __( 'No product', 'tmsm-aquos-spa-booking' );
}
?>

<h2><?php echo __( 'These draft products don’t have an Aquos ID meta data', 'tmsm-aquos-spa-booking' ); ?></h2>

<?php
$args = [
'status' => 'draft',
'posts_per_page' => '-1',
];
$products = wc_get_products($args);
$counter_products = 0;
foreach($products as $product){

	if ($product->get_type() === 'variable') {

		$output= '';
		$output .= '<a href="'.get_edit_post_link($product->get_id()).'">'.$product->get_title().'</a>';
		$output .= ' — '.$product->get_type();
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
			$counter_products ++;
			echo $output;
		}
	}
	else{
		if(empty($product->get_meta('_aquos_id'))){
			$counter_products ++;
			echo '<a href="' . get_edit_post_link( $product->get_id() ) . '">' . $product->get_title() . '</a>';
			echo ' — '.$product->get_type();
			echo '<br>';
			echo '<br>';
		}
	}
}
if($counter_products === 0){
	echo __( 'No product', 'tmsm-aquos-spa-booking' );
}