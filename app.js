$(function () {
  var app = {};
  window.app = app;

  app.xhr = [];
  app.$list = $('#results tbody'); // result list
  app.$tmpl = $('#results tbody tr').remove(); // result template
  app.$progress = $('.progress-bar');

  // Start the process.
  app.start = function (url) {
    if (url) { app.queue.push(url); }
    if (app.queue.length) {
      app.pid = window.setInterval(app.next, 100);
      $('.progress, #results').removeClass('hidden');
    }//end if: started

    return app;
  };

  // Stop the process.
  app.stop = function () {
    if (app.pid) { window.clearInterval(app.pid); }
    app.pid = false;

    return app;
  };

  // Reset the process.
  app.reset = function () {
    $('#results tbody tr').remove(); // clear table
    $('.progress, #results').addClass('hidden'); // hide progress / table

    app.count = 0; // number done
    app.done = {}; // processed urls (fast lookup)
    app.queue = []; // urls to process

    return app;
  };

  app.next = function () {
    if (app.queue.length && app.xhr < 3) {
      app.process(app.queue.shift());
    }//end if: process next item

    return app;
  };

  app.progress = function () {
    var percent = (app.count / (app.count + app.queue.length)) * 100;
    app.$progress
      .text(parseInt(percent, 10) + '%')
      .css({width: percent + '%'})
      .parent()
        .toggleClass('hidden', 100 === percent);

    return app;
  };

  app.process = function (url) {
    if (app.done[url]) {
      app.done[url] += 1;
      return app;
    }//end if: already handled

    app.xhr += 1;
    $.getJSON('url.php', {url: url}, function (data) {
      app.xhr -= 1;
      if (!app.pid) { return; }
      data = data.data;

      app.$tmpl.clone()
        .attr('id', 'url-' + data.id)
        .find('[data-bind="url"]')
          .attr('href', data.url)
          .text(data.url)
        .end()
        .find('[data-bind="title"]').text(data.title || ' ').end()
        .find('[data-bind="heading1"]').text(data.heading1 || ' ').end()
        .find('[data-bind="heading2"]').text(data.heading2 || ' ').end()
        .appendTo(app.$list);

      app.done[url] = 1;
      app.count += 1;

      $.each(data.internal, function (i, url) {
        if (app.done[url]) { // already done
          app.done[url] += 1;
        } else if (-1 === $.inArray(url, app.queue)) { // not in queue
          app.queue.push(url);
        }//end if: added new urls
      });

      app.progress();
      if (!app.queue.length) { return app.stop(); }
    });

    return app;
  };

  $('form').submit(function (e) {
    e.preventDefault();
    app.stop()
       .reset()
       .start($('#url').val());
  });
});