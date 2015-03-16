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
<div>
	<h2><?php echo __('Gallery') ?></h2>

	<?php foreach ($categories as $category): ?>
		<a href="<?php echo URL_PUBLIC. GAL_URL. '/'. $category->id. '/'. Node::toSlug($category->category_name) ?>"><?php echo $category->category_name ?></a>
	<?php endforeach; ?>
</div>