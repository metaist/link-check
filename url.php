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

$IN_callback = prep_input('callback', 100);
$IN_url = prep_input('url', 256);
// inputs sanitized

$OUT = array(
  'ok' => true,
  'status' => 'success',
  'data' => null
); // output prepared

$IN_url = normalize_url($IN_url);
$localhost = split_url($IN_url)['host'];

$result = array(
  'id' => sha1($IN_url),
  'url' => $IN_url,
  'title' => '',
  'heading1' => '',
  'heading2' => '',

  'headings' => array(),

  // Links
  'internal' => array(),
  'external' => array(),
  'errors' => array()
);

$html = file_get_html($IN_url); // HTML fetched
$result['title'] = $html->find('title', 0)->plaintext;

foreach($html->find('a') as $element) {
  $url = url_to_absolute($IN_url, $element->href);
  if (!$url) { // error
    array_push($result['errors'], $element->href);
  } else { // ok
    $host = split_url($url)['host'];
    if ($localhost == $host) { // internal
      array_push($result['internal'], $url);
    } else { // external
      array_push($result['external'], $url);
    }//end if: added valid url
  }//end if: added url
}//end for: added all urls

$result['internal'] = array_unique($result['internal']);
$result['external'] = array_unique($result['external']);
$result['errors'] = array_unique($result['errors']);
// duplicates removed

for($i = 1; $i <= 6; $i++) {
  $result['headings'][$i] = array();
  foreach($html->find("h$i") as $element) {
    $heading = trim($element->plaintext);
    array_push($result['headings'][$i], $heading);
    if (empty($result["heading$i"])) { $result["heading$i"] = $heading; }
  }//end for: added headings
}//end for: added all heading levels

$OUT['data'] = $result;
$json = json_encode($OUT);
echo (empty($IN_callback) ? $json : "$IN_callback($json);");
