<?php
function prep_input($name, $length=100) {
  return substr(filter_input(INPUT_GET, $name, FILTER_SANITIZE_STRING),
                0, $length);
}
?><!DOCTYPE html>
<html lang="en-US" class="no-js"><head>
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Link Checker by Metaist</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" />
  <style>
    .progress .count {
      line-height: 20px;
      font-size: 12px;
      right: 20px;
      position: absolute;
    }

    nav {
      border-bottom: 0.1em solid #ccc;
      padding-bottom: 1em;
      margin-bottom: 1em;
    }

    nav:last-child {
      border-bottom: 0;
      padding-bottom: 0;
      margin-bottom: 0;
    }
  </style>
</head><body>
<?php
if (file_exists('google-analytics.php')) { require('google-analytics.php'); }
?>
  <div class="container">
    <h1>
      Link Checker
      <small>by <a href="http://metaist.com">Metaist</a></small>
    </h1>
    <form class="form-horizontal">
      <div class="form-group">
        <div class="col-sm-10">
          <input id="url" class="form-control" placeholder="Enter a URL"
                 value="<?php echo prep_input('url', 256); ?>"/>
        </div>
        <div class="col-sm-2">
          <a id="btn-start" class="btn btn-primary" title="Run">
            <i class="glyphicon glyphicon-play"></i>
          </a>
          <a id="btn-stop" class="btn btn-danger hidden" title="Stop">
            <i class="glyphicon glyphicon-stop"></i>
          </a>
          <a id="btn-download" class="btn btn-success hidden"
             title="Download">
            <i class="glyphicon glyphicon-download"></i>
          </a>
        </div>
      </div>
      <div class="form-group hidden show-running">
        <div class="col-sm-10">
          <div class="progress">
            <span class="count pull-right">0%</span>
            <div class="progress-bar"></div>
          </div>
        </div>
      </div>
    </form>
    <div id="results" class="hidden show-running">
      <nav>
        <strong class="title"></strong>
        <ul class="items">
          <li class="csv">
            <span class="title">Title</span> -
            <a href="http://example.com" class="url">http://example.com</a>
          </li>
        </ul>
      </nav>
    </div>
  </div>

  <script src="http://code.jquery.com/jquery-2.1.3.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
  <script src="app.js"></script>
</body></html>
