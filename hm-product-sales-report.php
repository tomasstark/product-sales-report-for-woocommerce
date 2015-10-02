<?php
/**
 * Plugin Name: Product Sales Report for WooCommerce
 * Description: Generates a report on individual WooCommerce products sold during a specified time period.
 * Version: 1.1.6
 * Author: Hearken Media
 * Author URI: http://hearkenmedia.com/landing-wp-plugin.php?utm_source=product-sales-report&utm_medium=link&utm_campaign=wp-widget-link
 * License: GNU General Public License version 2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
 */




define('HM_PSR_IS_PRO', class_exists('HM_Product_Sales_Report_Pro'));


// Add the Product Sales Report to the WordPress admin
add_action('admin_menu', function() {
	add_submenu_page('woocommerce', 'Product Sales Report', 'Product Sales Report', 'view_woocommerce_reports', 'hm_sbp', 'hm_sbp_page');
});

function hm_psr_default_report_settings() {
	return array(
		'report_time' => '30d',
		'report_start' => date('Y-m-d', current_time('timestamp') - (86400 * 31)),
		'report_end' => date('Y-m-d', current_time('timestamp') - 86400),
		'cat' => 0,
		'variations' => 0,
		'orderby' => 'quantity',
		'orderdir' => 'desc',
		'fields' => array('product_id', 'product_sku', 'product_name', 'quantity_sold', 'gross_sales'),
		'limit_on' => 0,
		'limit' => 10,
		'include_header' => 1
	);
}

