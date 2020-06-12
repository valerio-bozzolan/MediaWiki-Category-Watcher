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

$CONFIGS = [
	// subject of the email
	// the '%s' is your watched category (you may omit it)
	'SUBJECT'     => "New Page in %s",

	// smtp credentials to send emails
	'SMTP_HOST'   => 'ssl://smtp.example.com',
	'SMTP_PORT'   => '465',
	'SMTP_FROM'   => 'asd@example.com',
	'SMTP_UID'    => 'asd@example.com',
	'SMTP_PWD'    => 'secret',
	'SMTP_METHOD' => 'LOGIN',

	// file with the body of the email
	'BODY'        => __DIR__ . '/body.txt',

	// directory used to cache stuff (please with trailing slash)
	'DIR_CACHE'   => __DIR__ . '/cache',

	// base recipients of your emails
	// note that you can also set '--to=receiver@host.com,receiver2@host.com' from command line
	'BASE_RECIPIENTS'  => [
		// 'asd@receiver.it',
	],
];

// Load the 'boz-mw' framework
//
// Go outside this directory and run:
//    git clone https://gitpull.it/source/boz-mw/
require __DIR__ . '/../boz-mw/autoload.php';
