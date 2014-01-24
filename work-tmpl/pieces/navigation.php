<?php foreach ($this->pages as $route => $page): ?>
 <?php if (isset($page['date']) || !isset($page['title'])) continue; ?>
 <a href="<?php echo $route ?>"><?php echo $page['title'] ?></a>
<?php endforeach; ?>

