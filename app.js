$(function () {
  var app = {};
  window.app = app;

  app.pid = false; // interval id
  app.xhr = 0; // counter semaphore
  app.count = 0; // number done
  app.seen = {}; // exposed urls (fast lookup)
  app.queue = []; // urls to process

  app.$tmpl = $('#results li').remove(); // result template
  app.$progress = $('.progress-bar');

  // Start the process.
  app.start = function (url) {
    if (url) {
      $.getJSON('url.php', {url: url}).done(function (data) {
        data = data.data;
        app.add({
            parent: '#results',
            id: data.id,
            fingerprint: data.fingerprint,
            title: data.title,
            url: data.url,
            depth: 0
        });
      });

      app.pid = window.setInterval(app.next, 100);
      app.next(); // immediate
      $('.show-running, #btn-stop').removeClass('hidden');
      $('#btn-start, #btn-download').addClass('hidden');
    }//end if: started

    return app;
  };

  // Stop the process.
  app.stop = function () {
    $('#btn-stop').addClass('hidden');
    $('#btn-start').removeClass('hidden');
    if (app.pid) { window.clearInterval(app.pid); }
    app.pid = false;
    app.progress();

    return app;
  };

  // Reset the process.
  app.reset = function () {
    $('#results li').remove(); // clear results
    $('.show-running, #btn-stop, #btn-download').addClass('hidden');
    $('#btn-start').removeClass('hidden');

    app.count = 0;
    app.seen = {};
    app.queue = [];

    return app;
  };

  // Add to the queue.
  app.add = function (item) {
    if (app.seen[item.url]) { // old
      app.seen[item.url] += 1;
    } else { // new
      app.seen[item.url] = 1;
      app.queue.push(item);

      app.$tmpl.clone()
        .data('csv', item)
        .addClass('text-muted url-' + item.id)
        .find('[data-bind="url"]')
          .attr('href', item.url)
          .text(item.url)
        .end()
        .find('[data-bind="title"]').html(item.title || ' ').end()
        .appendTo($(item.parent + ' > [data-bind="children"]'));
    }//end if: track url

    return app;
  };

  // Process next item in queue.
  app.next = function () {
    if (!app.pid) { app.stop(); }
    if (app.queue.length && app.xhr < 4) {
      app.process(app.queue.shift());
    }//end if: process next item

    return app;
  };

  app.csv = function () {
    var content = 'data:text/csv;charset=utf-8,';
    $('.csv').each(function () {
      var data = $(this).data('csv');
      content += Array(data.depth + 1).join(',') +
                 '"' + data.title + ' - ' + data.url + '"\n';
    });

    $('#btn-download')
      .attr('href', encodeURI(content))
      .attr('download', encodeURI($('#url').val()) + '.csv')
      .removeClass('hidden');

    return app;
  };

  app.progress = function () {
    var total = app.count + app.queue.length;
    if (total <= 0) { total = 1; }

    var percent = (app.count / total) * 100;
    app.$progress
      .text(parseInt(percent, 10) + '% (' + app.queue.length + ' left)')
      .css({width: percent + '%'})
      .parent()
        .toggleClass('hidden', 100 === percent);

    return app;
  };

  app.process = function (item) {
    app.xhr += 1;
    $.getJSON('url.php', {url: item.url}).always(function () {
      app.xhr -= 1;
    }).done(function (data) {
      if (!app.pid) { return; }
      data = data.data;
      app.count += 1;

      if ($('.uid-' + data.fingerprint).length) { // already exists
        $('.url-' + data.id).remove();
      } else {
        $('.url-' + data.id)
          .addClass('uid-' + data.fingerprint)
          .removeClass('text-muted');

        $.each(data.nav, function (i, link) {
          app.add($.extend(link, {
            parent: '.url-' + data.id,
            depth: item.depth + 1
          }));
        });
      }

      app.progress();
      if (!app.queue.length) {
        return app.stop().csv();
      }//end if: finished all links
    });

    return app;
  };

  $('form').submit(function (e) {
    e.preventDefault();
    $('#btn-start').click();
  });

  $('#btn-start').click(function () {
    app.stop()
       .reset()
       .start($('#url').val());
  });

  $('#btn-stop').click(function () {
    app.stop()
       .csv();
  });
});
