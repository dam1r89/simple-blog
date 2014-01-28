<!doctype html>
<html lang="en">

<head>
    <base href="{{base}}">
    <link href='http://fonts.googleapis.com/css?family=Abel' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="css/style.css">
    <meta charset="UTF-8">
    <title><?php echo $this->prop('title') ?></title>
</head>

<body>
    <header>
        <nav><?php $this->piece('navigation') ?></nav>
    </header>
    <div class="content cf">
        <h1>Welcome layout</h1>
        <article>
            <?php $this->content() ?>
        </article>
    </div>
    <footer>

    </footer>
</body>

</html>
