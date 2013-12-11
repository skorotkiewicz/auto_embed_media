<?php

/**
 * Copyright (C) 2008-2012 FluxBB
 * based on code by Rickard Andersson copyright (C) 2002-2008 PunBB
 * License: http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 */

// Make sure no one attempts to run this script "directly"
if (!defined('PUN'))
	exit;

// Globals
$embedables = array(
	'#https?://(www\.)?youtube\.com/watch.*#i',
	'#http://youtu.be/.*#i',
	'#https?://(www\.)?vimeo\.com/.*#i',
	'#https?://(www\.)?dailymotion\.com/.*#i',
	'#http://dai.ly/.*#i',
	'#https?://(www\.)?flickr\.com/.*#i',
	'#http://flic.kr/.*#i',
	'#https?://(.+?\.)?deviantart\.com/art/.*#i',
	'#https?://(.+?\.)?deviantart\.com/.+?\#/d.*#i',
	'#https?://(www\.)?twitter\.com/.+?/status(es)?/.*#i',
	'#https?://(www\.)?soundcloud\.com/.*#i',
	'#http://instagr(\.am|am\.com)/p/.*#i',
	'#https?://(www\.)?facebook\.com/permalink\.php\?story_fbid\=([0-9]+)(\&|\&amp;)id=([0-9]+)#i',
	'#https?://(www\.)?facebook\.com/([0-9]+)/posts/([0-9]+)#i'
);

$oembed = array(
	array( 'http://www.youtube.com/oembed', false ),
	array( 'http://www.youtube.com/oembed', false ),
	array( 'http://vimeo.com/api/oembed.json', false ),
	array( 'http://www.dailymotion.com/services/oembed', false ),
	array( 'http://www.dailymotion.com/services/oembed', false ),
	array( 'http://www.flickr.com/services/oembed/', true ),
	array( 'http://www.flickr.com/services/oembed/', true ),
	array( 'http://backend.deviantart.com/oembed', true ),
	array( 'http://backend.deviantart.com/oembed', true ),
	array( 'http://api.twitter.com/1/statuses/oembed.json', false ),
	array( 'http://soundcloud.com/oembed', false ),
	array( 'http://api.instagram.com/oembed', true ),
	'callback_facebook',
	'callback_facebookp'
);


//
// Handles OEmbed media
//
function handle_embed_tag( $url ) 
{
	global $embedables, $oembed;

	$url = pun_trim( $url );

	foreach ( $embedables as $i => $embedable ) {
		$_oembed = $oembed[ $i ];
		// No OEmbed support (Facebook)
		if ( ! is_array( $_oembed ) && preg_match( $embedable, $url, $m ) ) {
			switch ( $_oembed ) {
				case 'callback_facebook':
					$url = '<div id="fb-root"></div><script>(function(d, s, id) {var js, fjs = d.getElementsByTagName(s)[0];if (d.getElementById(id)) return;js = d.createElement(s); js.id = id;js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=appId}";fjs.parentNode.insertBefore(js, fjs);}(document, \'script\', \'facebook-jssdk\'));</script><div class="fb-post" data-href="'.$url.'"></div>';
					return $url;
					break;
				case 'callback_facebookp':
					$url = 'https://www.facebook.com/permalink.php?story_fbid='.$m[3].'&id='.$m[2];
					$url = '<div id="fb-root"></div><script>(function(d, s, id) {var js, fjs = d.getElementsByTagName(s)[0];if (d.getElementById(id)) return;js = d.createElement(s); js.id = id;js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=appId";fjs.parentNode.insertBefore(js, fjs);}(document, \'script\', \'facebook-jssdk\'));</script><div class="fb-post" data-href="'.$url.'"></div>';
					return $url;
					break;
				default:
					break;
			}
		}
		// OEmbed supported
		else if ( preg_match( $embedable, $url, $m ) ) {
			$url = preg_replace( $embedable, call_user_func( 'fetch_oembed', $_oembed, $m[0] ), $url );
		}
	}

	return $url;
}


//
// Fetch OEmbed JSON data
//
function fetch_oembed( $_oembed, $url ) {

	if ( is_array( $_oembed ) ) {
		$oembed_url = $_oembed[0];
		$is_img     = $_oembed[1];
	}

	$_url  = $oembed_url . '?' . http_build_query( array( 'url' => $url, 'format' => 'json' ) );
	$embed = json_decode( file_get_contents( $_url ) );

	if ( is_null( $embed ) )
		return $url;

	if ( true === $is_img )
		return '<a href="' . $embed->author_url . '"><img src="' . $embed->url . '" alt="' . $embed->title . '" width="' . $embed->width . '" height="' . $embed->height . '" /></a>';
	else
		return $embed->html;

	return $url;
}


//
// Make Embed links
//
function do_embed( $text ) {

	global $embedables, $oembed;

	foreach ( $embedables as $i => $embedable )
		$text = preg_replace( array( $embedable, "#(\[embed\])+#i", "#(\[\/embed\])+#i" ), array( "[embed]embed_$0[/embed]", "[embed]", "[/embed]" ), $text );

	return $text;
}