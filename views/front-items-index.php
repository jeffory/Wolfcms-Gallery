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

<table class="items_listing">
<?php foreach ($items as $item): ?>

	<a href="<?php echo URL_PUBLIC. GAL_URL. URL_SUFFIX ?>">Back</a>
	<!--<?php print_r($item) ?>-->
	<tr>
		<td>
			<div class="thumb_container">
				<img src="<?php echo URL_PUBLIC. GAL_URL. '/file/image_thumb/'. $item->image_id[0] ?>">
			</div>
		</td>

		<td>
			<p>
				<strong><a href="<?php echo URL_PUBLIC. GAL_URL. '/'. $cat_id. '/'. $cat_slug. '/'. $item->id. '/'. Node::toSlug($item->name). URL_SUFFIX ?>"><?php echo $item->name ?></a></strong>
			</p>
		</td>

		<td>
			<p>
				<?php echo $item->description ?>
			</p>
		</td>
	</tr>
<?php endforeach; ?>
</table>

<style type="text/css">
	.items_listing {
		padding: 0 12px;
	}
</style>