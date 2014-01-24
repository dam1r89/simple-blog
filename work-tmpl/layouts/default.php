<!doctype html>
<html lang="en">

<head>
    <base href="{{base}}">
    <link href='http://fonts.googleapis.com/css?family=Abel' rel='stylesheet' type='text/css'>
    <link rel="stylesheet" href="css/style.css">
    <meta charset="UTF-8">
    <title><?php $this->val('title') ?></title>
</head>

<body>
    <header>
        <nav><?php $this->piece('navigation') ?></nav>
    </header>
    <div class="content cf">
        <article>
            <?php $this->content() ?>
        </article>
        <aside>
            <nav><?php $this->piece('articles') ?></nav>
        </aside>
    </div>
    <footer>
        <div class="inner">
            <span>dam1r89 simple blog</span>
            <span class="right">Simple blog generator version: 5</span>
        </div>

    </footer>
</body>

</html>
