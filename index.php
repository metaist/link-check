<?php
function prep_input($name, $length=100) {
  return substr(filter_input(INPUT_GET, $name, FILTER_SANITIZE_STRING),
                0, $length);
}
?><!DOCTYPE html>
<html lang="en-US" class="no-js">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Link Checker by Metaist</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" />
  </head>
  <body>
    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
      ga('create', 'UA-11511623-2', 'auto');
      ga('send', 'pageview');
    </script>
    <div class="container">
      <h1>Link Checker <small>by <a href="http://metaist.com">Metaist</a></small></h1>

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
              <div class="progress-bar" style="min-width: 2em;">0%</div>
            </div>
          </div>
        </div>
      </form>
      <div id="results" class="hidden show-running">
        <ul data-bind="children">
          <li class="csv">
            <span data-bind="title">Title</span> -
            <a href="http://example.com" data-bind="url">http://example.com</a>
            <ul data-bind="children"></ul>
          </li>
        </ul>
      </div>
    </div>

    <script src="http://code.jquery.com/jquery-2.1.3.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    <script src="app.js"></script>
  </body>
</html>
