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

/**
 * Send an email using the Net SMTP library
 *
 * @param string $subj Subject of the email
 * @param string $body Body of the email
 * @param array  $rcpt Recipients as an array of e-mail addresses
 */
function send_email( $subj, $body, $rcpt ) {

	global $CONFIGS;

	require_once 'Net/SMTP.php';

	$host    = $CONFIGS['SMTP_HOST'];
	$port    = $CONFIGS['SMTP_PORT'];
	$from    = $CONFIGS['SMTP_FROM'];
	$uid     = $CONFIGS['SMTP_UID'];
	$pwd     = $CONFIGS['SMTP_PWD'];
	$method  = $CONFIGS['SMTP_METHOD'];
	$origin  = $CONFIGS['ORIGIN'];

	/* Create a new Net_SMTP object. */
	if (! ($smtp = new Net_SMTP( $host, $port ) ) ) {
		throw new Exception( "Unable to instantiate Net_SMTP object" );
	}

	/* Connect to the SMTP server. */
	if (PEAR::isError($e = $smtp->connect())) {
		throw new Exception( $e->getMessage() );
	}

	/* Do Authentication */
	if (Pear::isError( $e = $smtp->auth( $uid, $pwd, $method ) ) ) {
		throw new Exception( $e->getMessage() );
	}

	/* Send the 'MAIL FROM:' SMTP command. */
	if (PEAR::isError( $smtp->mailFrom($from) ) ) {
		throw new Exception("Unable to set sender to <$from>\n");
	}

	/* Address the message to each of the recipients. */
	foreach( $rcpt as $to ) {
		if ( PEAR::isError( $res = $smtp->rcptTo( $to ) ) ) {
			throw new Exception( "Unable to add recipient <$to>: " . $res->getMessage() );
		}
	}

	$header_values = [
		'MIME-Version' => '1.0',
		'Content-Type' => 'text/plain;charset=UTF-8',
		'Subject'      => $subj,
		'To'           => implode( ',', $rcpt ),
//                'From'         => sprintf(
//                                   '%s <%s>',
//                                    get_bloginfo( 'name' ),
//                                    WP_NET_SMTP_FROM
//                               ),
		'X-Mailer'     => 'Net/SMTP.php via MediaWiki Category Watcher',
	];

	// build the request headers
	$headers_raw = [];
	foreach( $header_values as $header => $value ) {
		$headers_raw[] = "$header: $value";
	}

	// block of headers
	$headers_txt = implode( "\r\n" , $headers_raw );

	/* Set the body of the message. */
	if ( PEAR::isError( $smtp->data( $headers_txt . "\r\n" . $body ) ) ) {
		throw new Exception( "Unable to send data" );
	}

	/* Disconnect from the SMTP server. */
	$smtp->disconnect();
}
