<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
    "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title>Is Neil Annoyed By?</title>
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.5.2/build/reset-fonts-grids/reset-fonts-grids.css"> 
    <link rel="stylesheet" type="text/css" href="http://yui.yahooapis.com/2.5.2/build/base/base-min.css">
    <link rel="stylesheet" type="text/css" href="/style.css">
    <link rel="search" type="application/opensearchdescription+xml" href="/opensearch.xml" title="Is Neil Annoyed By?">
    
</head>
<body>
    <div id="doc4" class="yui-t4">
        <div id="hd">
            <form action="/process" method="get">
                <h1>
                    <label for="hate">Is Neil Annoyed By</label>
                    <input type="text" name="hate" id="hate">
                    ?
                </h1>
            </form>
        </div>
        <div id="bd">
            <div id="yui-main">
                <div class="yui-b">
                    <div class="yui-g">
                            <p>
                                To see if Neil is annoyed by something, just 
                                type it above.
                            </p>
                            <p>
                                This site is mashed together using a
                                combination of Flickr, Wikipedia, Yahoo! BOSS
                                and OpenSearch APIs.
                            </p>
                            <p>
                                MOAR coming soon.
                            </p>
                    </div>
                </div>
            </div>
            <div class="yui-b">
                <?php
                    require_once('config.php');
                    echo getHtmlAnnoyingAndLovely($aYes, $nYesToShow, $aNo, $nNoToShow);
                ?>
            </div>
        </div>
        <div id="ft">
            "Is Neil Annoyed By?" is a fictional site that isn't based on a 
            real person at all.  That's right kids - any similarities to
            anyone, living or dead, is entirely coincidental.
        </div>
    </div>
</body>
</html>