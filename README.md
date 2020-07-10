# MediaWiki Category Watcher

Welcome in the MediaWiki Category Watcher! Another keep-it-simple-and-stupid watcher for your MediaWiki categories!

This tool is useful to reiceive an email when a page is added to a category.

## Installation


```
sudo apt install php-net-smtp
```

```
git clone https://gitpull.it/source/mediawiki-category-watcher.git
git clone https://gitpull.it/source/boz-mw.git
```

Then:

```
cd mediawiki-category-watcher
cp config-example.php config.php
```

Now fill your SMTP credentials in the `config.php` file.

## Usage

```
./watch.php --wiki=metawiki --strip-base --to=user@example.com,user2@example.com --category="Category:ItWikiCon 2020 - Proposals"
```

## Customize Email Body

The `body.txt` file is just your email body, supporting this `sprintf` format:

* `$1%s`: List of pages added inside the category.
* `%2$s`: Watched category
* `%3$s`: Watched category URL
* `%4$s`: Your ORIGIN, set in the configuration

## License

Copyright (c) 2020 Valerio Bozzolan

This is a Free as in Freedom project. It comes with ABSOLUTELY NO WARRANTY. You are welcome to redistribute it under the terms of the GNU General Public License v3+.
