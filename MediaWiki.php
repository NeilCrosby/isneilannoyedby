<?php

require_once('PhpCache.php');
require_once('CurlCall.php');

class MediaWiki {
    
    public function __construct( $baseUrl = null ) {
        
    }
    
    public function getArticleAsHtml( $searchText = null ) {
        if ( !$searchText ) {
            return;
        }
        
        $curl = new CurlCall();
        $wikiUrlTitle = urlencode($searchText);

        $wikiText = $this->getWikiText($wikiUrlTitle);

        // first, if wikipedia gives us nothing then search using Yahoo! BOSS for a wiki page
        if ( !$wikiText) {
            $searchTextNoUnderscore = preg_replace( '/_/', ' ', $searchText );
            
            if ( $wikiUrlTitle = $this->getWikiTitleFromBoss($searchTextNoUnderscore) ) {
                $wikiText = $this->getWikiText($wikiUrlTitle);
            }
        }

        if ( $wikiText ) {
            $page = $this->getWikiPageInfo($wikiUrlTitle);
            $wikiText = $this->getWikiHtml($wikiText);
            if ($wikiText) {
                $wikiText .= "<p><a href='http://en.wikipedia.org/wiki/".$page['title']."'>Read more about \"".$page['title']."\" on Wikipedia</a>.</p>";
            }
        } else {
            $wikiText = "Nothing.";
        }

        return $wikiText;
    }
    
    private function getDataFromArray( $aInput, $aKeys=array() ) {
        $current = $aInput;
        foreach ( $aKeys as $key ) {
            if ( !isset($current[$key]) ) {
                return false;
            }
            $current = $current[$key];
        }
        return $current;
    }
    
    private function getWikiTitleFromBoss($searchTerm) {
        $curl = new CurlCall();

        $method = "yahoo.boss";
        $url = "http://boss.yahooapis.com/ysearch/web/v1/site:wikipedia.org+".urlencode($searchTerm)."?appid=63kAAxDIkY1clAVxuReGlwYebC7l3_sQSoYTtEo-";
        $result = $curl->getFromJsonSource($url, array('cache-ident'=>$method));

        if ( $data = $this->getDataFromArray($result, array('ysearchresponse', 'resultset_web', 0)) ) {
            $url = $data['url'];
            $lastSlashPos = strrpos($url, '/wiki/');
            $wikiQuery = substr($url, $lastSlashPos + strlen('/wiki/'));

            $wikiUrlTitle = (strrchr( $wikiQuery, '_' )) ? urlencode($wikiQuery) : urlencode(ucwords($wikiQuery));
            return $wikiUrlTitle;
        }
        
        return false;
    }
    
    private function getWikiPageInfo($wikiUrlTitle) {
        $curl = new CurlCall();

        $method = "wiki.query.title";
        $url = Config::get('WIKI_API_BASE_URL')."&action=query&titles=$wikiUrlTitle&rvprop=content&prop=revisions&rvsection=0&redirects=1";
        $result = $curl->getFromPhpSource($url, array('cache-ident'=>$method));

        $page = false;

        if ( $this->getDataFromArray($result, array('query','pages')) && !$this->getDataFromArray($result, array('query','pages',-1)) ) {
            $page = array_shift($result['query']['pages']);
        }
        
        return $page;
    }
    
    private function getWikiText($wikiUrlTitle) {
        $curl = new CurlCall();

        $method = "wiki.query.title";
        $url = Config::get('WIKI_API_BASE_URL')."&action=query&titles=$wikiUrlTitle&rvprop=content&prop=revisions&rvsection=0&redirects=1";
        $result = $curl->getFromPhpSource($url, array('cache-ident'=>$method));

        $wikiText = false;

        if ( $this->getDataFromArray($result, array('query','pages')) && !$this->getDataFromArray($result, array('query','pages',-1)) ) {
            $page = array_shift($result['query']['pages']);
            $wikiText = array_shift($page['revisions'][0]);
        }
        
        return $wikiText;
    }
    
    private function getWikiHtml($wikiText) {
        $curl = new CurlCall();

        $method = "wiki.parse";
        $url = Config::get('WIKI_API_URL');
        $result = $curl->getFromPhpSourceAsPost(
            $url, 
            array(
                'post-fields'=>"action=parse&format=php&text=".urlencode($wikiText),
                'cache-ident'=>$method
            )
        );

        if ( $wikiText = $this->getDataFromArray($result, array('parse','text','*')) ) {
            
            // now we parse the html to get rid of the crap we don't want
            
            $doc = new DOMDocument();
            // have to give charset otherwise loadHTML gets confused
            $doc->loadHTML(
                '<html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head><body>'.
                $wikiText.
                '</body></html>'
            );

            $xpath = new DOMXPath($doc);

            $queries = array(
                '//*[contains(concat(" ",@class," "), " thumb ")]',
                '//*[contains(concat(" ",@class," "), " metadata ")]',
                '//*[contains(concat(" ",@class," "), " dablink ")]',
                '//*[contains(concat(" ",@class," "), " infobox ")]',
                '//sup[contains(concat(" ",@class," "), " reference ")]',
                '//sup[contains(concat(" ",@class," "), " Template-Fact ")]',
                '//table',
                '//dl',
                '//span[@id="coordinates"]',
            );
            
            foreach ($queries as $query) {
                $entries = $xpath->query($query);

                foreach ($entries as $entry) {
                    $entry->parentNode->removeChild($entry);
                }
            }
            $wikiText = $doc->saveHTML();
            $wikiText = str_replace('<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">', '', $wikiText);
            $wikiText = str_replace('<html>', '', $wikiText);
            $wikiText = str_replace('<head><meta http-equiv="content-type" content="text/html; charset=utf-8"></head>', '', $wikiText);
            $wikiText = str_replace('<body>', '', $wikiText);
            $wikiText = str_replace('</body>', '', $wikiText);
            $wikiText = str_replace('</html>', '', $wikiText);
        }

        return $wikiText;
    }
    
}