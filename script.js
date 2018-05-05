jQuery(document).ready(function($){
    $('form').submit(function(e) {
        e.preventDefault();

        var resultWrapper, result, resultHeader, resultBody, resultContent;
        $('#results').append(resultWrapper = $('<div>')
            .addClass('col-xl-3 col-md-4 col-sm-6 col-12')
            .append(result = $('<div>')
                .addClass('card text-white bg-secondary')
                .append(resultHeader = $('<div>')
                    .addClass('card-header')
                    .text($("input[name=url]").val() + ' ')
                    .append($('<small>')
                        .text($('select[name=option] option:selected').text())
                    )
                )
                .append(resultBody = $('<div>')
                    .addClass('card-body')
                    .append(resultContent = $('<pre>')
                    )
                )
            )
        );

        function formatText(text) {
            var result = "";
            for(var line of text.split("\n")) {
                var parts = line.split("\r");
                result += parts[parts.length - 1] + "\n";
            }
            return result;
        }

        $.ajax({
            'url': $('form').attr('action'),
            'method': $('form').attr('method'),
            'data': (function() {
                var data = {};
                $('form input, form select').each(function(){
                    data[$(this).attr('name')] = $(this).val();
                });
                return data;
            })(),
            'processData': true,
            'contentType': 'application/x-www-form-urlencoded; charset=UTF-8',
            'xhr': function() {
                var xhr = new XMLHttpRequest();

                xhr.addEventListener('progress', function(e) {
                    resultContent.text(formatText(e.target.response));
                }, false);

                return xhr;
            },
            'success': function(data) {
                resultContent.text(formatText(data));

                result
                    .removeClass('bg-warning')

                if(data.indexOf('exit code: 0') == -1) {
                    result
                        .addClass('bg-danger')
                        .append($('<p>').text('There was an error while processing your request!'));
                        return;
                }

                var anyLink = false;

                for(regex of [
                    /Destination: ([^\n\r$]*)/g,
                    / ([^ ]+) has already been downloaded and merged/g
                ]) {
                    var match;
                    while (match = regex.exec(data)) {
                        if(anyLink) {
                            resultBody.append($('<br/>'));
                        }

                        resultBody.append($('<a>')
                            .addClass('btn btn-sm btn-primary')
                            .attr('href', 'files/' + match[1])
                            .text(match[1])
                        );

                        anyLink = true;
                    }
                }

                if(anyLink) {
                    result
                        .addClass('bg-success');
                } else {
                    result
                        .addClass('bg-danger')
                        .append($('<p>').text('Did not find any downloadable file!'));
                }

            },
            'error': function(jqXHR, textStatus, errorThrown) {
                resultContent.text(formatText(jqXHR.responseText));

                result
                    .removeClass('alert-warning')
                    .addClass('alert-danger')
                    .append($('<p>').text('Request failed!'));
            },
            'complete': function() {
                resultHeader.prepend($('<a>')
                    .html('&times;')
                    .addClass('float-right btn btn-sm btn-danger')
                    .attr('href', '#')
                    .click(function(e) {
                        e.preventDefault();

                        resultWrapper.remove();
                    })
                )
            }
        });
    })
});