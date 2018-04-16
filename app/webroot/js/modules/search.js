define(function () {
    // ヘッダーの検索ボックス処理
    var headerSearch = {
        setup: function () {
            var $NavSearchForm = $('#NavSearchForm');
            var $NavSearchInput = $('#NavSearchInput');
            var $NavSearchResults = $('#NavSearchResults');
            var keyupTimer = null;
            var cache = {
                user: {},
                goal: {},
                circle: {}
            };

            var config = {
                user: {
                    url: cake.url.a,
                    link_base: cake.url.user_page,
                    label: cake.word.members
                },
                goal: {
                    url: cake.url.select2_goals,
                    link_base: cake.url.goal_page,
                    label: cake.word.goals
                },
                circle: {
                    url: cake.url.select2_circles,
                    link_base: cake.url.circle_page,
                    label: cake.word.circles
                }
            };

            $NavSearchForm
                // Enter 押しても submit させないようにする
                .on('submit', function (e) {
                    e.preventDefault();
                    return false;
                });

            $NavSearchInput
                .on('keydown', function (e) {
                    // down
                    if (e.keyCode == 40) {
                        e.preventDefault();
                        $NavSearchResults.find('.nav-search-result-item:first').focus();
                    }
                })
                .on('keyup', function (e) {
                    // 検索文字列
                    var inputText = $(this).val();
                    if(inputText.length){
                        $("#NavSearchInputClear").show();
                    } else {
                        $("#NavSearchInputClear").hide();
                    }

                    // キー連打考慮してすこし遅らせて ajax リクエストする
                    // clearTimeout(keyupTimer);
                    // keyupTimer = setTimeout(function () {
                        // 入力テキストが空
                        if (inputText.length == 0) {
                            $NavSearchResults.hide();
                            return;
                        }

                        var ajaxUser = $.get(config['user'].url, {
                            term: inputText,
                            page_limit: 10
                        });

                        var ajaxGoal = $.get(config['goal'].url, {
                            term: inputText,
                            page_limit: 10
                        });

                        var ajaxCircle = $.get(config['circle'].url, {
                            term: inputText,
                            page_limit: 10
                        });

                        $.when(ajaxUser, ajaxGoal, ajaxCircle).done(function(userResult, goalResult, circleResult){
                           // a1, a2 and a3 are arguments resolved 
                           // for the ajax1, ajax2 and ajax3 Ajax requests, respectively.

                           // Each argument is an array with the following structure:
                           // [ data, statusText, jqXHR ]
                            var $notFoundText = $('<div id="notFoundElement">')
                                .text(cake.message.notice.search_result_zero)
                                .addClass('nav-search-result-notfound');
                            $NavSearchResults.empty().append($notFoundText);

                           if (userResult && userResult[0].results && userResult[0].results.length) {
                                $('#notFoundElement').remove();
                                var $userLabel = $('<div>')
                                    .text(config['user'].label)
                                    .addClass('nav-search-result-label');
                                $NavSearchResults.append($userLabel);
                                for (var i = 0; i < userResult[0].results.length; i++) {
                                    var $row = $('<a>')
                                        .addClass('nav-search-result-item user-select')
                                        .attr('href', config['user'].link_base + userResult[0].results[i].id.split('_').pop());

                                    // image
                                    var $img = $('<img>').attr('src', userResult[0].results[i].image);
                                    $row.append($img);

                                    // text
                                    var $text = $('<span>').text(userResult[0].results[i].text);
                                    $row.append($text);
                                    $row.appendTo($NavSearchResults);
                                }
                            }
                            if (goalResult && goalResult[0].results && goalResult[0].results.length) {
                                $('#notFoundElement').remove();
                                var $goalLabel = $('<div>')
                                    .text(config['goal'].label)
                                    .addClass('nav-search-result-label');
                                $NavSearchResults.append($goalLabel);
                                for (var i = 0; i < goalResult[0].results.length; i++) {
                                    var $row = $('<a>')
                                        .addClass('nav-search-result-item goal-select')
                                        .attr('href', config['goal'].link_base + goalResult[0].results[i].id.split('_').pop());

                                    // image
                                    var $img = $('<img>').attr('src', goalResult[0].results[i].image);
                                    $row.append($img);

                                    // text
                                    var $text = $('<span>').text(goalResult[0].results[i].text);
                                    $row.append($text);
                                    $row.appendTo($NavSearchResults);
                                }
                            }
                           if (circleResult && circleResult[0].results && circleResult[0].results.length) {
                                $('#notFoundElement').remove();
                                var $circleLabel = $('<div>')
                                    .text(config['circle'].label)
                                    .addClass('nav-search-result-label');
                                $NavSearchResults.append($circleLabel);
                                for (var i = 0; i < circleResult[0].results.length; i++) {
                                    var $row = $('<a>')
                                        .addClass('nav-search-result-item circle-select')
                                        .attr('href', config['circle'].link_base + circleResult[0].results[i].id.split('_').pop());

                                    // image
                                    var $img = $('<img>').attr('src', circleResult[0].results[i].image);
                                    $row.append($img);

                                    // text
                                    var $text = $('<span>').text(circleResult[0].results[i].text);
                                    $row.append($text);
                                    $row.appendTo($NavSearchResults);
                                }
                            }

                            if($NavSearchResults.length && !$('#notFoundElement').length){
                                var $endLabel = $('<div>')
                                    .text(cake.word.end_search)
                                    .addClass('nav-search-result-end-label');
                                $NavSearchResults.append($endLabel);
                            }

                            // ポップアップ下の画面をスクロールさせないようにする
                            $("body").addClass('nav-search-results-open');

                            // ポップアップクローズ用
                            $NavSearchResults.one('click', function () {
                                $NavSearchResults.hide();
                                $("body").removeClass('nav-search-results-open');
                            });
                            $(".nav-search-result-label").off("click").on("click", function(e) {
                                e.preventDefault();
                                return false;
                            });
                            $NavSearchResults.show();
                        });
                    // }, 150);
                });

            // 矢印キーで選択可能にする
            $NavSearchResults
                .on('keydown', '.nav-search-result-item', function (e) {
                    var $selectedItem = $NavSearchResults.find('.nav-search-result-item:focus');
                    if ($selectedItem.size()) {
                        switch (e.keyCode) {
                            // up
                            case 38:
                                e.preventDefault();
                                $selectedItem.prev().focus();
                                break;

                            // down
                            case 40:
                                e.preventDefault();
                                $selectedItem.next().focus();
                                break;
                        }
                    }
                });
        }
    };

    return {
        headerSearch: headerSearch
    };
});
