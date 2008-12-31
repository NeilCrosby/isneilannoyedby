<?php

function removeMagicQuotes (&$postArray, $trim = false)
{
  if (!get_magic_quotes_gpc())
  {
    return;
  }

  foreach ($postArray as $key => $val)
  {
    if (is_array($val))
    {
      removeMagicQuotes ($postArray[$key], $trim);
    } else {
      if ($trim == true)
      {
        $val = trim($val);
      }
      $postArray[$key] = stripslashes($val);
    }
  }   
}

removeMagicQuotes($_GET);
