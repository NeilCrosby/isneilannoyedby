<?php

// third party requires
require_once('PhpCache.php');

// requires for this site
require_once('config.php');
require_once('init.php');
require_once('CurlCall.php');

function getClosestString($needle, $haystack, $caseSensitive = false) {
    if (!$caseSensitive) {
        $needle = strtolower($needle);
    }
    $needleLength = strlen($needle);
    
    $closest = null;
    $minLevPercent = 100000;
    
    foreach ( $haystack as $item ) {
        $title = $item;
        if ($temp = getDataFromArray($item, array('title'))) {
            $title = $temp;
        }
        
        $titleToUse = ($caseSensitive) ? $title : strtolower($title);
        
        $dist = levenshtein( $titleToUse, $needle );
        $percent = 100 * $dist / $needleLength;
        if ( $percent < $minLevPercent ) {
            $minLevPercent = $percent;
            $closest = $title;
        }
    }
    return $closest;
}


$hatedOriginal = $_GET['hate'];
$hatedNoUnderscore = preg_replace( '/_/', ' ', $hatedOriginal );

$hated = strtolower($hatedOriginal);


$answerString = "Probably!";

$minLevPercent = 100000;
$hatedLength = strlen($hated);
$currentDescription = '';

$closestYes = getClosestString( $hated, $aYes );
$closestNo = getClosestString( $hated, $aNo );

$levPercentClosestYes = 100 * levenshtein( strtolower($hated), strtolower($closestYes) ) / strlen($hated);
$levPercentClosestNo  = 100 * levenshtein( strtolower($hated), strtolower($closestNo) ) / strlen($hated);

$file = 'yes.tmpl';
if ( $levPercentClosestYes <= $levPercentClosestNo ) {
    if ( $levPercentClosestYes < 10 ) {
        $answerString = "Definitely Yes!";
        foreach ( $aYes as $item ) {
            if ( $closestYes == getDataFromArray($item, array('title')) ) {
                $currentDescription = getDataFromArray($item, array('description'));
            }
        }
    }
} else {
    if ( $levPercentClosestNo < 10 ) {
        $answerString = "No!";
        $file = 'no.tmpl';
        foreach ( $aNo as $item ) {
            if ( $closestNo == getDataFromArray($item, array('title')) ) {
                $currentDescription = getDataFromArray($item, array('description'));
            }
        }
    }
}

$tmpl = file_get_contents($file);
$tmpl = str_replace('%%hated%%', htmlentities($hatedNoUnderscore), $tmpl);

$annoyingHtml = getHtmlAnnoyingAndLovely($aYes, $nYesToShow, $aNo, $nNoToShow);

$tmpl = str_replace('%%annoying-things%%', $annoyingHtml, $tmpl);

// now we do the flickr image stuff

$temp = $hated;
$urlHated = urlencode($temp);

$curl = new CurlCall();

$imgAttribution = '';
$imgUrl = '';
$imgLink = '';

$method = 'flickr.photos.search';
$url = "$imgApiBaseUrl&method=$method&text=\"$urlHated\"&sort=relevance&safe_search=1&media=photos&per_page=1";
$result = $curl->getFromPhpSource($url, array('cache-ident'=>$method));

// if we didn't find an image, try again with a different search
if ( !getDataFromArray($result, array('photos', 'photo', '0')) ) {
    $method = 'flickr.photos.search';
    $url = "$imgApiBaseUrl&method=flickr.photos.search&text=$urlHated&sort=relevance&safe_search=1&media=photos&per_page=1";
    $result = $curl->getFromPhpSource($url, array('cache-ident'=>$method));
}

if ( $img = getDataFromArray($result, array('photos', 'photo', '0')) ) {
    
    // lets try and get some info about the specific user
    $method = 'flickr.people.getInfo';
    $url = "$imgApiBaseUrl&method=$method&user_id=${img['owner']}";
    $result = $curl->getFromPhpSource($url, array('cache-ident'=>$method));

    $ownerHtml = $img['owner'];
    
    if ( isset($result['person']) ) {
        $profileUrl = $result['person']['profileurl']['_content'];
        $profileUser = $result['person']['username']['_content'];
        
        $ownerHtml = "<a href='$profileUrl'>$profileUser</a>";
    }
    
    $imgUrl = "http://farm{$img['farm']}.static.flickr.com/{$img['server']}/{$img['id']}_{$img['secret']}.jpg";
    $imgLink = "http://www.flickr.com/photos/{$img['owner']}/{$img['id']}";
    $imgAttribution = "<p><a href='$imgLink'>${img['title']}</a> by $ownerHtml on Flickr</p>";
}


require_once('externals/wikislurp/name/client/WikiSlurpClient.php');

$query = strrchr( $hatedOriginal, '_' ) ? $hatedOriginal : ucwords($hatedNoUnderscore);

$wiki = new WikiSlurpClient();
$result = $wiki->getData(
	//"http://".$_SERVER['SERVER_NAME'].":".$_SERVER['SERVER_PORT']."/externals/wikislurp/name/index.php",
    "http://".$_SERVER['SERVER_NAME'].":8888/externals/wikislurp/name/index.php", // hardcoding for now
	$wikiSlurpSecret,
	$query,
	array(
		'xpath'	  => '/html/body/p[position()<=3]',
		'section' => 0,
	)
);

if ( isset($result['error']) ) {
    $wikiText = 'Nothing';
} else {
    $wikiText = $result['html']
              . "<p><a href='".$result['url']."'>Read more about \"".$result['title']."\" on Wikipedia</a>.</p>";
}

// turn any wikipedia links into links for this site
$wikiText = preg_replace( '/<a href="\/wiki\//', '<a href="/', $wikiText );

// now string replace all the things we need to replace
$tmpl = str_replace('%%answer-string%%', $answerString, $tmpl);
$tmpl = str_replace('%%background-image%%', $imgUrl, $tmpl);
$tmpl = str_replace('%%background-image-link%%', $imgLink, $tmpl);
$tmpl = str_replace('%%background-image-attributation%%', $imgAttribution, $tmpl);
$tmpl = str_replace('%%wikipedia-says%%', $wikiText, $tmpl);
$tmpl = str_replace('%%current-description%%', $currentDescription, $tmpl);

// finally output the page
echo $tmpl;
