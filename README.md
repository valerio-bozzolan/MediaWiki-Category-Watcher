= MediaWiki Category Watcher ==

Welcome in the MediaWiki Category Watcher! Another keep-it-simple-and-stupid watcher for your MediaWiki categories!

== Installation ==

	git clone this_repository
	git clone https://gitpull.it/source/boz-mw.git


Copy the `config-example.php` and save as `config.php`.

Fill your SMTP credentials.

== Usage ==

	./watch.php --wiki=metawiki --category="Category:ItWikiCon 2020 - Proposals"

== Body ==

The `body.txt` file is just your email body, supporting this `sprintf` format:

* `$1%s`: List of pages added inside the category.
* `%2$s`: Watched category
* `%3$s`: Watched category URL

== License ==

Copyright (c) 2020 Valerio Bozzolan

This is a Free as in Freedom project. It comes with ABSOLUTELY NO WARRANTY. You are welcome to redistribute it under the terms of the GNU General Public License v3+.
