
/**
 * Created by pavel on 4.6.18.
 * copy from coffee generated
 */

$.nette.ext('toggle_detail', {
    init: function () {

        $("body").on("click", ".dd-item-backgroundWrap, .item-detail-content button.close", function(e){

            // console.log("Detail close");

            root = $('.dd-item.open-detail');
            head_detail = root.find('.box-list');
            row_detail = root.find('.item-detail-content');

//            row_detail.toggleClass('toggled');

//            row_detail.slideToggle('fast');
//            head_detail.slideToggle('fast');

            // $(this).fadeOut("fast");
            root.removeClass("open-detail").removeClass("opening-detail");
            $('#wrapper').removeClass('modal-detail-open').removeClass('modal-detail-open');
        });


        $(".js-settingsClose, .dd-item-backgroundWrap").click(function(){
        });

    },

    before: function(xhr, settings) {

        var id, row_detail, root;
        if (settings.nette && settings.nette.el.attr('data-toggle-item-detail')) {
            var el = settings.nette.el;

            // console.log("before");

            /*
             * hide all previous opened details
             */
            $('.dd-item.open-detail').each(function (index) {

                var head_detail = $(this).find('.box-list');
                var row_detail = $(this).find('.item-detail-content');

                // row_detail.slideUp('fast').toggleClass('toggled');
                // head_detail.slideDown('fast');
                // $(this).find('.dd-item-backgroundWrap').fadeOut("fast");

                // self.fadeOut("fast");
                $(this).removeClass("open-detail").removeClass('opening-detail');
                return;

                // $(this).find('.row-item-detail').slideUp();
                // $(this).find('.activ-controler').fadeIn();

                // $(this).find('.dd-handle').fadeIn();
                // $(this).find('.js-preview').slideDown();
                // $(this).find('.dd-item-backgroundWrap').fadeOut("fast");
                $(this).removeClass('open-detail').removeClass('opening-detail');
            });

            id = el.attr('data-toggle-item-detail');
            root = el.closest('.dd-item');
            row_detail = $(root).find('.item-detail-' + id);

            // $('#wrapper').removeClass('modal-detail-open');

            // console.log(row_detail);
            // console.log(el);
            // console.log(root);




            // console.log(el.next('.js-preview'));


            // $(el).next('.js-preview').slideToggle();
            // $(el).closest(".dd-item").find(".activ-controler").fadeToggle();
            // $(el).closest(".dd-item").find(".dd-handle").fadeToggle();


            // $(this).parent().find(".js-settings").slideToggle();

            // $(this).closest(".dd-item").toggleClass("active");
            // $(this).closest(".dd-item").find(".activ-controler").fadeToggle();
            // $(this).closest(".dd-item").find(".dd-handle").fadeToggle();
            // $(this).closest(".dd-item").find(".dd-item-backgroundWrap").fadeToggle("fast");





            // row_detail.show();


            if (row_detail.hasClass('_loaded')) {
                console.log("Je loaded");



                if (!row_detail.find('.item-detail-content').length) {
                    row_detail.closest('.dd-item').removeClass('open-detail');
                    row_detail.removeClass('toggled');
                    return true;
                }
                if (row_detail.hasClass('toggled')) {
                    row_detail.find('.item-detail-content').slideToggle('fast', (function(_this) {
                        return function() {
                            row_detail.closest('.dd-item').toggleClass('open-detail');
                            return row_detail.toggleClass('toggled');
                        };
                    })(this));
                } else {
                    row_detail.toggleClass('toggled');
                    row_detail.closest('.dd-item').toggleClass('open-detail');
                    row_detail.find('.item-detail-content').slideToggle('fast');
                }
                return false;
            } else {
                // console.log("Není load class");

                // console.log(row_detail.closest('.dd-item'));

                $('#wrapper').addClass('modal-detail-opening');

                root.addClass('opening-detail');
                return row_detail.addClass('loading');
            }

        }
    },
    success: function(payload, status, xhr, settings) {
        // console.log(settings);

        if (payload._toggle_detail) {

            console.log("success detail");

            id = payload._toggle_detail;
            row_detail = $('.item-detail-' + id);
            root = row_detail.closest('.dd-item');
            head_detail = root.find('.box-list');


            $('#wrapper').addClass('modal-detail-open').removeClass('modal-detail-opening');
            root.addClass("open-detail").removeClass('opening-detail');
            row_detail.removeClass('loading').addClass('loaded').toggleClass('toggled');

            $('html, body').animate({
                scrollTop: $(root).offset().top-50
            }, 800);

        }
        if (payload._filter_toggle) {
            $('.dd-item.open-detail').removeClass("open-detail");
            $('#wrapper').removeClass('modal-detail-open');
        }
    }
});



