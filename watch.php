#!/usr/bin/php
<?php
# MediaWiki Category Watcher
# Copyright (C) 2020 Valerio Bozzolan
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU Affero General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <http://www.gnu.org/licenses/>.

// require some basic functions
require 'functions.php';

// register some inline options
$opts = getopt( '', [
	'config:',
	'category:',
	'wiki:',
	'to:',
	'strip-base',
] );

// config file
$CONFIG_FILE = $opts['config']   ?? 'config.php';

// config file
$CATEGORY    = $opts['category'] ?? null;

// wiki identifier
$WIKI_UID    = $opts['wiki']     ?? null;

// comma separated list of additional destination emails
$TO_RAW_LIST = $opts['to']       ?? '';

// eventually strip page title prefixes from the mail body
// to just have 'Something Title' from 'Project:Pages/Page Collection/Something Title'
$STRIP_BASE  = isset( $opts['strip-base'] );

// no wiki no party
if( !$WIKI_UID ) {
	echo "Please set a --wiki=identifier\n";
	exit( 1 );
}

// no category no party
if( !$CATEGORY ) {
	echo "Please set a --category=Name\n";
	exit( 1 );
}

// read the configuration
require $CONFIG_FILE;

use web\MediaWikis;

// check if the wiki exists
$wiki = MediaWikis::findFromUID( $WIKI_UID );
if( !$wiki ) {
	echo "Missing wiki with identifier $WIKI_UID\n";
	exit( 1 );
}

// merge the base email addresses with the specified ones (if any)
$TO = $CONFIGS['BASE_RECIPIENTS'] ?? [];
if( $TO_RAW_LIST ) {
	$to_additionals = explode( ',', $TO_RAW_LIST );
	foreach( $to_additionals as $to_additional ) {
		$TO[] = $to_additional;
	}
}

// avoid duplicates added by mistake
array_unique( $TO );

// validate each email address
foreach( $TO as $to ) {
	if( !filter_var( $to, FILTER_VALIDATE_EMAIL ) ) {
		echo "The email address '$to' is considered not valid\n";
		exit( 2 );
	}
}

// no addresses no party
if( !$TO ) {
	echo "Please specify at least one --to=email@example.com\n";
}

// parse the category title
$category_object = $wiki->createTitleParsing( $CATEGORY );

// cache file
$CACHE_FILE = $CONFIGS['DIR_CACHE'] . '/' . $CATEGORY;

// content of the cache
$cache = @json_decode( @file_get_contents( $CACHE_FILE, true ), true );
$cache = $cache ?? [];

// pages never seen before
$unseen = [];

// query the category members
$queries =
	$wiki->createQuery( [
		'action'  => 'query',
		'list'    => 'categorymembers',
		'cmtitle' => $CATEGORY,
	] );

// for each API continuation
foreach( $queries as $query ) {

	$members = $query->query->categorymembers ?? null;
	foreach( $members as $member ) {

		// well
		$pageid = $member->pageid;
		$title  = $member->title;

		// first time? save
		if( !isset( $cache[ $pageid ] ) ) {

			// this is a new page!
			$unseen[] = $title;

			// let's remember it
			$cache[ $pageid ] = [
				'title' => $title,
			];
		}

		// save the last seen date
		$cache[ $pageid ][ 'lastseen' ] = time();
	}
}

if( $unseen ) {

	// get the body of the email
	$body = file_get_contents( $CONFIGS['BODY' ] );

	// build a markdown list of the unseen pages
	$unseen_list = [];
	foreach( $unseen as $title ) {

		// eventually strip the title prefix
		if( $STRIP_BASE ) {
			$title = basename( $title );
		}

		// prepare a cute page body
		$title_object = $wiki->createTitleParsing( $title );
		$title_url = $title_object->getURL();
		$unseen_list[] = "* $title";
		$unseen_list[] = "* $title_url";
		$unseen_list[] = '';
	}

	// one per line please
	$unseen_list_txt = implode( "\n", $unseen_list );

	// replace arguments in the mail body
	$body = sprintf( $body,

		// $1%s
		$unseen_list_txt,

		// %2$s
		$CATEGORY,

		// %3$s
		$category_object->getURL(),

		// %4$s
		$CONFIGS['ORIGIN']
	);

	// replace arguments in the mail subject
	$subject = sprintf(
		$CONFIGS['SUBJECT'],
		$CATEGORY
	);

	// send the email to the recipients
	send_email( $subject, $body, $TO );

}

// save the results
file_put_contents( $CACHE_FILE, json_encode( $cache, JSON_PRETTY_PRINT ) );
