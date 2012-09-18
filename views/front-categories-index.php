<div style='width: 550px; text-align: center; margin: 0 auto;'>
	<h2 style='text-align: left'>Our Products</h2>

	<?php foreach ($categories as $category): ?>
		<a href="#" class="funky_button"><?php echo $category->category_name ?></a>
	<?php endforeach; ?>
	<p>
		If we don't have a product that you are after please contact us and we will see if we are able to procure it for you.
	</p>
</div>