/**
 * scroll to item id
 */
$.nette.ext('scroll', {
    success: function(payload, status, xhr, settings) {
        if (payload._scroll_id) {

            console.log(payload._scroll_id);
            var target = $('.dd-item[data-id=' + payload._scroll_id + ']');

            if (target.length == 1) {
                $('html, body').animate({
                    scrollTop: $(target).offset().top-50
                }, 800);
            }
        }
    }
});


/**
 * template form send by ajax
 */
$.nette.ext('template', {
    success: function(payload, status, xhr, settings) {
        if (settings.nette && (settings.nette.el).is('input[name="sendTemplate"]')) {

            var option = '<option selected="" value="' + payload.lastTemplateId + '">' + payload.lastTemplateName + '</option>';
            var form = $('.dd-item.open-detail .item-detail-content form');

            if ($(form.is(':visible'))) {
                $(form.find('select[name="template"] option:selected')).removeAttr("selected");
                $(form.find('select[name="template"]')).append(option);

                /*
                 * event addNewTemplate to campaign
                 */
                $('body').trigger('addNewTemplate', {tid: payload.lastTemplateId, cid: $(form).data('id')});
            }

            form = $('form#frm-campaignForm');
            if ($(form.is(':visible'))) {
                $(form.find('select[name="template"] option:selected')).removeAttr("selected");
                $(form.find('select[name="template"]')).append(option);

            }

            if ($('.box-newTemplate__inner').is(':visible')) {
                $(".js-newTemplate").slideToggle("fast");
            }
        }
    }
});

/**
 * CampaignForm send by ajax
 */
$.nette.ext('campaignForm', {
    before: function(xhr, settings) {
        if (settings.nette && settings.nette.isSubmit && settings.nette.form.data('name') === 'campaignDetailForm') {

            var progress = $(settings.nette.el.closest('form').find('.progress-group'));
            $(progress).addClass('in');
        }
    },
    success: function(payload, status, xhr, settings) {
        if (settings.nette && settings.nette.isSubmit && settings.nette.form.data('name') === 'campaignDetailForm') {

            var progress = $(settings.nette.el.closest('form').find('.progress-group'));
            $(progress).removeClass('in');
            if (payload._success && payload._success === true) {
                $('.dd-item.open-detail').removeClass('open-detail');
                $('#wrapper').removeClass('modal-detail-open');

            }
        }
    }
});

/**
 * deviceGroup modal form hide send by ajax
 */
$.nette.ext('deviceGroup', {
    success: function(payload, status, xhr, settings) {
        if (settings.nette && settings.nette.isSubmit && settings.nette.form.data('name') == 'deviceGroupForm') {
            var option = '<option value="' + payload.lastDeviceGroupId + '">' + payload.lastDeviceGroupName + '</option>';
            var optionSelected = '<option selected="" value="' + payload.lastDeviceGroupId + '">' + payload.lastDeviceGroupName + '</option>';
            var form = $('.dd-item.open-detail form');

            $(form.find('select[name="deviceGroup"] option:selected')).removeAttr("selected");
            $(form.find('select[name="deviceGroup"]')).append(optionSelected);

            form = $('form#frm-deviceForm');
            $(form.find('select[name="deviceGroup"] option:selected')).removeAttr("selected");
            $(form.find('select[name="deviceGroup"]')).append(option);

            $(settings.nette.el.closest('.modal')).modal('hide');
        }
    }
});

/**
 * deviceGroup modal form hide send by ajax
 */
$.nette.ext('newUser', {
    success: function(payload, status, xhr, settings) {
        if (settings.nette && settings.nette.isSubmit && settings.nette.form.data('name') == 'userForm') {
            // $(settings.nette.el.closest('.modal')).modal('hide');
            $('.modal.in').modal('hide');
        }
    }
});


