<?php foreach ($simpleBlog->getPages() as $route => $page): ?>
<?php if (!isset($page['date'])) continue; ?>
	<a href="<?php echo $route ?>">
		<time datetime="<?php echo $page['date'] ?>"> <?php echo date('d/m/Y',strtotime($page['date'])) ?></time> <?php echo $page['title'] ?>
	
	</a>
<?php endforeach; ?>
