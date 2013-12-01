<?php foreach ($t->pages as $key => $page): ?>
  <?php if (!isset($page['date'])) continue; ?>
  <a href="<?php echo $key ?>">
    <?php echo $page['title'] ?>
    (<?php echo date('d/m/Y',strtotime($page['date'])) ?>)
  </a>
<?php endforeach; ?>
