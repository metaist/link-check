<?php
require('lib/simple_html_dom.php');
require('lib/http_build_url.php');
require('lib/url_to_absolute.php');

header('Content-type: application/json');

function prep_input($name, $length=100) {
  return substr(filter_input(INPUT_GET, $name, FILTER_SANITIZE_STRING),
                0, $length);
}

function url_normalize($url, $stripFragment=false) {
  $parts = split_url($url);
  if (!array_key_exists('scheme', $parts)) { $parts['scheme'] = 'http'; }
  if (!array_key_exists('host', $parts)) {
    $parts['host'] = $parts['path'];
    $parts['path'] = '';
  }//end if: fixed example.com hosts

  if ($stripFragment) { $parts['fragment'] = null; }

  return rawurldecode(join_url($parts));
}

function url_domain($url) {
  $host = split_url($url)['host'];
  $parts = explode('.', $host);
  return implode('.', array_slice($parts, -2));
}

function selector($dom) {
  $parts = array();
  $parts[] = $dom->tag;
  if ($dom->id) {
    $parts[] = '#' . $dom->id;
  } else if ($dom->class) {
    $classes = array_unique(explode(' ', $dom->class));
    $parts[] = '.' . implode('.', $classes);
  }

  return implode('', $parts);
}

$IN_callback = prep_input('callback', 100);
$IN_url = url_normalize(prep_input('url', 256), true);
// INPUT: sanitized

$OUT = array(
  'ok' => true,
  'status' => 'success',
  'data' => null
);
// OUTPUT: prepared

$HTAGS = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'strong');
$DOMAIN = url_domain($IN_url);
$html = file_get_html($IN_url); // document HTML
$unique_links = array($IN_url); // unique list of links

$result = array(
  'url' => $IN_url,
  'id' => sha1($IN_url),
  'hash' => sha1($html->plaintext),
  'title' => trim($html->find('title', 0)->plaintext),
  'nav' => array()
);

function process_node($node) {
  global $IN_url, $HTAGS, $DOMAIN, $unique_links;
  $result = array(
    //'selector' => selector($node),
    'id' => sha1($node->plaintext),
    'url' => '',
    'title' => '',
    'items' => array()
  );

  foreach($node->children() as $child):
    switch($child->tag):
    case 'a':
      $url = rawurldecode(url_to_absolute($IN_url, $child->href));
      //if (in_array($url, $unique_links)) { continue; } // skip duplicates
      if ($DOMAIN == url_domain($url)) { // internal link
        //$unique_links[] = $url; // prevent duplicates
        $result = array_merge($result, array(
          'id' => sha1($url),
          'url'=> $url,
          'title'=> trim($child->plaintext)
        ));
      }//end if: added internal link
      break;
    default: // li, div, etc.
      $subresult = process_node($child);
      if ('' == $subresult['title']) {
        $result['items'] = array_merge($result['items'], $subresult['items']);
      } else {
        $result['items'][] = process_node($child, $depth);
      }//end if: merged result up a level
    endswitch;
  endforeach;

  if (empty($result['title'])) {
    $prev = $node->prev_sibling();
    if ($prev && in_array($prev->tag, $HTAGS)) {
      $result['title'] = trim($prev->plaintext);
    }//end if: found a heading
  }//end if: tried to find a heading

  return $result;
}

foreach($html->find('ul') as $list) {
  if ('li' == $list->parent()->tag) { continue; } // skip nested
  $subresult = process_node($list);
  if (count($subresult['items']) > 0) { $result['nav'][] = $subresult; }
}//end for: checked all lists


$OUT['data'] = $result;
$json = json_encode($OUT);
echo (empty($IN_callback) ? $json : "$IN_callback($json);");
