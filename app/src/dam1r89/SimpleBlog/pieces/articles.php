<?php 
$pages = array_filter($simpleBlog->getPages(), function($page){ return isset($page['date']); });

usort($pages, function($a, $b){
	return strtotime($a['date']) >= strtotime($b['date']);
});

?>

<?php foreach ($pages as $page): ?>
	<a href="<?php echo $page['route'] ?>">
		<time datetime="<?php echo $page['date'] ?>"> <?php echo date('d/m/Y',strtotime($page['date'])) ?></time> <?php echo $page['title'] ?>
	</a>
<?php endforeach; ?>
