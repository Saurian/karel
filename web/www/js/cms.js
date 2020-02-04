
/**
 * Created by pavel on 4.6.18.
 * copy from coffee generated
 */


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

            if (payload._success && payload._success === true) {
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

/**
 * happy checkboxes
 */
$.nette.ext('happy', {
    success: function() {
        if (window.happy) {
            window.happy.reset();
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

            if(typeof(payload.success) != "undefined" && payload.success !== null) {
                if (payload.success) {
                    $('.modal.in').modal('hide');
                }

            } else {
                $('.modal.in').modal('hide');
            }

        }
    }
});


$.nette.ext('unCollapse', {
    success: function(payload, status, xhr, settings) {

        // if (settings.nette && settings.nette.isSubmit && settings.nette.form.data('name') == 'userForm') {
        if (settings.nette && payload._un_collapse) {
            if ($(payload._un_collapse).is('.collapse.in')) {
                $(payload._un_collapse).collapse('hide');
            }
        }
    }
});


$.nette.ext('calendar', {

    /**
     * autoload calendar
     */
    load: function() {
        if ($(this.selector).length > 0) {

            if (!this.calendarIsLoaded()) {
                this.initCalendar();
            }
        }
    },

    /**
     * refresh calendar after ajax send calendar_refresh in payload
     *
     * @param payload
     * @param status
     * @param xhr
     * @param settings
     */
    success: function(payload, status, xhr, settings) {
        if ($(this.selector).length > 0 && this.calendarIsLoaded()) {
            if (settings.nette && settings.nette.isSubmit) {
                if (payload.calendar_refresh && payload.calendar_refresh === true) {
                    this.calendar.refetchEvents();
                }
                if (payload.modal_hide) {
                    $(payload.modal_hide).modal('hide');
                }
            }
        }
    }

}, {

    selector: '#calendar',
    calendar: null,
    eventsLink: null,
    newEventLink: null,
    moveEventLink: null,
    resizeEventLink: null,
    paramIdName: null,
    paramTimeName: null,

    calendarIsLoaded: function () {
        return $(this.selector).children().length > 0;
    },
    initCalendar: function () {
        var Calendar = FullCalendar.Calendar;
        var Draggable = FullCalendarInteraction.Draggable;

        /* initialize the external events
        -----------------------------------------------------------------*/
        var containerEl = document.getElementById('calendar-events');
        new Draggable(containerEl, {
            itemSelector: '.fc-event',
            eventData: function(eventEl) {
                return {
                    title: eventEl.innerText.trim()
                }
            }
        });


        var calendarEl = $('#calendar').get(0);
        var self = this;

        this.calendar = new FullCalendar.Calendar(calendarEl, {
            plugins: [ 'interaction', 'dayGrid', 'timeGrid', 'list' ],
            timeZone: 'local',
            height: 'parent',
            handleWindowResize: false,
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },

            /**
             * click to empty calendar item (new event)
             *
             * @param info
             */
            dateClick: function(info) {
                $('#frm-calendarControl-newEvent').find('[name="datetime"]').val(info.dateStr);
                $('#newEventModal').modal({ show: 'true' });
            },

            /**
             * click to existing calendar item (edit event)
             *
             * @param info
             */
            eventClick: function(info) {
                var form = $('#frm-calendarControl-editEvent');

                $(form).find('[name="id"]').val(info.event.id);
                $(form).find('[name="name"]').val(info.event.title);
                $(form).find('[name="from"]').val(info.event.start.toDateString() + " " + info.event.start.toLocaleTimeString());
                $(form).find('[name="to"]').val(info.event.end.toDateString() + " " + info.event.end.toLocaleTimeString());
                $(form).find('[name="devices"]').val(info.event.extendedProps.devices);
                $(form).find('[name="deviceGroups"]').val(info.event.extendedProps.deviceGroups);

                $('#editEventModal').modal({ show: 'true' });
            },

            /**
             * move event
             *
             * @param info
             */
            eventDrop: function (info) {
                var data = {};
                data[self.paramIdName] = info.event.id;
                data[self.paramTimeName] = info.event.start.toISOString();
                console.log(info.event);
                console.log(info.event.start.toISOString());
                // console.log(info.event.end.toISOString());

                $.nette.ajax({
                    type: "POST",
                    url: self.moveEventLink,
                    data: data,
                    success: function (payload) {
                        // snippets
                        if (payload.snippets) {
                            // $.nette.ext('snippets').updateSnippets(payload.snippets);
                        }
                    },
                    fail: function (payload) {
                        console.log(payload);
                    }
                });
            },


            /**
             * remove add event will be refresh from db
             *
             * @param info
             */
            eventReceive: function (info) {
                info.event.remove();
            },


            /**
             * add event
             *
             * @param info
             */
            drop: function(info) {

                var data = {};
                data[self.paramIdName] = $(info.draggedEl).data('id');
                data[self.paramTimeName] = info.dateStr;

                console.log(data);

                $.nette.ajax({
                    type: "POST",
                    url: self.newEventLink,
                    data: data,
                    success: function (payload) {

                        if (payload.calendar_refresh && payload.calendar_refresh === true) {
                            self.calendar.refetchEvents();
                        }

                        // snippets
                        if (payload.snippets) {
                            // $.nette.ext('snippets').updateSnippets(payload.snippets);
                        }
                    },
                    fail: function (payload) {
                        console.log(payload);
                    }
                });
            },

            /**
             * resize event
             *
             * @param info
             */
            eventResize: function(info) {
                var data = {};
                data[self.paramIdName] = info.event.id;
                data[self.paramTimeName] = info.event.end.toISOString();

                $.nette.ajax({
                    type: "POST",
                    url: self.resizeEventLink,
                    data: data,
                    success: function (payload) {

                        if (payload.calendar_refresh && payload.calendar_refresh === true) {
                            self.calendar.refetchEvents();
                        }

                        // snippets
                        if (payload.snippets) {
                            // $.nette.ext('snippets').updateSnippets(payload.snippets);
                        }
                    },
                    fail: function (payload) {
                        info.revert();
                        console.log(payload);
                    }
                });
            },


            defaultView: 'dayGridMonth',
            defaultDate: '2019-08-12',
            minTime: '07:00',
            maxTime: '22:00',
            slotDuration: '01:00',
            locale: 'cs',
            navLinks: true, // can click day/week names to navigate views
            editable: true,
            eventDurationEditable: true,
            droppable: true, // this allows things to be dropped onto the calendar
            allDaySlot: false,
            eventLimit: true, // allow "more" link when too many events
            events: {
                url: this.eventsLink,
                failure: function() {
                    // document.getElementById('script-warning').style.display = 'block'
                }
            },
            loading: function(bool) {
                // document.getElementById('loading').style.display = bool ? 'block' : 'none';
            }

        });

        this.calendar.render();
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
    },
    success: function(payload, status, xhr, settings) {
        if (settings.nette && payload.closeModal) {
            $('.modal.in').modal('hide');
        }
    }

}, {
    open: function (el) {
        var content = el.find('.modal-content');
        if (!content.length) {
            return; // ignore empty modal
        }

        el.modal({});
    },
});