/**
 * deviceDetailForm ajax success submit
 */
$.nette.ext('deviceForm', {
    success: function(payload, status, xhr, settings) {
        if (settings.nette && settings.nette.isSubmit && settings.nette.form.data('name') == 'deviceForm') {

            console.log("deviceDetailForm success");

            if (payload._success && payload._success == true) {
                $('.modal.addDeviceModal').modal('hide')
            }
        }
    }
});

$.nette.ext('deviceGroupDetailForm', {
    success: function(payload, status, xhr, settings) {
        if (settings.nette && settings.nette.isSubmit && settings.nette.form.data('name') == 'deviceGroupsDetailForm') {
            if (payload._success && payload._success == true) {
                $('.dd-item.open-detail').removeClass('open-detail');
                $('#wrapper').removeClass('modal-detail-open');
            }
        }
    }
});

/**
 * switchery form send by ajax
 */
$.nette.ext('switchery', {
    init: function() {
        //switchery
        // var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
        $('.js-switch').each(function() {
            new Switchery($(this)[0], $(this).data());
        });
    },
    complete: function(payload, status, settings) {

        if (settings.nette && settings.nette.el.is("[data-dismiss='filter']") || payload._switchery_redraw == true) {

            //switchery
            var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
            $('.js-switch').each(function() {
                new Switchery($(this)[0], $(this).data());
            });
        }
    }
});


// $.nette.ext('dsds', {
//     init: function () {
//         this.ext('snippets').after(function ($snippet) {
//             $(mySuperElementSelector, $snippet).fadeIn();
//         });
//     }
// });

$.nette.ext('dsds', {
    init: function () {

        var selectorAnimationEnd = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
        // var animation = 'flipInX';

        var animationIn = 'fadeIn';
        var animationOut = 'fadeOut';

        // var animationIn = 'flipInX';
        // var animationOut = 'flipOutX';

        // var animationIn = 'flipInY';
        // var animationOut = 'flipOutY';

        // var animationIn = 'rotateIn';
        // var animationOut = 'rotateOut';

        // var animationIn = 'zoomIn';
        // var animationOut = 'zoomOut';

        // var animationIn = 'fadeInDown';
        // var animationOut = 'fadeOut';

        // var animationIn = 'bounceIn';
        // var animationOut = 'fadeOut';

        this.ext('snippets').before(function (el) {
            var animateBefore = $(el).data('animate-before');
            if (animateBefore) {
                el.addClass('animated ' + animateBefore).one(selectorAnimationEnd, function () {
                    $(this).removeClass('animated ' + animateBefore);
                });

                setTimeout(
                    function () {

                    }, 50);
            }

        });
        this.ext('snippets').after(function (el) {

            var animateBefore = $(el).data('animate-before');
            var animateAfter = $(el).data('animate-after');
            if (animateAfter) {
                el.removeClass('animated ' + animateBefore).addClass('animated ' + animateAfter).one(selectorAnimationEnd, function () {
                    $(this).removeClass('animated ' + animateAfter);
                });
            }

        });
    }
});

/*

$.nette.ext('snippets').applySnippet = function ($el, html, back) {
    var selectorAnimationEnd = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
    // var animation = 'flipInX';
    var animation = 'fadeIn';

    setTimeout(
        function () {
            $el.addClass('animated ' + animation).one(selectorAnimationEnd, function () {
                $(this).removeClass('animated ' + animation);
            });
        }, 50);



    if (!back && $el.is('[data-ajax-append]')) {
        $el.append(html);
    } else if (!back && $el.is('[data-ajax-prepend]')) {
        $el.prepend(html);
    } else if ($el.html() != html || /<[^>]*script/.test(html)) {
        $el.html(html);
    }
};

*/

$.nette.ext('modalForm', {
    success: function(payload, status, xhr, settings) {

        // if (settings.nette && settings.nette.isSubmit && settings.nette.form.data('name') == 'userForm') {
        if (settings.nette && settings.nette.el.is("[data-dismiss='modal']")) {
            // $(settings.nette.el.closest('.modal')).modal('hide');
            $('.modal.in').modal('hide');
        }
    }
});



