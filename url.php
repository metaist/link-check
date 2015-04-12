<?php
require('lib/simple_html_dom.php');
require('lib/http_build_url.php');
require('lib/url_to_absolute.php');

header('Content-type: application/json');

function prep_input($name, $length=100) {
  return substr(filter_input(INPUT_GET, $name, FILTER_SANITIZE_STRING),
                0, $length);
}

function normalize_url($url) {
  $parts = split_url($url);
  if (!array_key_exists('scheme', $parts)) { $parts['scheme'] = 'http'; }
  if (!array_key_exists('host', $parts)) {
    $parts['host'] = $parts['path'];
    $parts['path'] = '';
  }//end if: fixed example.com hosts
  return join_url($parts);
}

function get_domain($url) {
  $host = split_url($url)['host'];
  $parts = explode('.', $host);
  return implode('.', array_slice($parts, -2));
}

$IN_callback = prep_input('callback', 100);
$IN_url = prep_input('url', 256);
// inputs sanitized

$OUT = array(
  'ok' => true,
  'status' => 'success',
  'data' => null
); // output prepared

$IN_url = normalize_url($IN_url);
$domain = get_domain($IN_url);

$result = array(
  'id' => sha1($IN_url),
  'fingerprint' => '',
  'url' => $IN_url,
  'title' => '',

  // Links
  'errors' => array(),
  'nav_links' => array(),
  'nav' => array()
);

$html = file_get_html($IN_url); // HTML fetched
$result['fingerprint'] = sha1($html->plaintext);
$result['title'] = $html->find('title', 0)->plaintext;

foreach($html->find('nav') as $nav) {
  foreach($nav->find('a') as $link) {
    $url = url_to_absolute($IN_url, $link->href);
    if (!$url) { // error
      array_push($result['errors'], $link->href);
    } else { // maybe
      $host = get_domain($url);
      if ($domain == $host && !in_array($url, $result['nav_links'])) { // ok
        array_push($result['nav_links'], $url);
        array_push($result['nav'], array(
          'id' => sha1($url),
          'url'=> $url,
          'title'=> $link->plaintext,
        ));
      }
    }//end if: added link
  }//end for: added all links
}//end for: added all navs

$result['errors'] = array_unique($result['errors']);  // duplicates removed

$OUT['data'] = $result;
$json = json_encode($OUT);
echo (empty($IN_callback) ? $json : "$IN_callback($json);");
