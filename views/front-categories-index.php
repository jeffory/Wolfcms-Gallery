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
	<?php foreach ($categories as $category): ?>
		<a href="<?php echo URL_PUBLIC. GAL_URL. '/'. $category->id. '/'. Node::toSlug($category->category_name). URL_SUFFIX ?>"><?php echo $category->category_name ?></a>
	<?php endforeach; ?>
</div>