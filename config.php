<?php

$nNoToShow = 2;
$aNo = array(
    'PubStandards', 
    'Pub Standards',
    'smiles',
    'Tenacious D',
    'chocolate',
    array('title'=>'Kung Fu Panda', 'description'=>"You can see this film for free at your local cinema, because there is no charge for AWESOME."),
    'his pumpkin',
    array('title'=>'everything', 'description'=>"I'm not annoyed by everything.  It would be a very silly state of affairs if I was."),
    array('title'=>'rats', 'description'=>"I've got two lovely pet rats called Pickle and Peanut.  Contrary to popular belief, rats are highly intelligent and clean animals.  We spoil them rotten, and they can do no wrong."),
    'SubStandards',
    array('title'=>'slicehost', 'description'=>"The people I host this site with.  So far, everything's been easy."),
    '34sp',
    't-shirts',
    array('title'=>'Becca Courtley', 'description'=>"My most lovely fiancée, <a href='http://doesbeccalove.com/everything'>Becca loves everything</a>."),
    array('title'=>'Rebecca Courtley', 'description'=>"My most lovely fiancée, <a href='http://doesbeccalove.com/everything'>Becca loves everything</a>."),
    'lightsabers',
    'LEGO',
    'mermaids',
		'david bowie',
);

$nYesToShow = 5;
$aYes = array(
    array('title'=>'Mike Arrington','description'=>"Who wouldn't be annoyed by this man?  Just look at his face."),
    array('title'=>'yo momma','description'=>"Yo momma so fat she..."),
    'stupidity',
    array('title'=>'lolcats', 'description'=>"OH HAI.  So, you think silly spelling of simplistic phrases is funny do you?  <a href='/FAIL'>FAIL</a>."),
    array('title'=>'Apple Inc.','description'=>"Where to start?  Apple products are lovely, but Apple the Company is starting to annoy the hell out of me."),
    'crowded places',
    'stock control systems',
    'evil teenage girls',
    'overcrowded trains',
    'Hitler',
    'bad acting',
    'liars',
    'FAIL',
    
);

$imgApiUrl = "http://api.flickr.com/services/rest/";
$imgApiKey = "YOU NEED A FLICKR API KEY HERE";
$imgApiFormat = "php_serial";

$imgApiBaseUrl = "$imgApiUrl?api_key=$imgApiKey&format=$imgApiFormat";

$wikiApiUrl = "http://en.wikipedia.org/w/api.php";
$wikiApiFormat = "php";

$wikiApiBaseUrl = "$wikiApiUrl?format=$wikiApiFormat";

class Config {
    private static $WIKI_API_URL      = "http://en.wikipedia.org/w/api.php";
    private static $WIKI_API_FORMAT   = 'php';
    private static $WIKI_API_BASE_URL = "http://en.wikipedia.org/w/api.php?format=php";

    public function get($string=null) {
        if (!$string) {
            return null;
        }
        
        if (!property_exists('Config', $string)) {
            return null;
        }
        
        return self::$$string;
    }
}

function getDataFromArray( $aInput, $aKeys=array() ) {
    if ( !is_array($aInput) ) {
        return false;
    }
    
    $current = $aInput;
    foreach ( $aKeys as $key ) {
        if ( !isset($current[$key]) ) {
            return false;
        }
        $current = $current[$key];
    }
    return $current;
}

function getHtmlAnnoyingAndLovely($aYes, $nYesToShow, $aNo, $nNoToShow) {
    $annoyingHtml = "<h2>Annoying things...</h2><ul>";
    $aRandKeys = array_rand($aYes, $nYesToShow);
    foreach ( $aRandKeys as $key ) {
        $title = $aYes[$key];
        if ($temp = getDataFromArray($title, array('title'))) {
            $title = $temp;
        }

        $url = '/'.urlencode($title);
        $annoyingHtml .= "<li><a href='$url'>$title</a></li>";
    }
    $annoyingHtml .= "</ul>";

    $annoyingHtml .= "<h2>Lovely things...</h2><ul>";
    $aRandKeys = array_rand($aNo, $nNoToShow);
    foreach ( $aRandKeys as $key ) {
        $title = $aNo[$key];
        if ($temp = getDataFromArray($title, array('title'))) {
            $title = $temp;
        }

        $url = '/'.urlencode($title);
        $annoyingHtml .= "<li><a href='$url'>$title</a></li>";
    }
    $annoyingHtml .= "</ul>";
    
    return $annoyingHtml;
}


