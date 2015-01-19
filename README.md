#Simple Blog

## TODO list

`.` route automatically build to index.html

You need to setup YAML configuration with name `simpleblog.yml`. In that file you need to define paths for following
folders. 

	output : ../static
	pages  : ./pages
	assets : ./assets
	pieces : ./pieces
	layouts: ./layouts

###Output

`output` is a folder where static files will be generated.

###Pages

`pages` is a folder containing all your pages organized in any way. Scanning this folder is recursive, so you can
create folder structure however you want, and it will not have any meaning to the created site.

###Assets

This is folder that will be completely copied to the static folder when building a site. All files inside this
folder are on the same level as layout. So if you have a __css__ folder with __styles.css__ file inside of an
assets folder you would reference it in your layout like they are on the same folder. For example:

	<link rel="stylesheet" href="css/styles.css">

###Layouts

Layouts will be treated as a php file, even if it has other extension. Default layout is `layout.html`.
In layout you can use properties that are defined i **YAML** block of a page.

	---
	title: Home Page
	---
For example if you have above code in your page, you can use that value in layout with `prop()` method.

	<?php $this->prop('title') ?>

To put content of a page somewhere in your layout you need to add this:

	<?php $this->content() ?>

You can add pieces of your blog with `<?php $this->piece('piece-name') ?>`. Piece name is same
as a file name of php file in pieces folder (which is defined in **simpleblog.yml** config file).

> Layouts and Pieces share same methods and properties. Only it doesn't make sense to to use __$this->content()__
> in piece.

You can use following properties to generate code in your layout or piece.
	`$this->page` - Associative array which represents requested page. It contains properties defined in in **yaml**
	block of a page. 

	`$this->pages` - array of all parsed pages from pages folder


###Pieces

Pieces are partial parts of a site. This can be used to generate navigation.
