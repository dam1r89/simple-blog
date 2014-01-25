---
route: .
title: Home Page
---

This is <?php echo $title ?> 

#Simple Blog

## TODO list

`.` route automatically build to index.html

You need to setup YAML configuration with name `simpleblog.yml`. In that file you need to define paths for following
folders. 

	output: ../static
	pages : ./pages
	assets: ./assets
	pieces: ./pieces
	layout: ./layouts/default.php

###Layout

Layout will be putted through php engine, no mather what extension it has.
In layout you can use properties that are defined i **YAML** block. Example:

	---
	title: Home Page
	---


###Properties

You can use every property that is defined in **YAML** block. Example:
	

and i piece you can use it like this:

	<?php echo $title ?>

There are additional properties that every page have.

`$pages` is an array of all other pages. You can use this array to create related links.

###Methods

`$this->content()` insert content of requested page on that place.

`$this->piece(pieceName)` insert piece with selected name.

If you for example need `'navigation'` piece, you will type `$this->piece('navigation')`