// This function generates the Product Sales Report page HTML
function hm_sbp_page() {

	$savedReportSettings = get_option('hm_psr_report_settings');
	if (isset($_POST['op']) && $_POST['op'] == 'preset-del' && !empty($_POST['r']) && isset($savedReportSettings[$_POST['r']])) {
		unset($savedReportSettings[$_POST['r']]);
		update_option('hm_psr_report_settings', $savedReportSettings);
		$_POST['r'] = 0;
		echo('<script type="text/javascript">location.href = location.href;</script>');
	}
	
	$reportSettings = (empty($savedReportSettings) ?
						hm_psr_default_report_settings() :
						array_merge(hm_psr_default_report_settings(),
								$savedReportSettings[
									isset($_POST['r']) && isset($savedReportSettings[$_POST['r']]) ? $_POST['r'] : 0
								]
						));
	
	$fieldOptions = array(
		'product_id' => 'Product ID',
		'variation_id' => 'Variation ID',
		'product_sku' => 'Product SKU',
		'product_name' => 'Product Name',
		'variation_attributes' => 'Variation Attributes',
		'quantity_sold' => 'Quantity Sold',
		'gross_sales' => 'Gross Sales',
		'gross_after_discount' => 'Gross Sales (After Discounts)'
	);
		
	
	// Print header
	echo('
		<div class="wrap">
			<h2>Product Sales Report'.'</h2>
	');
	
	// Check for WooCommerce
	if (!class_exists('WooCommerce')) {
		echo('<div class="error"><p>This plugin requires that WooCommerce is installed and activated.</p></div></div>');
		return;
	} else if (!function_exists('wc_get_order_types')) {
		echo('<div class="error"><p>The Product Sales Report plugin requires WooCommerce 2.2 or higher. Please update your WooCommerce install.</p></div></div>');
		return;
	}
	
	
	
	// Check for license
	if (HM_PSR_IS_PRO && !HM_Product_Sales_Report_Pro::licenseCheck())
		return;
	
	
	// Print form
	
	
		echo('<div style="background-color: #fff; border: 1px solid #ccc; padding: 20px;">
				<h3 style="margin: 0;">Upgrade to Product Sales Report Pro for the following additional features:</h3>
				<ul>
					<li>Report on product variations individually</li>
					<li>Change the order of the fields/columns in the report</li>
					<li>Save multiple report presets to save time when generating different reports</li>
				</ul>
				<strong>Receive a 25% discount with the coupon code <span style="color: #f00;">PSR25OFF</span>!</strong>
				<a href="http://hearkenmedia.com/landing-wp-plugin.php?utm_source=product-sales-report&amp;utm_medium=link&amp;utm_campaign=wp-plugin-upgrade-link" target="_blank">Buy Now &gt;</a>
			</div>');
	
	
	
	echo('<form action="" method="post">
				<input type="hidden" name="hm_sbp_do_export" value="1" />
		');
	wp_nonce_field('hm_sbp_do_export');
	echo('
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label for="hm_sbp_field_report_time">Report Period:</label>
						</th>
						<td>
							<select name="report_time" id="hm_sbp_field_report_time">
								<option value="0d"'.($reportSettings['report_time'] == '0d' ? ' selected="selected"' : '').'>Today</option>
								<option value="1d"'.($reportSettings['report_time'] == '1d' ? ' selected="selected"' : '').'>Yesterday</option>
								<option value="7d"'.($reportSettings['report_time'] == '7d' ? ' selected="selected"' : '').'>Last 7 days</option>
								<option value="30d"'.($reportSettings['report_time'] == '30d' ? ' selected="selected"' : '').'>Last 30 days</option>
								<option value="all"'.($reportSettings['report_time'] == 'all' ? ' selected="selected"' : '').'>All time</option>
								<option value="custom"'.($reportSettings['report_time'] == 'custom' ? ' selected="selected"' : '').'>Custom date range</option>
							</select>
						</td>
					</tr>
					<tr valign="top" class="hm_sbp_custom_time">
						<th scope="row">
							<label for="hm_sbp_field_report_start">Start Date:</label>
						</th>
						<td>
							<input type="date" name="report_start" id="hm_sbp_field_report_start" value="'.$reportSettings['report_start'].'" />
						</td>
					</tr>
					<tr valign="top" class="hm_sbp_custom_time">
						<th scope="row">
							<label for="hm_sbp_field_report_end">End Date:</label>
						</th>
						<td>
							<input type="date" name="report_end" id="hm_sbp_field_report_end" value="'.$reportSettings['report_end'].'" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="hm_sbp_field_cat">Product Category:</label>
						</th>
						<td>
	');
	
	wp_dropdown_categories(array(
		'taxonomy' => 'product_cat',
		'id' => 'hm_sbp_field_cat',
		'name' => 'cat',
		'orderby' => 'NAME',
		'order' => 'ASC',
		'show_option_all' => 'All Categories',
		'selected' => $reportSettings['cat']
	));
	
	echo('
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label>Product Variations:</label>
						</th>
						<td>
							<label>
								<input type="radio" name="variations" value="0"'.(empty($reportSettings['variations']) ? ' checked="checked"' : '').' class="variations-fld" />
								Group product variations together
							</label><br />
							<label>
								<input type="radio" name="variations" value="1"'.(empty($reportSettings['variations']) ? '' : ' checked="checked"').(HM_PSR_IS_PRO ? '' : ' disabled="disabled"').' class="variations-fld" />
								Report on each variation separately'.(HM_PSR_IS_PRO ? '' : '<sup style="color: #f00;">PRO</sup>').'
							</label>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="hm_sbp_field_orderby">Sort By:</label>
						</th>
						<td>
							<select name="orderby" id="hm_sbp_field_orderby">
								<option value="product_id"'.($reportSettings['orderby'] == 'product_id' ? ' selected="selected"' : '').'>Product ID</option>
								<option value="quantity"'.($reportSettings['orderby'] == 'quantity' ? ' selected="selected"' : '').'>Quantity Sold</option>
								<option value="gross"'.($reportSettings['orderby'] == 'gross' ? ' selected="selected"' : '').'>Gross Sales</option>
								<option value="gross_after_discount"'.($reportSettings['orderby'] == 'gross_after_discount' ? ' selected="selected"' : '').'>Gross Sales (After Discounts)</option>
							</select>
							<select name="orderdir">
								<option value="asc"'.($reportSettings['orderdir'] == 'asc' ? ' selected="selected"' : '').'>ascending</option>
								<option value="desc"'.($reportSettings['orderdir'] == 'desc' ? ' selected="selected"' : '').'>descending</option>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label>Report Fields:</label>
						</th>
						<td id="hm_psr_report_field_selection">');
	$fieldOptions2 = $fieldOptions;
	foreach ($reportSettings['fields'] as $fieldId) {
		if (!isset($fieldOptions2[$fieldId]))
			continue;
		echo('<label><input type="checkbox" name="fields[]" checked="checked" value="'.$fieldId.'"'.(in_array($fieldId, array('variation_id', 'variation_attributes')) ? ' class="variation-field"' : '').' /> '.$fieldOptions2[$fieldId].'</label>');
		unset($fieldOptions2[$fieldId]);
	}
	foreach ($fieldOptions2 as $fieldId => $fieldDisplay) {
		echo('<label><input type="checkbox" name="fields[]" value="'.$fieldId.'"'.(in_array($fieldId, array('variation_id', 'variation_attributes')) ? ' class="variation-field"' : '').' /> '.$fieldDisplay.'</label>');
	}
	unset($fieldOptions2);
				echo('</td>
					</tr>
					<tr valign="top">
						<th scope="row" colspan="2" class="th-full">
							<label>
								<input type="checkbox" name="limit_on"'.(empty($reportSettings['limit_on']) ? '' : ' checked="checked"').' />
								Show only the first
								<input type="number" name="limit" value="'.$reportSettings['limit'].'" min="0" step="1" class="small-text" />
								products
							</label>
						</th>
					</tr>
					<tr valign="top">
						<th scope="row" colspan="2" class="th-full">
							<label>
								<input type="checkbox" name="include_header"'.(empty($reportSettings['include_header']) ? '' : ' checked="checked"').' />
								Include header row
							</label>
						</th>
					</tr>
				</table>');
				
				

				
				echo('<p class="submit">
					<button type="submit" class="button-primary">Get Report</button>
				</p>
			</form>');
			
				echo('
				<div style="background-color: #fff; border: 1px solid #ccc; padding: 20px; display: inline-block;">
					<h3 style="margin: 0;">Plugin by:</h3>
					<a href="http://hearkenmedia.com/landing-wp-plugin.php?utm_source=product-sales-report&amp;utm_medium=link&amp;utm_campaign=wp-widget-link" target="_blank"><img src="'.plugins_url('images/hm-logo.png', __FILE__).'" alt="Hearken Media" style="width: 250px;" /></a><br />
					<a href="https://wordpress.org/support/view/plugin-reviews/product-sales-report-for-woocommerce" target="_blank"><strong>
						If you find this plugin useful, please write a brief review!
					</strong></a>
				</div>
				');

			
	echo('
		</div>
		
		<script type="text/javascript" src="'.plugins_url('js/hm-product-sales-report.js', __FILE__).'"></script>
	');
	
	


}

// Hook into WordPress init; this function performs report generation when
// the admin form is submitted
add_action('init', 'hm_sbp_on_init');
function hm_sbp_on_init() {
	global $pagenow;
	
	// Check if we are in admin and on the report page
	if (!is_admin())
		return;
	if ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'hm_sbp' && !empty($_POST['hm_sbp_do_export'])) {
		
		// Verify the nonce
		check_admin_referer('hm_sbp_do_export');
		
		$newSettings = array_intersect_key($_POST, hm_psr_default_report_settings());
		foreach ($newSettings as $key => $value)
			if (!is_array($value))
				$newSettings[$key] = htmlspecialchars($value);
		
		// Update the saved report settings
		$savedReportSettings = get_option('hm_psr_report_settings');
		$savedReportSettings[0] = array_merge(hm_psr_default_report_settings(), $newSettings);
		

		update_option('hm_psr_report_settings', $savedReportSettings);
		
		// Check if no fields are selected
		if (empty($_POST['fields']))
			return;
		
		// Assemble the filename for the report download
		$filename =  'Product Sales - ';
		if (!empty($_POST['cat']) && is_numeric($_POST['cat'])) {
			$cat = get_term($_POST['cat'], 'product_cat');
			if (!empty($cat->name))
				$filename .= addslashes(html_entity_decode($cat->name)).' - ';
		}
		$filename .= date('Y-m-d', current_time('timestamp')).'.csv';
		
		// Send headers
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		
		// Output the report header row (if applicable) and body
		$stdout = fopen('php://output', 'w');
		if (!empty($_POST['include_header']))
			hm_sbp_export_header($stdout);
		hm_sbp_export_body($stdout);
		
		exit;
	}
}

// This function outputs the report header row
function hm_sbp_export_header($dest) {
	$header = array();
	
	foreach ($_POST['fields'] as $field) {
		switch ($field) {
			case 'product_id':
				$header[] = 'Product ID';
				break;
			case 'variation_id':
				$header[] = 'Variation ID';
				break;
			case 'product_sku':
				$header[] = 'Product SKU';
				break;
			case 'product_name':
				$header[] = 'Product Name';
				break;
			case 'variation_attributes':
				$header[] = 'Variation Attributes';
				break;
			case 'quantity_sold':
				$header[] = 'Quantity Sold';
				break;
			case 'gross_sales':
				$header[] = 'Gross Sales';
				break;
			case 'gross_after_discount':
				$header[] = 'Gross Sales (After Discounts)';
				break;
		}
	}
	
	fputcsv($dest, $header);
}

// This function generates and outputs the report body rows
function hm_sbp_export_body($dest) {
	global $woocommerce;
	
	// If a category was selected, fetch all the product IDs in that category
	$category_id = (isset($_POST['cat']) && is_numeric($_POST['cat']) ? $_POST['cat'] : 0);
	if (!empty($category_id))
		$product_ids = get_objects_in_term($category_id, 'product_cat');
	
	// Calculate report start and end dates (timestamps)
	switch ($_POST['report_time']) {
		case '0d':
			$end_date = strtotime('midnight', current_time('timestamp'));
			$start_date = $end_date;
			break;
		case '1d':
			$end_date = strtotime('midnight', current_time('timestamp')) - 86400;
			$start_date = $end_date;
			break;
		case '7d':
			$end_date = strtotime('midnight', current_time('timestamp')) - 86400;
			$start_date = $end_date - (86400 * 7);
			break;
		case 'custom':
			$end_date = strtotime('midnight', strtotime($_POST['report_end']));
			$start_date = strtotime('midnight', strtotime($_POST['report_start']));
			break;
		default: // 30 days is the default
			$end_date = strtotime('midnight', current_time('timestamp')) - 86400;
			$start_date = $end_date - (86400 * 30);
	}
	
	// Assemble order by string
	$orderby = (in_array($_POST['orderby'], array('product_id', 'gross', 'gross_after_discount')) ? $_POST['orderby'] : 'quantity');
	$orderby .= ' '.($_POST['orderdir'] == 'asc' ? 'ASC' : 'DESC');
	
	// Create a new WC_Admin_Report object
	include_once($woocommerce->plugin_path().'/includes/admin/reports/class-wc-admin-report.php');
	$wc_report = new WC_Admin_Report();
	$wc_report->start_date = $start_date;
	$wc_report->end_date = $end_date;

	// Get report data
	
	

		// Based on woocoommerce/includes/admin/reports/class-wc-report-sales-by-product.php
		$sold_products = $wc_report->get_order_report_data(array(
			'data' => array(
				'_product_id' => array(
					'type' => 'order_item_meta',
					'order_item_type' => 'line_item',
					'function' => '',
					'name' => 'product_id'
				),
				'_qty' => array(
					'type' => 'order_item_meta',
					'order_item_type' => 'line_item',
					'function' => 'SUM',
					'name' => 'quantity'
				),
				'_line_subtotal' => array(
					'type' => 'order_item_meta',
					'order_item_type' => 'line_item',
					'function' => 'SUM',
					'name' => 'gross'
				),
				'_line_total' => array(
					'type' => 'order_item_meta',
					'order_item_type' => 'line_item',
					'function' => 'SUM',
					'name' => 'gross_after_discount'
				)
			),
			'query_type' => 'get_results',
			'group_by' => 'product_id',
			'order_by' => $orderby,
			'limit' => (!empty($_POST['limit_on']) && is_numeric($_POST['limit']) ? $_POST['limit'] : ''),
			'filter_range' => ($_POST['report_time'] != 'all'),
			'order_types' => wc_get_order_types('order_count')
		));
	

	
	// Output report rows
	foreach ($sold_products as $product) {
		if (!empty($category_id) && !in_array($product->product_id, $product_ids))
			continue;
		$row = array();
		
		foreach ($_POST['fields'] as $field) {
			switch ($field) {
				case 'product_id':
					$row[] = $product->product_id;
					break;
				case 'variation_id':
					$row[] = (empty($product->variation_id) ? '' : $product->variation_id);
					break;
				case 'product_sku':
					$row[] = get_post_meta($product->product_id, '_sku', true);
					break;
				case 'product_name':
					$row[] = html_entity_decode(get_the_title($product->product_id));
					break;
				case 'variation_attributes':
					$row[] = (HM_PSR_IS_PRO ? HM_Product_Sales_Report_Pro::getFormattedVariationAttributes($product) : '');
					break;
				case 'quantity_sold':
					$row[] = $product->quantity;
					break;
				case 'gross_sales':
					$row[] = $product->gross;
					break;
				case 'gross_after_discount':
					$row[] = $product->gross_after_discount;
					break;
			}
		}
			
		fputcsv($dest, $row);
	}
}

add_action('admin_enqueue_scripts', 'hm_psr_admin_enqueue_scripts');
function hm_psr_admin_enqueue_scripts() {
	wp_register_style('hm_psr_admin_style', plugins_url('css/hm-product-sales-report.css', __FILE__));
	wp_enqueue_style('hm_psr_admin_style');
}
?>