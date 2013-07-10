<?php foreach (scandir('pages') as $file): if (substr($file,0,1)==='.') continue; ?>
  <a href="admin.php?f=<?php echo $file ?>"><?php echo $file ?></a>
<?php endforeach; ?>
<form action="admin.php?" method="post">
  <input type="text" name="f">
  <input type="hidden" name="c" value="{{route:change_me}}">
  <button type="submit">Nova stranica</button>
</form>

<?php
if (isset($_POST['c'])){
  file_put_contents('pages/'.$_POST['f'], $_POST['c']);
} ?>
<?php if (isset($_GET['f'])): ?>
  <form action="admin.php" method="post">
    <input type="hidden" name="f" value="<?php echo $_GET['f'] ?>">
    <textarea name="c" id="" cols="30" rows="10"><?php echo file_get_contents('pages/'.$_GET['f']) ?></textarea>
    <button type="submit">save</button>
  </form>
<?php endif;?>