$.nette.ext('bs-modal', {
    init: function () {
        var self = this;

        this.ext('snippets', true).after($.proxy(function ($el) {
            if (!$el.is('.modal')) {
                return;
            }

            self.open($el);
        }, this));

        $('.modal[id^="snippet-"]').each(function () {
            self.open($(this));
        });
    }
}, {
    open: function (el) {
        var content = el.find('.modal-content');
        if (!content.length) {
            return; // ignore empty modal
        }

        el.modal({});
    }
});


/**
 * init slider on bootstrap shown
 *
 * data-custom-input-min="inputName"
 * data-custom-input-max="inputName"
 *
 */
$(".modal").on('shown.bs.modal', function(){

    customInputSet = function(el) {
        var values = $(el).val().split(",");
        var inputMin = $(el).data('custom-input-min');
        var inputMax = $(el).data('custom-input-max');

        if (inputMin) {
            $('input[name="' + inputMin + '"]').val(values[0]);
        }
        if (inputMax) {
            $('input[name="' + inputMax + '"]').val(values[1]);
        }
    };

    $("[data-provide='slider-range']").on('slide', function (e) {
        customInputSet(this);

    }).slider({
        // tooltip: 'always',
        _formatter: function(value) {
            var days = ['pondělí', 'úterý', 'středa', ];




            console.log($(this));
            // console.log($(this).data('custom-titles'));

            return value;

            return 'Current value: ' + value;
        }
    }).each(function () {
        customInputSet(this);
    });

});




$.nette.ext('bs-slider', {
    init: function () {
        var self = this;


    }
}, {
    open: function (el) {
        var content = el.find('.modal-content');
        if (!content.length) {
            return; // ignore empty modal
        }

        el.modal({});
    }
});


/**
 * media carousel form send by ajax
 */
