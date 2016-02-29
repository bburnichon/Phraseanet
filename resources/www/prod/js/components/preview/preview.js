// var p4 = p4 || {};

var recordPreviewModule = (function (p4) {
    var prevAjax, prevAjaxrunning;
    prevAjaxrunning = false;
    p4.slideShow = false;

    $(document).ready(function () {
        $('#PREVIEWIMGDESC').tabs();
    });

    /**
     *
     * @param env
     * @param pos - relative position in current page
     * @param contId
     * @param reload
     */
    function openPreview(env, pos, contId, reload) {

        if (contId == undefined)
            contId = '';
        var roll = 0;
        var justOpen = false;

        if (!p4.preview.open) {
            commonModule.showOverlay();

            $('#PREVIEWIMGCONT').disableSelection();

            justOpen = true;

            if (!( navigator.userAgent.match(/msie/i))) {
                $('#PREVIEWBOX').css({
                    'display': 'block',
                    'opacity': 0
                }).fadeTo(500, 1);
            } else {
                $('#PREVIEWBOX').css({
                    'display': 'block',
                    'opacity': 1
                });
            }
            p4.preview.open = true;
            p4.preview.nCurrent = 5;
            $('#PREVIEWCURRENT, #PREVIEWOTHERSINNER, #SPANTITLE').empty();
            resizePreview();
            if (env == 'BASK')
                roll = 1;

        }

        if (reload === true)
            roll = 1;


        $('#tooltip').css({
            'display': 'none'
        });

        $('#PREVIEWIMGCONT').empty();

        var options_serial = p4.tot_options;
        var query = p4.tot_query;
        var navigation = p4.navigation;

        // keep relative position for answer train:
        var relativePos = pos;
        // update real absolute position with pagination:
        var absolutePos = parseInt(navigation.perPage,10) * (parseInt(navigation.page, 10) - 1) + parseInt(pos,10);

        // if comes from story, work with relative positionning
        if (env == 'REG') {
            // @TODO - if event comes from workzone (basket|story),
            // we can use the relative position in order to display the doubleclicked records
            // except we can't know the original event in this implementation
            absolutePos = 0;
        }

        prevAjax = $.ajax({
            type: "POST",
            url: "../prod/records/",
            dataType: 'json',
            data: {
                env: env,
                pos: absolutePos,
                cont: contId,
                roll: roll,
                options_serial: options_serial,
                query: query
            },
            beforeSend: function () {
                if (prevAjaxrunning)
                    prevAjax.abort();
                if (env == 'RESULT')
                    $('#current_result_n').empty().append(parseInt(pos) + 1);
                prevAjaxrunning = true;
                $('#PREVIEWIMGDESC, #PREVIEWOTHERS').addClass('loading');
            },
            error: function (data) {
                prevAjaxrunning = false;
                $('#PREVIEWIMGDESC, #PREVIEWOTHERS').removeClass('loading');
                posAsk = null;
            },
            timeout: function () {
                prevAjaxrunning = false;
                $('#PREVIEWIMGDESC, #PREVIEWOTHERS').removeClass('loading');
                posAsk = null;
            },
            success: function (data) {
                _cancelPreview();
                prevAjaxrunning = false;
                posAsk = null;

                if (data.error) {
                    $('#PREVIEWIMGDESC, #PREVIEWOTHERS').removeClass('loading');
                    alert(data.error);
                    if (justOpen)
                        closePreview();
                    return;
                }
                posAsk = data.pos - 1;

                $('#PREVIEWIMGCONT').empty().append(data.html_preview);
                $('#PREVIEWIMGCONT .thumb_wrapper')
                    .width('100%').height('100%').image_enhance({zoomable: true});

                $('#PREVIEWIMGDESCINNER').empty().append(data.desc);
                $('#HISTORICOPS').empty().append(data.history);
                $('#popularity').empty().append(data.popularity);

                if ($('#popularity .bitly_link').length > 0) {

                    BitlyCB.statsResponse = function (data) {
                        var result = data.results;
                        if ($('#popularity .bitly_link_' + result.userHash).length > 0) {
                            $('#popularity .bitly_link_' + result.userHash).append(' (' + result.clicks + ' clicks)');
                        }
                    };
                    BitlyClient.stats($('#popularity .bitly_link').html(), 'BitlyCB.statsResponse');
                }

                p4.preview.current = {};
                p4.preview.current.width = parseInt($('#PREVIEWIMGCONT input[name=width]').val());
                p4.preview.current.height = parseInt($('#PREVIEWIMGCONT input[name=height]').val());
                p4.preview.current.tot = data.tot;
                p4.preview.current.pos = relativePos;

                if ($('#PREVIEWBOX img.record.zoomable').length > 0) {
                    $('#PREVIEWBOX img.record.zoomable').draggable();
                }

                $('#SPANTITLE').empty().append(data.title);
                $("#PREVIEWTITLE_COLLLOGO").empty().append(data.collection_logo);
                $("#PREVIEWTITLE_COLLNAME").empty().append(data.collection_name);

                _setPreview();

                if (env != 'RESULT') {
                    if (justOpen || reload) {
                        _setCurrent(data.current);
                    }
                    _viewCurrent($('#PREVIEWCURRENT li.selected'));
                }
                else {
                    if (!justOpen) {
                        $('#PREVIEWCURRENT li.selected').removeClass('selected');
                        $('#PREVIEWCURRENTCONT li.current' + absolutePos).addClass('selected');
                    }
                    if (justOpen || ($('#PREVIEWCURRENTCONT li.current' + absolutePos).length === 0) || ($('#PREVIEWCURRENTCONT li:last')[0] == $('#PREVIEWCURRENTCONT li.selected')[0]) || ($('#PREVIEWCURRENTCONT li:first')[0] == $('#PREVIEWCURRENTCONT li.selected')[0])) {
                        _getAnswerTrain(pos, data.tools, query, options_serial);
                    }

                    _viewCurrent($('#PREVIEWCURRENT li.selected'));
                }
                if (env == 'REG' && $('#PREVIEWCURRENT').html() === '') {
                    _getRegTrain(contId, pos, data.tools);
                }
                _setOthers(data.others);
                _setTools(data.tools);
                $('#tooltip').css({
                    'display': 'none'
                });
                $('#PREVIEWIMGDESC, #PREVIEWOTHERS').removeClass('loading');
                if (!justOpen || (p4.preview.mode != env))
                    resizePreview();

                p4.preview.mode = env;
                $('#EDIT_query').focus();

                $('#PREVIEWOTHERSINNER .otherBaskToolTip').tooltip();

                return;
            }

        });

    }

    function closePreview() {
        p4.preview.open = false;
        commonModule.hideOverlay();

        $('#PREVIEWBOX').fadeTo(500, 0);
        $('#PREVIEWBOX').queue(function () {
            $(this).css({
                'display': 'none'
            });
            _cancelPreview();
            $(this).dequeue();
        });

    }

    function startSlide() {
        if (!p4.slideShow) {
            p4.slideShow = true;
        }
        if (p4.slideShowCancel) {
            p4.slideShowCancel = false;
            p4.slideShow = false;
            $('#start_slide').show();
            $('#stop_slide').hide();
        }
        if (!p4.preview.open) {
            p4.slideShowCancel = false;
            p4.slideShow = false;
            $('#start_slide').show();
            $('#stop_slide').hide();
        }
        if (p4.slideShow) {
            $('#start_slide').hide();
            $('#stop_slide').show();
            getNext();
            setTimeout("startSlide()", 3000);
        }
    }

    function stopSlide() {
        p4.slideShowCancel = true;
        $('#start_slide').show();
        $('#stop_slide').hide();
    }

    function getNext() {
        if (p4.preview.mode == 'REG' && parseInt(p4.preview.current.pos) === 0)
            $('#PREVIEWCURRENTCONT li img:first').trigger("click");
        else {
            if (p4.preview.mode == 'RESULT') {
                posAsk = parseInt(p4.preview.current.pos) + 1;
                posAsk = (posAsk >= parseInt(p4.tot) || isNaN(posAsk)) ? 0 : posAsk;
                recordPreviewModule.openPreview('RESULT', posAsk, '', false);
            }
            else {
                if (!$('#PREVIEWCURRENT li.selected').is(':last-child'))
                    $('#PREVIEWCURRENT li.selected').next().children('img').trigger("click");
                else
                    $('#PREVIEWCURRENT li:first-child').children('img').trigger("click");
            }

        }
    }

    function getPrevious() {
        if (p4.preview.mode == 'RESULT') {
            posAsk = parseInt(p4.preview.current.pos) - 1;
            posAsk = (posAsk < 0) ? ((parseInt(p4.tot) - 1)) : posAsk;
            recordPreviewModule.openPreview('RESULT', posAsk, '', false);
        }
        else {
            if (!$('#PREVIEWCURRENT li.selected').is(':first-child'))
                $('#PREVIEWCURRENT li.selected').prev().children('img').trigger("click");
            else
                $('#PREVIEWCURRENT li:last-child').children('img').trigger("click");
        }
    }

    function _setPreview() {
        if (!p4.preview.current)
            return;

        var zoomable = $('img.record.zoomable');
        if (zoomable.length > 0 && zoomable.hasClass('zoomed'))
            return;

        var h = parseInt(p4.preview.current.height);
        var w = parseInt(p4.preview.current.width);
//	if(p4.preview.current.type == 'video')
//	{
//		var h = parseInt(p4.preview.current.flashcontent.height);
//		var w = parseInt(p4.preview.current.flashcontent.width);
//	}
        var t = 20;
        var de = 0;

        var margX = 0;
        var margY = 0;

        if ($('#PREVIEWIMGCONT .record_audio').length > 0) {
            margY = 100;
            de = 60;
        }


//	if(p4.preview.current.type != 'flash')
//	{
        var ratioP = w / h;
        var ratioD = parseInt(p4.preview.width) / parseInt(p4.preview.height);

        if (ratioD > ratioP) {
            //je regle la hauteur d'abord
            if ((parseInt(h) + margY) > parseInt(p4.preview.height)) {
                h = Math.round(parseInt(p4.preview.height) - margY);
                w = Math.round(h * ratioP);
            }
        }
        else {
            if ((parseInt(w) + margX) > parseInt(p4.preview.width)) {
                w = Math.round(parseInt(p4.preview.width) - margX);
                h = Math.round(w / ratioP);
            }
        }
//	}
//	else
//	{

//		h = Math.round(parseInt(p4.preview.height) - margY);
//		w = Math.round(parseInt(p4.preview.width) - margX);
//	}
        t = Math.round((parseInt(p4.preview.height) - h - de) / 2);
        var l = Math.round((parseInt(p4.preview.width) - w) / 2);
        $('#PREVIEWIMGCONT .record').css({
            width: w,
            height: h,
            top: t,
            left: l
        }).attr('width', w).attr('height', h);
    }

    function _setCurrent(current) {
        if (current !== '') {
            var el = $('#PREVIEWCURRENT');
            el.removeClass('loading').empty().append(current);

            $('ul', el).width($('li', el).length * 80);
            $('img.prevRegToolTip', el).tooltip();
            $.each($('img.openPreview'), function (i, el) {
                var jsopt = $(el).attr('jsargs').split('|');
                $(el).removeAttr('jsargs');
                $(el).removeClass('openPreview');
                $(el).bind('click', function () {
                    _viewCurrent($(this).parent());
                    // convert abssolute to relative position
                    var absolutePos = jsopt[1];
                    var relativePos = parseInt(absolutePos, 10) - parseInt(p4.navigation.perPage, 10) * (parseInt(p4.navigation.page, 10) - 1);
                    // keep relative position for answer train:
                    recordPreviewModule.openPreview(jsopt[0], relativePos, jsopt[2],false);
                });
            });
        }
    }

    function _viewCurrent(el) {
        if (el.length === 0) {
            return;
        }
        $('#PREVIEWCURRENT li.selected').removeClass('selected');
        el.addClass('selected');
        $('#PREVIEWCURRENTCONT').animate({'scrollLeft': ($('#PREVIEWCURRENT li.selected').position().left + $('#PREVIEWCURRENT li.selected').width() / 2 - ($('#PREVIEWCURRENTCONT').width() / 2 ))});
        return;
    }

    function reloadPreview() {
        $('#PREVIEWCURRENT li.selected img').trigger("click");
    }

    function _getAnswerTrain(pos, tools, query, options_serial) {
        // keep relative position for answer train:
        var relativePos = pos;
        // update real absolute position with pagination:
        var absolutePos = parseInt(p4.navigation.perPage,10) * (parseInt(p4.navigation.page, 10) - 1) + parseInt(pos,10);

        $('#PREVIEWCURRENTCONT').fadeOut('fast');
        $.ajax({
            type: "POST",
            url: "/prod/query/answer-train/",
            dataType: 'json',
            data: {
                pos: absolutePos,
                options_serial: options_serial,
                query: query
            },
            success: function (data) {
                _setCurrent(data.current);
                _viewCurrent($('#PREVIEWCURRENT li.selected'));
                _setTools(tools);
                return;
            }
        });
    }

    function _getRegTrain(contId, pos, tools) {
        $.ajax({
            type: "POST",
            url: "/prod/query/reg-train/",
            dataType: 'json',
            data: {
                cont: contId,
                pos: pos
            },
            success: function (data) {
                _setCurrent(data.current);
                _viewCurrent($('#PREVIEWCURRENT li.selected'));
                if (typeof(tools) != 'undefined')
                    _setTools(tools);
                return;
            }
        });
    }

    function _cancelPreview() {
        $('#PREVIEWIMGDESCINNER').empty();
        $('#PREVIEWIMGCONT').empty();
        p4.preview.current = false;
    }

    function _setOthers(others) {

        $('#PREVIEWOTHERSINNER').empty();
        if (others !== '') {
            $('#PREVIEWOTHERSINNER').append(others);

            $('#PREVIEWOTHERS table.otherRegToolTip').tooltip();
        }
    }

    function _setTools(tools) {
        $('#PREVIEWTOOL').empty().append(tools);
        if (!p4.slideShowCancel && p4.slideShow) {
            $('#start_slide').hide();
            $('#stop_slide').show();
        } else {
            $('#start_slide').show();
            $('#stop_slide').hide();
        }
    }

    function resizePreview() {
        p4.preview.height = $('#PREVIEWIMGCONT').height();
        p4.preview.width = $('#PREVIEWIMGCONT').width();
        _setPreview();
    }
    return {
        openPreview: openPreview,
        closePreview: closePreview,
        startSlide: startSlide,
        stopSlide: stopSlide,
        getNext: getNext,
        getPrevious: getPrevious,
        reloadPreview: reloadPreview,
        resizePreview: resizePreview
    }
})(p4);
