<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Request Log Viewer</title>

        <script type="text/javascript" src="//code.jquery.com/jquery-3.3.1.min.js"></script>

        <!-- jquery-jsonview -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-jsonview/1.2.3/jquery.jsonview.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-jsonview/1.2.3/jquery.jsonview.min.js"></script>

        <!-- moment -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js"></script>

        <!-- clipboard.js -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.0/clipboard.min.js"></script>

        <!-- List.js -->
        <script src="//cdnjs.cloudflare.com/ajax/libs/list.js/1.5.0/list.min.js"></script>

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
            .request:first-of-type {
                border-top: 1px solid #aaa;
            }
            .request {
                padding: 8px;
                border-bottom: 1px solid #aaa;
            }
            .request:after {
                content: "";
                display: table;
                clear: both;
            }
            .json-view {
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
                <h1 style="padding: 0; margin: 0">Requests</h1>
                <br />
                <div id="requests-panel">
                    <input type="text" class="search" placeholder="Search" />
                    &nbsp;&nbsp;
                    <button class="sort" data-sort="date">Sort by date</button>
                    <br />
                    <br />
                    <div class="list"></div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            var clipboard = null;
            var requestList = null;

            $(document).ready(function ()
            {
                requestList = new List('requests-panel', {
                    valueNames: [ 'date', 'info' ]
                });

               $('.log-file').click(function (ev)
               {
                   $('.log-file').removeClass('log-file-active');
                   $(this).addClass('log-file-active');

                   var id = $(this).data('id');
                   var url = '/api/request-logs/' + id + '/requests';
                   $.get(url, function (response)
                   {
                       var listPanel = $('#requests-panel > .list');
                       listPanel.html('');

                       response.data.forEach(function (request)
                       {
                           var requestDiv = '<div class="request">';

                           requestDiv +=
                               '<div class="js-request" style="cursor: pointer">' +
                                   '<div style="float: left; width: 20%" class="date">' + formatDate(request.date) + '</div>' +
                                   '<div style="float: left; width: 80%" class="info">' +
                                        formatMethod(request.method) + ' ' + formatUri(request.uri) +
                                        '<br />' + formatErrorMessage(request.responseBody.message) + ' [' + formatStatusCode(request.statusCode) + ']' +
                                   '</div>' +
                               '</div>';

                           var requestBodyJson = JSON.stringify(request.requestBody);
                           requestDiv +=
                               '<button class="tooltip js-copy-button">Copy Request Body' +
                                   '<span class="tooltiptext" style="display:none">Copied</span>' +
                               '</button>';
                           requestDiv += '<span style="display:none">' + requestBodyJson + '</span>';

                           requestDiv += '<div class="json-view"></div>';

                           requestDiv += '</div>';
                           listPanel.append(requestDiv);

                           // with options
                           $(".json-view").last().JSONView(request, { collapsed: true });
                       });

                       $('.js-request').click(function (ev)
                       {
                            $(this).siblings('.json-view').fadeToggle();
                       });

                       if (clipboard != null)
                           clipboard.destroy();

                       clipboard = new ClipboardJS('.js-copy-button', {
                           text: function(trigger) {
                               return trigger.nextElementSibling.innerHTML;
                           }
                       });
                       clipboard.on('success', function (e)
                       {
                            $(e.trigger).find('> .tooltiptext')
                                .show()
                                .delay(2000)
                                .fadeOut();
                       });

                       requestList.reIndex();

                   }).fail(function ()
                   {
                       alert("Failed to load requests");
                   });
               });

               // auto-load first (latest) log file
               $('.log-file')[0].click();
            });

            function formatMethod (method)
            {
                var color;
                switch (method)
                {
                    case 'POST':
                        color = '#ff6f00';
                        break;
                    case 'PUT':
                        color = '#428bca';
                        break;
                    case 'DELETE':
                        color = '#f50057';
                        break;
                    case 'GET':
                        color = '#00b248';
                        break;
                    default:
                        color = '#000';
                        break;
                }
                return '<span style="color: ' + color + '; font-weight: bold;">' + method + '</span>';
            }

            function formatDate (date)
            {
                var formattedDate = moment(date).format('YYYY-MM-DD hh:mm:ss');
                return '<span style="font-weight: bold;">' + formattedDate + '</span>';
            }

            function formatUri (uri)
            {
                return '<span style="font-weight: bold;">' + uri + '</span>';
            }

            function formatErrorMessage (errorMessage)
            {
                return errorMessage;
            }

            function formatStatusCode (statusCode)
            {
                return '<span style="color: red; font-weight: bold;">' + statusCode + '</span>';
            }

        </script>
    </body>
</html>