$.nette.ext('daterange-time-picker', {
    init: function () {

        if ( typeof Nette === 'undefined') {
            return;
        }

        /**
         * @return {boolean}
         */
        Nette.validators.CmsModuleFormsControlsBootstrapRangeDatePicker_validateDateRange = function (elem, arg, value) {
            var arr = value.split('-');
            var rawStart, rawEnd, minStart, maxEnd, start, end, result = false;
            if (arr.length == 2 && arg.length == 2) {
                rawStart = arr[0].replace(/ /gi, "");
                rawEnd = arr[1].replace(/ /gi, "");
                minStart = moment(arg[0]['date']);
                maxEnd = moment(arg[1]['date']);

                start = moment(rawStart, "D.M.YYYY");
                end = moment(rawEnd, "D.M.YYYY");

                result = start.isBetween(minStart, maxEnd, 'days', '[]') && end.isBetween(minStart, maxEnd, 'days', '[]');
            }

            return result;
        };

        /**
         * @return {boolean}
         */
        Nette.validators.CmsModuleFormsControlsBootstrapRangeDatePicker_validateDateTimeRange = function (elem, arg, value) {
            var arr = value.split('-');
            var rawStart, rawEnd, minStart, maxEnd, start, end, result = false;
            if (arr.length == 2 && arg.length == 2) {
                rawStart = arr[0].replace(/ /gi, "");
                rawEnd = arr[1].replace(/ /gi, "");
                minStart = moment(arg[0]['date']);
                maxEnd = moment(arg[1]['date']);

                start = moment(rawStart, "D.M.YYYY HH:mm");
                end = moment(rawEnd, "D.M.YYYY HH:mm");

                result = start.isBetween(minStart, maxEnd, 'days', '[]') && end.isBetween(minStart, maxEnd, 'days', '[]');
            }

            return result;
        };

        /**
         * @return {boolean}
         */
        Nette.validators.CmsModuleFormsControlsBootstrapDatePicker_validateDateRange = function (elem, arg, value) {

            var rawValue, minDate, maxDate, compare;

            rawValue = value;
            minDate = moment(arg[0]['date']);
            maxDate = moment(arg[1]['date']);

            compare = moment(rawValue, "D.M.YYYY");

            return compare.isBetween(minDate, maxDate, null, '[]');
        };

    },


    load: function() {

        var config = {
            selectorDate: '.input-date-timepicker',
            selectorDateRange: '.input-daterange-timepicker',
            dateTimeOptions: {
                "locale": {
                    "format": "D. M. YYYY",
                    "separator": " - ",
                    "applyLabel": "Použít",
                    "cancelLabel": "Zrušit",
                    "fromLabel": "Od",
                    "toLabel": "Do",
                    "customRangeLabel": "Vyberte",
                    "daysOfWeek": [
                        "Ne",
                        "Po",
                        "Út",
                        "St",
                        "Čt",
                        "Pá",
                        "So"
                    ],
                    "monthNames": [
                        "Leden",
                        "Únor",
                        "Březen",
                        "Duben",
                        "Květen",
                        "Červen",
                        "Červenec",
                        "Srpen",
                        "Září",
                        "Říjen",
                        "Listopad",
                        "Prosinec"
                    ],
                    "firstDay": 1
                },
                //open: "left",
                //parentEl: ".default-daterange",
                "singleDatePicker": true,
                "autoApply": true,
                "autoUpdateInput": true,
                "showDropdowns": true,
                "linkedCalendars": false
            },
            rangeOptions: {
                "locale": {
                    "format": "D. M. YYYY",
                    "separator": " - ",
                    "applyLabel": "Použít",
                    "cancelLabel": "Zrušit",
                    "fromLabel": "Od",
                    "toLabel": "Do",
                    "customRangeLabel": "Vyberte",
                    "daysOfWeek": [
                        "Ne",
                        "Po",
                        "Út",
                        "St",
                        "Čt",
                        "Pá",
                        "So"
                    ],
                    "monthNames": [
                        "Leden",
                        "Únor",
                        "Březen",
                        "Duben",
                        "Květen",
                        "Červen",
                        "Červenec",
                        "Srpen",
                        "Září",
                        "Říjen",
                        "Listopad",
                        "Prosinec"
                    ],
                    "firstDay": 1
                },
                //open: "left",
                //parentEl: ".default-daterange",
                "autoApply": true,
                "autoUpdateInput": true,
                "showDropdowns": true,
                "linkedCalendars": false
            }

        };

        $(config.selectorDate).each(function (index) {
            var element = this;
            var options = config.dateTimeOptions;

            var attributes = {
                'startDate': 'data-date-start',
                'endDate': 'data-date-end',
                'minDate': 'data-date-min',
                'maxDate': 'data-date-max',
                'timePicker': 'data-time-picker',
                'timePicker24Hour': 'data-time-picker-24',
                'timePickerIncrement': 'data-time-picker-increment'
            };

            $.each(attributes, function (index, option) {
                if ($(element).attr(option)) {
                    var val = $(element).attr(option);
                    if ($.isNumeric(val)) {
                        options[index] = parseInt(val, 10);

                    } else {
                        options[index] = $(element).attr(option) == "true" ? true : $(element).attr(option);
                    }
                }
            });

            if ('timePicker' in options) {
                options['locale']['format'] = 'D. M. YYYY H:mm';
            }


            // console.log(options);

            $(this).daterangepicker(options);

        });

        $(config.selectorDateRange).each(function (index) {
            var element = this;
            var options = config.rangeOptions;

            var attributes = {
                'startDate': 'data-date-start',
                'endDate': 'data-date-end',
                'minDate': 'data-date-min',
                'maxDate': 'data-date-max',
                'timePicker': 'data-time-picker',
                'timePicker24Hour': 'data-time-picker-24',
                'timePickerIncrement': 'data-time-picker-increment'
            };

            $.each(attributes, function (index, option) {
                if ($(element).attr(option)) {
                    var val = $(element).attr(option);
                    if ($.isNumeric(val)) {
                        options[index] = parseInt(val, 10);

                    } else {
                        options[index] = $(element).attr(option) == "true" ? true : $(element).attr(option);
                    }
                }
            });

            if ('timePicker' in options) {
                options['locale']['format'] = 'D. M. YYYY H:mm';
            }


            // console.log(options);

            $(this).daterangepicker(options);

        });


    }

});




Nette.toggle = function (id, visible) {
    var el = $('#' + id);
    if (visible) {
        el.slideDown();
    } else {
        el.slideUp();
    }
};

Nette.validators.mimeType = function(elem, args, val) {

    if (val.length > 0) {
        var mimeType = MimeType.lookup(val[0].name);
        return ($.inArray(mimeType, args)) > -1;
    }

    return false;
};