$.nette.ext('bs-slider', {
    load: function () {
        var self = this;

        this.initModalSlider('.modal');
    }

    }, {

    /**
     * init slider on bootstrap shown
     * https://seiyria.com/bootstrap-slider/
     *
     * data-custom-input-min="inputName"
     * data-custom-input-max="inputName"
     *
     * @param selector
     */
    initModalSlider: function (selector) {

        $(selector).on('shown.bs.modal', function(){

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

            }).each(function () {
                customInputSet(this);
            });

        });

    }
});


$.nette.ext('bs-popover', {
    load: function () {
        var self = this;

        this.initModalSlider('[data-toggle="popover"]');
        // this.initModalSlider('[data-toggle="tooltip"]');
    }

    }, {

    /**
     * init slider on bootstrap shown
     * https://seiyria.com/bootstrap-slider/
     *
     * data-custom-input-min="inputName"
     * data-custom-input-max="inputName"
     *
     * @param selector
     */
    initModalSlider: function (selector) {
        // $(selector).popover();
        $(selector).tooltip()
    }
});


$.nette.ext('select2', {
    load: function () {
        var self = this;

        this.initSelect2('.grido:not(.no-select2) select:not(.no-select2)');
    }

    }, {

    /**
     * init select2
     * https://select2.org/
     *
     * @param selector
     */
    initSelect2: function (selector) {
        $(selector).select2();
    }
});


