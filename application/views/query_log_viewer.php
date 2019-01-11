<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Query Log Viewer</title>

        <script type="text/javascript" src="//code.jquery.com/jquery-3.3.1.min.js"></script>

        <!-- moment -->
        <script src="//cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js"></script>

        <!-- clipboard.js -->
        <script src="//cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.0/clipboard.min.js"></script>

        <!-- Clusterize.js -->
        <link href="//cdnjs.cloudflare.com/ajax/libs/clusterize.js/0.18.0/clusterize.min.css" rel="stylesheet">
        <script src="//cdnjs.cloudflare.com/ajax/libs/clusterize.js/0.18.0/clusterize.min.js"></script>



        <style>
            * {
                color: #444;
            }
            .log-file {
                height: 32px;
                margin-bottom: 8px;
                cursor: pointer;
            }
            .log-file-active {
                background: #fff176;
            }
            #list {
                width: 100%;
                max-height: 640px;
            }
            .query:first-of-type {
                border-top: 1px solid #aaa;
            }
            .query {
                padding: 8px;
                border-bottom: 1px solid #aaa;
            }
            .query:after {
                content: "";
                display: table;
                clear: both;
            }
            .query-view {
                max-width: 1024px;
                overflow-wrap: break-word;
                padding: 16px;
                background: #eee;
                display: none;
            }

            /* Tooltip - https://www.w3schools.com/css/css_tooltip.asp */
            .tooltip {
                position: relative;
                display: inline-block;
            }
            .tooltip .tooltiptext {
                width: 64px;
                background-color: black;
                color: #fff;
                text-align: center;
                border-radius: 6px;
                padding: 5px 0;

                /* Position the tooltip */
                position: absolute;
                z-index: 1;
                top: -5px;
                left: 105%;
            }
        </style>
    </head>
    <body style="padding: 16px">
        <div style="display: flex">
            <div class="logs-panel" style="float: left; min-width: 256px; padding-right: 16px;">
                <h1 style="padding: 0; margin: 0">Logs</h1>
                <br />
                <ul>
                <?php
                foreach ($logs as $log)
                {
                    echo '<li class="log-file" data-id="'. $log->id .'">'. $log->name .'</li>';
                }
                ?>
                </ul>
            </div>
            <div style="float: left; flex: 1">
                <h1 style="padding: 0; margin: 0">Queries</h1>
                <br />
                <div id="queries-panel">
                    <!-- TODO <input type="text" class="search" placeholder="Search" disabled/> -->
                    &nbsp;&nbsp;
                    <br />
                    <br />
                    <div id="list" class="clusterize-scroll">
                        <div id="list-content" class="clusterize-content">

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            var clipboard = null;

            $(document).ready(function ()
            {
               $('.log-file').click(function (ev)
               {
                   $('.log-file').removeClass('log-file-active');
                   $(this).addClass('log-file-active');

                   $('#list-content').html('<div class="clusterize-no-data">Loading data…</div>');

                   var id = $(this).data('id');
                   var url = '/api/query-logs/' + id + '/logs';
                   $.get(url, function (response)
                   {
                       var list = [];

                       response.data.forEach(function (log)
                       {
                           var queryDiv = '<div class="query">';

                           var query = log.query;
                           var previewQuery = getPreviewQuery(log.query);
                           var ipAddress = '<code>' + log.ipAddress + '</code>';

                           queryDiv +=
                               '<div class="js-query" style="display: table; cursor: pointer;">' +
                                    '<div style="float: left; width: 192px" class="date">' + formatDate(log.date) + '</div>' +
                                    '<div style="float: left; min-width: 480px">' +
                                        previewQuery + '<br />' + ipAddress +
                                    '</div>' +
                               '</div>';

                           queryDiv += '<div>' +
                               '<button class="tooltip js-copy-button">Copy Query' +
                                    '<span class="tooltiptext" style="display:none">Copied</span>' +
                                    '<span style="display:none" class="info">' + query + '</span>' +
                               '</button>' +
                           '</div>';

                           queryDiv += '<div class="query-view"><code>' + query + '</code></div>';

                           queryDiv += '</div>';
                           list.push(queryDiv);
                       })

                      if (clipboard != null)
                           clipboard.destroy();

                       clipboard = new ClipboardJS('.js-copy-button', {
                           text: function(trigger) {
                               return trigger.getElementsByClassName('info')[0].innerHTML;
                           }
                       });
                       clipboard.on('success', function (e)
                       {
                            $(e.trigger).find('> .tooltiptext')
                                .show()
                                .delay(2000)
                                .fadeOut();
                       });

                       new Clusterize({
                           rows: list,
                           scrollId: 'list',
                           contentId: 'list-content'
                       });
                   }).fail(function ()
                   {
                       alert("Gagal memuat log");
                   });
               });

                $(document).on('click', '.js-query', function (ev)
                {
                    $(this).siblings('.query-view').fadeToggle();
                });
            });

            function formatDate (date)
            {
                var formattedDate = moment(date).format('YYYY-MM-DD HH:mm:ss');
                return '<span style="font-weight: bold;">' + formattedDate + '</span>';
            }

            function getPreviewQuery (query)
            {
                var firstSpaceIdx = query.indexOf(' ');

                var method = query.substring(0, firstSpaceIdx);
                method = formatMethod(method);

                query = query.substring(firstSpaceIdx + 1);
                query = formatQuery(query);

                return method + ' ' + query;
            }

            function formatMethod (method)
            {
                var color;
                switch (method)
                {
                    case 'INSERT':
                        color = '#ff6f00';
                        break;
                    case 'UPDATE':
                        color = '#428bca';
                        break;
                    case 'DELETE':
                        color = '#f50057';
                        break;
                    case 'SELECT':
                        color = '#00b248';
                        break;
                    default:
                        color = '#000';
                        break;
                }
                return '<span style="color: ' + color + '; font-weight: bold;">' + method + '</span>';
            }

            function formatQuery (query)
            {
                if (query.length > 80)
                    query = query.substring(0, 80) + "...";

                return '<b><code>' + query + '</code></b>';
            }
        </script>
    </body>
</html>

