<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit panel</title>
  <style>
  html, body, textarea{
    width: 100%;
    height: 100%;
    padding: 0;
    margin: 0;
    background: #333;
  }
  a{
    color: white;
  }
  a:hover{
    color: #c50;
  }
  textarea{
    position: absolute;
    padding: 4px;
    left: 0;
    top: 0;
    box-sizing: border-box;
    color: white;

  }
  .pages{
    padding: 8px;
    position: absolute;
    right: 0;
    background: black;
    background: rgba(255,255,255,0.4);

  }
  .pages a{
    display: block;
  }
  .save{
    position: absolute;
    bottom: 8px;
    right: 8px;
    padding: 4px 18px;
  }
  </style>
  <script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
  <script>
  $(document).on('keydown', 'textarea', function(e) {
    if(e.keyCode === 9) { // tab was pressed
        // get caret position/selection
        var start = this.selectionStart;
            end = this.selectionEnd;

        var $this = $(this);

        // set textarea value to: text before caret + tab + text after caret
        $this.val($this.val().substring(0, start)
                    + "\t"
                    + $this.val().substring(end));

        // put caret at right position again
        this.selectionStart = this.selectionEnd = start + 1;

        // prevent the focus lose
        return false;
    }
});
  </script>

</head>
<body>


  <?php
  if (isset($_POST['c'])){
    file_put_contents('pages/'.$_POST['f'], $_POST['c']);
  } ?>
  <?php if (isset($_GET['f'])): ?>
    <form action="admin.php" method="post">
      <input type="hidden" name="f" value="<?php echo $_GET['f'] ?>">
      <textarea name="c" id="" cols="30" rows="10"><?php echo file_get_contents('pages/'.$_GET['f']) ?></textarea>
      <button class="save" type="submit">save</button>
    </form>
  <?php endif;?>

  <div class="pages">
  <?php foreach (scandir('pages') as $file): if (substr($file,0,1)==='.') continue; ?>
    <a href="admin.php?f=<?php echo $file ?>"><?php echo $file ?></a>
  <?php endforeach; ?>
  <form action="admin.php?" method="post">
    <input type="text" name="f">
    <input type="hidden" name="c" value="{{route:change_me}}">
    <button type="submit">Nova stranica</button>
  </form>
  </div>


</body>
</html>