$.nette.ext('smoothScroll', {
    success: function(payload, status, xhr, settings) {
        if (settings.nette && payload.scrollTo) {
            $.smoothScroll({ scrollTarget: payload.scrollTo });
        }
    }
});


$.nette.ext('fancyTree', {
    load: function(payload, status, xhr, settings) {
        var self = true;

        $('.tree').fancytree({
            checkbox: true,
            selectMode: 2,

            select: function(event, data) {
                // Display list of selected nodes
                var id = data.node.data['id'];
                var target = data.node.data['target'];
                if (id) {
                }
                if (target) {
                    $(target).prop('checked', data.node.isSelected());
                }
            }
        });

        $('.tree').each(function () {

            var checkBoxes = $(this).data('checkbox-list');
            var tree = $(this).fancytree("getTree");
            var node = tree.getNodeByKey("_2"); // for example
            nodes = tree.findAll("");

            var selected = [];
            $.each($(checkBoxes + ":checked"), function(){
                selected.push(parseInt($(this).val()));
            });

            $.each(nodes, function (index, node) {
                if ($.inArray(node.data['id'], selected) > -1) {
                    node.setSelected(true);
                }
            })

        });
    }
}, {
    selector: '.tree'
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
         * emulate click to detail grid
         */
    }).on('click', 'a[data-toggle="detail-click"]', function (e) {
        e.preventDefault();
        $(this).closest('.col-action').find('[data-toggle-detail]').click();


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
    }).on("change", "_form#frm-deviceGroupListGridControl-filter input.happy", function(e){

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
            /*
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
            */
            /********************************************************/


            /*
             * unselectujeme všechny rodiče
             */
            /*
            if (checked == false) {
                var parents = $(item).parents('.datagrid-tree-item');

                $( parents ).each(function( ) {
                    $(this).find('.happy-checkbox').first().removeClass('active');
                    $(this).find('input[data-check]').first().prop( "checked", false );
                });
            }
            */
            /********************************************************/


            /*
             * odešleme current selectovaný prvek na server, ten sám označí potomky a rodiče
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


    /**
     * editace cílových skupin na zařízení
     */
    $(document).on("click", ".col-dtg-edit:not(.edit)", function(e) {

        var cell = this;
        var valueToEdit = "";
        var targetGroups = $(this).closest('table').data('target-groups');
        var selects = $(this).data('select');

        $.each(targetGroups, function (id, val) {
            var optionSelect = id in selects
                ? " selected='selected' "
                : "";

            valueToEdit += "<option value='" + id + "'" + optionSelect + ">" + val + "</option>";
        });


        input = $('<select class="select2" style="width: 100%;" multiple="multiple">' + valueToEdit + '</select>');


        $(cell).html(input);

        $(input).select2().select2('open');
        // $(input).select2('open');
        // $(input).trigger('select2:open');

        $(cell).addClass('edit');

        $(input).on('change', function (e) {

            var cell = $(this).closest('.col-dtg-edit');
            var url = $(cell).closest('table').data('url');
            var time = $(cell).data('time');
            var day = $(cell).data('day');

            var paramDay = $(cell).closest('table').data('param-day');
            var paramTime = $(cell).closest('table').data('param-time');
            var paramValues = $(cell).closest('table').data('param-values');
            var data = {};

            data[paramDay] = day;
            data[paramTime] = time;
            data[paramValues] = JSON.stringify($(this).val());

            $.nette.ajax({
                type: "POST",
                url: url,
                data: data,
                success: function (payload) {
                    // snippets
                    if (payload.snippets) {
                        $.nette.ext('snippets').updateSnippets(payload.snippets);
                    }
                },
                fail: function (payload) {
                    console.log(payload);
                }
            });

        }).on('change.select2', function (e) {

        }).on('select2:unselect', function (e) {

        });

    });


});



