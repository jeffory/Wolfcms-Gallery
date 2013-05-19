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
<div style='width: 550px; text-align: center; margin: 0 auto;'>
	<h2 style='text-align: left'><?php echo __('Gallery') ?></h2>

	<?php foreach ($categories as $category): ?>
		<a href="<?php echo URL_PUBLIC. GAL_URL. '/'. $category->id. '/'. url_slug($category->category_name) ?>"><?php echo $category->category_name ?></a>
	<?php endforeach; ?>
</div>