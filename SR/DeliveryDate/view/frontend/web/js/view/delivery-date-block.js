define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Ui/js/form/element/abstract',
    'mage/calendar'
], function ($, ko, Component) {
    'use strict';


$(document).on('change', '#checkout-shipping-method-load input[type=radio]', function (event) {
    var target = event.target;
    if(target.value == 'deliverydateandtime_deliverydateandtime') {
        $('#deleverydateandtimeid').show();

    } else{
        $('#deleverydateandtimeid').hide();
    }

    event.preventDefault();
});

    var active = window.checkoutConfig.shipping.delivery_date.active;

       if(active == 1){
    return Component.extend({
        
        defaults: {
            template: 'SR_DeliveryDate/delivery-date-block',
            customdata: ''
        },

        initialize: function () {
            this._super();
            var disabled = window.checkoutConfig.shipping.delivery_date.disabled;
            var noday = window.checkoutConfig.shipping.delivery_date.noday;
            var hourMin = parseInt(window.checkoutConfig.shipping.delivery_date.hourMin);
            var hourMax = parseInt(window.checkoutConfig.shipping.delivery_date.hourMax);
            var format = window.checkoutConfig.shipping.delivery_date.format;
            var starttimne = window.checkoutConfig.shipping.delivery_date.starttimne;

            var startDate = window.checkoutConfig.shipping.delivery_date.startDate;
            var endDate = window.checkoutConfig.shipping.delivery_date.endDate;
            var cutofmin = window.checkoutConfig.shipping.delivery_date.cutofmin;

            var date = new Date();
            if(startDate =='today'){
                startDate = startDate;
            }else{
                startDate = startDate;
            }
            if(endDate){
                endDate = endDate;
            }else{
                endDate = '+7d';
            }
            
           

            if(!format) {
                format = 'yy-mm-dd';
            }
            var disabledDay = disabled.split(",").map(function(item) {
                return parseInt(item, 10);
            });

            ko.bindingHandlers.datepicker = {
                init: function (element, valueAccessor, allBindingsAccessor) {
                  
                        var $el = $(element);
                  
                //    console.log($el);
                    //initialize datepicker
                    if(noday) {
                        var options = {
                            showsTime: false,
                            dateFormat:format,
                            hourMin: hourMin,
                            hourMax: hourMax,
                            minDate: startDate,
                            maxDate: endDate,
                           
                        };
                    } else {
                        var options = {
                            showsTime: false,
                            dateFormat:format,
                            hourMin: hourMin,
                            hourMax: hourMax,
                            minDate: startDate,
                            maxDate: endDate,
                            beforeShowDay: function(date) {
                                var day = date.getDay();
                                alert(day);
                                if(disabledDay.indexOf(day) > -1) {
                                    return [false];
                                } else {
                                    return [true];
                                }
                            }
                        };
                    }

                    $el.datepicker(options);

                    var writable = valueAccessor();
                    if (!ko.isObservable(writable)) {
                        var propWriters = allBindingsAccessor()._ko_property_writers;
                        if (propWriters && propWriters.datepicker) {
                            writable = propWriters.datepicker;
                        } else {
                            return;
                        }
                    }
                    writable($(element).datepicker("getDate"));
                },
                // update: function (element, valueAccessor) {
                //     var widget = $(element).data("datepicker");
               
                //     if (widget) {
                //         var date = ko.utils.unwrapObservable(valueAccessor());
                //         widget.date(date);
                //     }
                // }
                
            };

            return this;

        },
            initObservable: function() {
                var customvalue = window.checkoutConfig.shipping.delivery_time.customvalue;
                var customtime = window.checkoutConfig.shipping.otherdelevery_time.customtime;
                var deepak = this._super().observe([
                    'customdata','hasForm'
                ]);


              $(document).on('change', '#onepage-checkout-shipping-method-additional-load #delivery_date', function (event) {
                    var target = event.target;
                    var userselecteddate = target.value;
                    var userselecteddatefull = new Date(userselecteddate);
                    var today = new Date();
                    var isToday = (today.toDateString() == userselecteddatefull.toDateString());
                    

                   if(isToday){
                    
                    var element = $('#delivery_time')[0]; 
                    ko.cleanNode(element);

                    var OptionsViewModel = function() {
                        var self = this;
                        self.hasForm = ko.observable(true);
                        if(customvalue.toString().indexOf("available") !== -1){
                            self.hasForm = ko.observable(false);
                        }
                        if(customvalue.toString().indexOf("end") !== -1){
                            self.hasForm = ko.observable(false);
                        }
                        self.customdata = customvalue;
                    }
                    ko.applyBindings(new OptionsViewModel(),document.getElementById("delivery_time"));
          
                   }else{
                   
                    var element = $('#delivery_time')[0]; 
                    ko.cleanNode(element);

                    var OptionsViewModelother = function() {
                        var self = this;
                        self.hasForm = ko.observable(true);
                        self.customdata = customtime;
                        // self.customdata = customvalue;
                    }
                    ko.applyBindings(new OptionsViewModelother(),document.getElementById("delivery_time"));

                   }
                
                    event.preventDefault();
                });
           
                deepak.hasForm = ko.observable(true);
                if(customvalue.toString().indexOf("available") !== -1){
                    deepak.hasForm = ko.observable(false);
                }else if(customvalue.toString().indexOf("end") !== -1){
                    deepak.hasForm = ko.observable(false);
                }
                deepak.customdata = customvalue;
                return this;
            }
    });

}else{
    return Component.extend({
      
    });
}
    
});
