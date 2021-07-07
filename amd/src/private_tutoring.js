// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * intellicart PrivateTutoring
 *
 * @package    local_intellicart
 * @copyright  2017 IntelliBoard
 */

define(['jquery', 'core/ajax', 'core/log', 'block_products_catalog/bxslider', 'local_intellicart/intellicart'],
    function($, ajax, log, Bxslider, Intellicart) {

    var PrivateTutoring = {

        productid:                  0,
        container:                  '',
        loader:                     '<i class="loader fa fa-circle-o-notch fa-spin"></i>',
        sliderbox:                  '.productview-instructors-box',
        contentcontainer:           '.private-tutoring-instructors',
        instructoritemcontainer:    '.productview-instructor-item',
        instructordetailscontainer: '.productview-instructors-details',

        init: function(productid) {

            PrivateTutoring.productid = productid;
            PrivateTutoring.container = $(PrivateTutoring.contentcontainer);
            Bxslider.init($, document, window, undefined);

            PrivateTutoring.container.on('click', PrivateTutoring.instructoritemcontainer, function() {
                PrivateTutoring.container.find(PrivateTutoring.instructoritemcontainer).removeClass('active');
                $(this).addClass('active');
                var instructorid = $(this).attr('data-instructor-id');

                PrivateTutoring.loadInstructorDetails(instructorid);
            });

            $(PrivateTutoring.instructordetailscontainer).on('click', '.sessions-list input[type="radio"]', function() {
                var sid = $(this).val();
                Intellicart.getsessionstatus(productid, sid);
                $(".product-sessions").find('.session-item').removeClass('open');
                $(this).parents('.session-item').addClass('open');
            });

            $('#product_instructors').on('keyup', '#search_instructors', function() {
                var filter = $(this).val().toLowerCase();

                if (filter) {
                    $(PrivateTutoring.instructoritemcontainer).hide();
                    $(PrivateTutoring.instructoritemcontainer).find('.instructorname').each(function() {
                        if($(this).text().toLowerCase().indexOf(filter) >= 0){
                            $(this).parents(PrivateTutoring.instructoritemcontainer).show();
                        }
                    });
                } else {
                    $(PrivateTutoring.instructoritemcontainer).show();
                }
            });

            $('#product_instructors').on('keypress', '#search_instructors', function(e) {
                return e.which !== 13;
            });

            if ($(PrivateTutoring.sliderbox).length) {
                $(PrivateTutoring.sliderbox).bxSlider({
                    controls: true,
                    pager: false,
                    infiniteLoop: false,
                    hideControlOnEnd: true,
                    responsive: true,
                    minSlides: 3,
                    maxSlides: 3,
                    moveSlides: 3,
                    slideWidth: 300,
                    slideMargin: 15,
                    shrinkItems: true,
                    touchEnabled: false
                });
            }
        },

        loadInstructorDetails: function(instructorid) {
            $(PrivateTutoring.instructordetailscontainer).html(PrivateTutoring.loader);

            ajax.call([{
                methodname: 'local_intellicart_load_instructor_details', args: {
                    productid: PrivateTutoring.productid,
                    instructorid: instructorid,
                    day: ''
                }
            }])[0]
                .done(function (response) {
                    try {
                        $(PrivateTutoring.instructordetailscontainer).html(response.content);

                        // load date filter
                        ajax.call([{
                            methodname: 'local_intellicart_get_product_sessions_days', args: {
                                id: PrivateTutoring.productid,
                                purchased: 0,
                                instructorid: instructorid
                            }
                        }])[0]
                            .done(function (response) {
                                try {
                                    var enabledDates = JSON.parse(response.days);

                                    document.getElementById("instructor_session_day").flatpickr({
                                        dateFormat: "Y/m/d",
                                        onChange: function(dateObj, dateStr) {
                                            Intellicart.disablebuybuttons();
                                            PrivateTutoring.loadInstructorSessions(instructorid, dateStr);
                                        },
                                        disable: [
                                            function(dateObject){
                                                for (var i = 0; i < enabledDates.length; i++) {
                                                    if(dateObject.getTime() === new Date(enabledDates[i]).getTime()) {
                                                        return false;
                                                    }
                                                }
                                                return true;
                                            }
                                        ]
                                    });

                                } catch (Error) {
                                    log.debug(Error.message);
                                }
                            }).fail(function (ex) {
                            log.debug(ex.message);
                        });

                    } catch (Error) {
                        log.debug(Error.message);
                        $(PrivateTutoring.instructordetailscontainer).html('');
                    }
                }).fail(function (ex) {
                log.debug(ex.message);
                $(PrivateTutoring.instructordetailscontainer).html('');
            });
        },

        loadInstructorSessions: function(instructorid, day) {

            $(PrivateTutoring.instructordetailscontainer).find(Intellicart.sessionscontainer).html(Intellicart.loader);

            ajax.call([{
                methodname: 'local_intellicart_get_product_sessions_time', args: {
                    id: PrivateTutoring.productid,
                    day: day,
                    purchased: 0,
                    instructorid: instructorid,
                    checkoutid: 0
                }
            }])[0]
                .done(function (response) {
                    try {
                        $(PrivateTutoring.instructordetailscontainer).find(Intellicart.sessionscontainer).html(response.sessions);
                    } catch (Error) {
                        log.debug(Error.message);
                    }
                }).fail(function (ex) {
                log.debug(ex.message);
            });

        },

    };

    /**
     * @alias module:local_intellicart/private_tutoring
     */
    return PrivateTutoring;

});