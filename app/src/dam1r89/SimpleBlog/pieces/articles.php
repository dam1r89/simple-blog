<?php foreach ($simpleBlog->getPages() as $route => $page): ?>
  <?php if (!isset($page['date'])) continue; ?>
  <a href="<?php echo $route ?>">
    <?php echo $page['title'] ?>
    (<?php echo date('d/m/Y',strtotime($page['date'])) ?>)
  </a>
<?php endforeach; ?>
