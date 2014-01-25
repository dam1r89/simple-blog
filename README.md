#Simple Blog

## TODO list

`.` route automatically build to index.html

Work folder has four folder:
	
	- assets
	- layouts
	- pages
	- pieces

##Layout and Pieces

Every layout and pieces have these elements when you use php templates.

###Properties

You can use every property that is defined in **YAML** block. Example:
	
	---
	title: Home Page
	---

and i piece you can add it 

	<?php echo $title ?>

There are additional properties that every page have.

`$pages` is an array of all other pages. You can use this array to create related links.

###Methods

`$this->content()` insert content of requested page on that place.

`$this->piece(pieceName)` insert piece with selected name.

If you for example need `'navigation'` piece, you will type `$this->piece('navigation')`
