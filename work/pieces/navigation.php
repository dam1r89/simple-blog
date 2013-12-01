<?php foreach ($t->pages as $key => $page): ?>
 <?php if (isset($page['date']) || !isset($page['title'])) continue; ?>
 <a href="<?php echo $key ?>"><?php echo $page['title'] ?></a>
<?php endforeach; ?>

