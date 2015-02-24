<?php
/**
 * Simple, easy to setup and use gallery plugin for WolfCMS
 *
 * @package Plugins
 * @subpackage Gallery
 *
 * @author Keith McGahey
 */
?>
<h2></h2>

<p class='item_image'>
	<img src='<?php echo URL_PUBLIC. GAL_URL. '/file/image/'. $item->image_id[0] ?>' style='max-width: 100%'>
</p>

<p class='item_info'>
	<div class='thumbs'>
		<?php foreach ($item->image_id as $image_id): ?>
			<img src='<?php echo URL_PUBLIC. GAL_URL. '/file/image_thumb/'. $image_id ?>' style='max-width: 100%'>
		<?php endforeach; ?>
	</div>
	
	<dl>
		<dt>Product name</dt>
		<dd><?php echo $item->name ?></dd>

		<dt>Description</dt>
		<dd><?php echo $item->description ?></dd>

		<dt>Categories</dt>
		<dd><?php echo implode(',', $item->category_name) ?></dd>
	</dl>
</p>

<p class='item_footer'>
	<a href="javascript:history.go(-1)">Go back</a>
</p>

<style type="text/css">
	.item_image {
		width: 440px;
		float: left;
		margin-right: 40px;
	}
	.item_info {
		margin-top: 70px;
	}
	dt, dd {
		display: inline-block;
	}
	dt {
		width: 180px;
		float: left;
	}
	dt:after {
		content: ":";
	}
	dd {
		min-width: 120px;
		clear: right;
	}
</style>