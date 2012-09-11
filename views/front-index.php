<h2><?php echo GAL_TITLE ?></h2>

<table class="items_listing">
<?php foreach ($items as $item): ?>
	<tr>
		<td>
			<div class="thumb_container">
				<img src="<?php echo URL_PUBLIC. GAL_URL. '/file/image/'. $item['id'] ?>">
			</div>
		</td>

		<td>
			<p>
				<strong><?php echo $item['code'] ?></strong><br>
				<?php echo $item['name'] ?>
			</p>
		</td>

		<td>
			<p>
				<?php echo $item['description'] ?>
			</p>
		</td>
	</tr>
<?php endforeach; ?>
</table>