$(function(){

    /**
     * color (tag) changed
     */
    $(document).on("click", "input.tagColor:checked", function(e) {

        /**
         *
         *
         */
        var lastValueEl = $(this).closest('.box-list__settings__one__colors');
        var lastValue = $(lastValueEl).data('last-value');
        var value = $(this).val();

        if (lastValue == value) {
            $( this ).prop( "checked", false );
            $(lastValueEl).data('last-value', 0);

        } else {
            $(lastValueEl).data('last-value', value);
        }



        /**
         * on change autoSave form elements [auto send form]
         */
    }).on('change', 'form.auto-save input:not(.not-auto-save), form.auto-save select:not(.not-auto-save)', function (e) {

        /**
         * auto save form
         */
        // var form = $(this).get(0);
        var form = $(this).closest('form').get(0);

        if (Nette.validateForm(form)) {
            $(form).submit();
        }



        /**
         * message error from media multi upload to another element
         */
    }).on('DOMSubtreeModified', "#frm-mediaForm-files_message", function() {
        $('#mediaErrorMessage').text($(this).text());




        /**
         * click to target
         */
    }).on('click', "[data-click]", function(e) {
        e.preventDefault();
        $($(this).data('click')).click();




        /**
         * send ajax before modal open
         */
    }).on("click", "[data-toggle='ajax-modal']", function(e){
        e.preventDefault();

        var target = $(this).data('target');
        var targetEl;

        if (!target) {
            console.warn('`data-target` not found');

        } else {
            if ($(target).length == 0) {
                console.warn('modal target `' + target + '` not found');

            } else {
                targetEl = $(target);
            }
        }

        var backdrop = $(this).data('backdrop');
        var title = $(this).data('title');
        if (title) {
            var titleTarget = $(this).find('.modal-title');
            if (titleTarget && targetEl) {
                targetEl.find('.modal-title').text(title);
            }
        }

        $.nette.ajax({
            type: "POST",
            url: this.href,
            success: function (payload) {

                // snippets
                if (payload.snippets) {
                    $.nette.ext('snippets').updateSnippets(payload.snippets);
                    if (backdrop) {
                        targetEl.modal({
                            show: true
                            // backdrop: 'static'
                        });

                    } else {
                        targetEl.modal('show');
                    }

                }

            },
            fail: function (payload) {
                console.log(payload);
            }
        });



        /**
         * zařízení select / unselect ve skupinách zařízení
         */
    }).on("change", "form input.happy", function(e){

        var ajaxSelectEl = $(this).closest('[data-ajax-select]');

        if (ajaxSelectEl.length > 0) {

            var ajaxSelect = $(ajaxSelectEl).data('ajax-select');
            var did = $(ajaxSelectEl).data('device-id');
            var item = $(this).closest('.datagrid-tree-item');
            var id = $(item).data('id');
            var checked = $(this).is(":checked");

            console.log(item);
            console.log(did);


            /*
             * selectujeme všechny potomky
             */
            if ($(item).is('.has-children')) {
                var childrenCheckboxes = $(item).find('.happy-checkbox');
                var childrenInputs = $(item).find('input[data-check]');

                if (checked) {
                    $(childrenCheckboxes).addClass('active');
                    $(childrenInputs).prop( "checked", true );

                } else {
                    $(childrenCheckboxes).removeClass('active');
                    $(childrenInputs).prop( "checked", false );
                }
            }
            /********************************************************/


            /*
             * unselectujeme všechny rodiče
             */
            if (checked == false) {
                var parents = $(item).parents('.datagrid-tree-item');

                $( parents ).each(function( ) {
                    $(this).find('.happy-checkbox').first().removeClass('active');
                    $(this).find('input[data-check]').first().prop( "checked", false );
                });
            }
            /********************************************************/


            /*
             * odešleme current selectovaný prvek na server, ten sám osnačí potomky a rodiče
             */
            $.nette.ajax({
                off: ['datagrid.happy'],
                data: { 'did': did, 'gid': id, 'checked': checked },
                url: ajaxSelect,
                success: function (payload) {

                    // snippets
                    if (payload.snippets) {
                        // $.nette.ext('snippets').updateSnippets(payload.snippets);

                        // targetEl.modal('show');
                    }

                },
                fail: function (payload) {
                    console.log(payload);
                }
            });
            /********************************************************/





            console.log(id);
            console.log(checked);
        }

    });








});



