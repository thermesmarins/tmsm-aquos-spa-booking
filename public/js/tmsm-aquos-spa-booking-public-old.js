var TmsmAquosSpaBooking = TmsmAquosSpaBooking || {};

(function ($, TmsmAquosSpaBooking) {
  'use strict';

  var selected_date = null;

  // Check existence of shortcode
  if ($('#tmsm-aquos-spa-booking-form').length > 0) {

    // Animate Display
    var tmsmAquosSpaBookingAnimate = function(element){
      console.log('tmsmAquosSpaBookingAnimate ' + element.attr('id'))
      element.show();
      $('html, body').animate({
        scrollTop: element.offset().top
      }, 400);
    };

    // Load Product Category
    var tmsmAquosSpaBookingReset = function () {

      $('#tmsm-aquos-spa-booking-cancel').hide();
      $('#tmsm-aquos-spa-booking-voucher-container input').attr('checked', false).removeAttr('checked').prop('checked', false);
      $('#tmsm-aquos-spa-booking-categories-container').hide();
      $('#tmsm-aquos-spa-booking-products-container').hide();
      $('#tmsm-aquos-spa-booking-variations-container').hide();
      $('#tmsm-aquos-spa-booking-date-container').hide();
      $('#tmsm-aquos-spa-booking-times-container').hide();

      $('#tmsm-aquos-spa-booking-confirm').hide();
      $('#tmsm-aquos-spa-booking-times').empty();
      $('#tmsm-aquos-spa-booking-date-display').empty();

      $('#tmsm-aquos-spa-booking-selected-hasvoucher').val('');
      $('#tmsm-aquos-spa-booking-selected-productcategory').val('');
      $('#tmsm-aquos-spa-booking-selected-product').val('');
      $('#tmsm-aquos-spa-booking-selected-time').val('');
      $('#tmsm-aquos-spa-booking-selected-date').val('');

    };

    // Load Product Category
    var tmsmAquosSpaBookingLoadProductCategories = function () {

      console.log('tmsmAquosSpaBookingLoadProductCategories');
      $('#tmsm-aquos-spa-booking-cancel').show();

      tmsmAquosSpaBookingAnimate($('#tmsm-aquos-spa-booking-categories-container'));
      $('#tmsm-aquos-spa-booking-products-container').hide();
      $('#tmsm-aquos-spa-booking-variations-container').hide();
      $('#tmsm-aquos-spa-booking-date-container').hide();
      $('#tmsm-aquos-spa-booking-times-container').hide();

      $('#tmsm-aquos-spa-booking-confirm').hide();
      $('#tmsm-aquos-spa-booking-times').empty();
      $('#tmsm-aquos-spa-booking-date-display').empty();

      $('#tmsm-aquos-spa-booking-selected-productcategory').val('');
      $('#tmsm-aquos-spa-booking-selected-product').val('');
      $('#tmsm-aquos-spa-booking-selected-time').val('');
      $('#tmsm-aquos-spa-booking-selected-date').val('');

      var productcategory_template = wp.template('tmsm-aquos-spa-booking-product-category');

      $('#tmsm-aquos-spa-booking-categories').empty().val('').prop('disabled', true).attr('title', tmsm_aquos_spa_booking_params.i18n.loading).selectpicker('destroy').selectpicker();

      $.ajax({
        url: _wpUtilSettings.ajax.url,
        type: 'post',
        dataType: 'json',
        enctype: 'multipart/form-data',
        data: {
          action: 'tmsm-aquos-spa-booking-product-categories',
          security: $('#tmsm-aquos-spa-booking-nonce').val()
        },
        success: function (data) {
          if (data.success === true) {
            $('#tmsm-aquos-spa-booking-categories').empty().val('').prop('disabled', false).attr('title', tmsm_aquos_spa_booking_params.i18n.selectcategory).selectpicker('destroy').selectpicker();
            $.each(data.product_categories, function (index, product_category) {
              $('#tmsm-aquos-spa-booking-categories').append(productcategory_template(product_category));
            });
            $('#tmsm-aquos-spa-booking-categories').selectpicker('refresh');
          }
          else {
            // App error
          }
        },
        error: function (jqXHR, textStatus) {
          // Ajax error
          console.log(jqXHR);
          console.log(textStatus);
        }
      });
    };

    // Load Products
    var tmsmAquosSpaBookingLoadProducts = function (productcategory_element) {

      tmsmAquosSpaBookingAnimate($('#tmsm-aquos-spa-booking-products-container'));

      $('#tmsm-aquos-spa-booking-variations-container').hide();
      $('#tmsm-aquos-spa-booking-date-container').hide();
      $('#tmsm-aquos-spa-booking-times-container').hide();

      $('#tmsm-aquos-spa-booking-cancel').show();
      $('#tmsm-aquos-spa-booking-confirm').hide();
      $('#tmsm-aquos-spa-booking-times').empty();
      $('#tmsm-aquos-spa-booking-date-display').empty();

      $('#tmsm-aquos-spa-booking-products').empty().val('').prop('disabled', true).attr('title', tmsm_aquos_spa_booking_params.i18n.loading).selectpicker('destroy').selectpicker();

      var product_template = wp.template('tmsm-aquos-spa-booking-product');
      var product_category = productcategory_element.val();

      if (product_category) {
        $('#tmsm-aquos-spa-booking-selected-productcategory').val(product_category);

        //$('#tmsm-aquos-spa-booking-categories .list-group-item').removeClass('active');
        //productcategory_element.closest('.list-group-item').addClass('active');

        $.ajax({
          url: _wpUtilSettings.ajax.url,
          type: 'post',
          dataType: 'json',
          enctype: 'multipart/form-data',
          data: {
            action: 'tmsm-aquos-spa-booking-products',
            productcategory: product_category,
            security: $('#tmsm-aquos-spa-booking-nonce').val()
          },
          success: function (data) {
            if (data.success === true) {
              //console.log('data.product:');
              //console.log(data.products);
              $('#tmsm-aquos-spa-booking-products').empty().val('').prop('disabled', false).attr('title', tmsm_aquos_spa_booking_params.i18n.selectproduct).selectpicker('destroy').selectpicker();

              $.each(data.products, function (index, product) {
                product.is_voucher = $('#tmsm-aquos-spa-booking-selected-hasvoucher').val();
                $('#tmsm-aquos-spa-booking-products').append(product_template(product));
              });
              $('#tmsm-aquos-spa-booking-products').selectpicker('refresh');

            }
            else {
              // App error
            }
          },
          error: function (jqXHR, textStatus) {
            // Ajax error
            console.log(jqXHR);
            console.log(textStatus);
          }
        });
      }
    };


    // Load Products
    var tmsmAquosSpaBookingLoadProductVariations = function (product_element) {

      tmsmAquosSpaBookingAnimate($('#tmsm-aquos-spa-booking-variations-container'));

      $('#tmsm-aquos-spa-booking-date-container').hide();
      $('#tmsm-aquos-spa-booking-times-container').hide();

      $('#tmsm-aquos-spa-booking-confirm').hide();
      $('#tmsm-aquos-spa-booking-times').empty();
      $('#tmsm-aquos-spa-booking-date-display').empty();

      $('#tmsm-aquos-spa-booking-variations').empty().val('').prop('disabled', true).attr('title', tmsm_aquos_spa_booking_params.i18n.loading).selectpicker('destroy').selectpicker();

      var product_variation_template = wp.template('tmsm-aquos-spa-booking-product-variation');
      var product_attribute_template = wp.template('tmsm-aquos-spa-booking-product-attribute');
      var product = product_element.val();

      if (product) {

        $.ajax({
          url: _wpUtilSettings.ajax.url,
          type: 'post',
          dataType: 'json',
          enctype: 'multipart/form-data',
          data: {
            action: 'tmsm-aquos-spa-booking-variations',
            product: product,
            security: $('#tmsm-aquos-spa-booking-nonce').val()
          },
          success: function (data) {
            if (data.success === true) {
              //console.log('data.product:');
              //console.log(data.products);
              $('#tmsm-aquos-spa-booking-variations').empty().val('').prop('disabled', false).attr('title', tmsm_aquos_spa_booking_params.i18n.selectproduct).selectpicker('destroy').selectpicker();

              $.each(data.variations, function (index, variation) {

                variation.is_voucher = $('#tmsm-aquos-spa-booking-selected-hasvoucher').val();
                $('#tmsm-aquos-spa-booking-variations').append(product_variation_template(variation));
              });

              var $list = $('#tmsm-aquos-spa-booking-attributes');

              /*TmsmAquosSpaBooking.attributesview.destroy();

              $.each(data.attributes, function (index, attribute) {
                console.log(attribute);
                var item = $attributesview.change( { model: attribute } );
                //var item = new TmsmAquosSpaBooking.AttributesView( { model: attribute } );
                $list.prepend( item.render().$el );
              });*/
              /*
              $.each(data.attributes, function (index, attribute) {
                $('#tmsm-aquos-spa-booking-attributes').append(product_attribute_template(attribute));
              });*/

              $('#tmsm-aquos-spa-booking-variations').selectpicker('refresh');

            }
            else {
              // App error
            }
          },
          error: function (jqXHR, textStatus) {
            // Ajax error
            console.log(jqXHR);
            console.log(textStatus);
          }
        });
      }
    };

    // Load Times
    var tmsmAquosSpaBookingLoadDate = function (product) {
      tmsmAquosSpaBookingAnimate($('#tmsm-aquos-spa-booking-date-container'));
      $('#tmsm-aquos-spa-booking-times-container').hide();
    };

    // Load Times
    var tmsmAquosSpaBookingLoadTimes = function (date) {

      tmsmAquosSpaBookingAnimate($('#tmsm-aquos-spa-booking-times-container'));

      var date_formatted = date.format('yyyy-mm-dd');
      $('#tmsm-aquos-spa-booking-times').empty();
      var product_category = $('#tmsm-aquos-spa-booking-selected-productcategory').val();
      var product = $('#tmsm-aquos-spa-booking-selected-product').val();

      if (product && product_category && date_formatted) {

        $('#tmsm-aquos-spa-booking-selected-date').val(date_formatted);
        var time_template = wp.template('tmsm-aquos-spa-booking-time');
        var options = {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'};
        $('#tmsm-aquos-spa-booking-date-display').html(date.date.toLocaleDateString(tmsm_aquos_spa_booking_params.locale, options));

        $.ajax({
          url: _wpUtilSettings.ajax.url,
          type: 'post',
          dataType: 'json',
          enctype: 'multipart/form-data',
          data: {
            action: 'tmsm-aquos-spa-booking-times',
            productcategory: product_category,
            product: product,
            date: date_formatted,
            security: $('#tmsm-aquos-spa-booking-nonce').val()
          },
          success: function (data) {
            if (data.success === true) {
              //console.log(data);
              var $list = $('#tmsm-aquos-spa-booking-times').empty();

              $('#tmsm-aquos-spa-booking-times-error').empty();
              $.each(data.errors, function (index, error) {
                console.log(error);
                $('#tmsm-aquos-spa-booking-times-error').append( error );
              });

              $.each(data.times, function (index, time) {
                var date = new Date();
                console.log(time);
                if(time.Hour){
                  var timesplit = time.Hour.split(':');
                  console.log(timesplit);
                  date.setHours(timesplit[0]);
                  date.setMinutes(timesplit[1]);
                  date.setSeconds(0);
                  var options = {
                    hour: '2-digit',
                    minute: '2-digit'
                  };
                  time.hour_formatted = date.toLocaleTimeString(tmsm_aquos_spa_booking_params.locale, options);

                  //var addtemplate = time_template(time);
                  //$('#tmsm-aquos-spa-booking-times').append(addtemplate);

                  var item = new TmsmAquosSpaBooking.TimesView( { model: time } );
                  $list.append( item.render().$el );
                }

              });
            }
            else {
              // App error
            }
          },
          error: function (jqXHR, textStatus) {
            // Ajax error
            console.log(jqXHR);
            console.log(textStatus);
          }
        });
      }
    };



    /**
     * Times View
     *
     * @see https://deliciousbrains.com/building-reactive-wordpress-plugins-part-1-backbone-js/
     */
    TmsmAquosSpaBooking.TimesView = Backbone.View.extend( {
      tagName: 'li',
      className: 'tmsm-aquos-spa-booking-time-listitem',
      template: wp.template('tmsm-aquos-spa-booking-time'),
      //template: wp.template( $( '#cron-pixie-event-item-tmpl' ).html() ),

      initialize: function() {
        $('#tmsm-aquos-spa-booking-times-loading').hide();
        this.listenTo( this.model, 'change', this.render );
        this.listenTo( this.model, 'destroy', this.destroy );
        this.listenTo( this.model, 'destroy', this.remove );
      },

      events: {
        'click .tmsm-aquos-spa-booking-time': 'selectTime',
        'click .tmsm-aquos-spa-booking-time-change-label': 'changeTime'
      },

      destroy: function(){
        $('#tmsm-aquos-spa-booking-times-loading').show();
      },

      render: function() {
        var html = this.template( this.model );
        this.$el.html( html );
        return this;
      },

      selectTime: function(e) {
        e.preventDefault();
        console.log('selectTime');

        var time = this.model.hour_formatted;

        if (time) {
          $('#tmsm-aquos-spa-booking-selected-time').val(time);

          //$('.tmsm-aquos-spa-booking-time-listitem').removeClass('selected').addClass('not-selected');
          $('.tmsm-aquos-spa-booking-time-listitem').removeClass('disabled').removeClass('selected').addClass('not-selected');
          //var groupitem = $(this).closest('.tmsm-aquos-spa-booking-time-listitem');
          //groupitem.removeClass('not-selected').addClass('selected');
          //$(this).addClass('disabled').addClass('selected').removeClass('not-selected');
          this.$el.addClass('selected').removeClass('not-selected').find('.tmsm-aquos-spa-booking-time').addClass('disabled');

          $('#tmsm-aquos-spa-booking-confirm').show();
        }
      },

      changeTime: function() {
        console.log('changeTime');
        tmsmAquosSpaBookingLoadTimes(selected_date);
      },

      runNow: function() {
        console.log('runNow');
      }
      /*runNow: function() {
        CronPixie.pauseTimer();

        // Only bother to run update if not due before next refresh.
        var seconds_due = this.model.get( 'seconds_due' );

        if ( seconds_due > CronPixie.timer_period ) {
          var timestamp = this.model.get( 'timestamp' ) - seconds_due;
          this.model.save(
            { timestamp: timestamp, seconds_due: 0 },
            {
              success: function( model, response, options ) {

                 console.log( options );
                 console.log( response );

                CronPixie.runTimer();
              },
              error: function( model, response, options ) {

                 console.log( options );
                 console.log( response );

                CronPixie.runTimer();
              }
            }
          );
        }
      }*/
    } );


    TmsmAquosSpaBooking.VoucherView = Backbone.View.extend( {
      el: '#tmsm-aquos-spa-booking-voucher-container',

      initialize: function() {
      },
      events: {
        'click input': 'selectVoucher'
      },
      selectVoucher: function(event){
        console.log('selectVoucher');
        $('#tmsm-aquos-spa-booking-selected-hasvoucher').val($(event.target).val());
        tmsmAquosSpaBookingLoadProductCategories();
      },
      render: function() {
      }
    } );



    // Product Category Selection
    $('#tmsm-aquos-spa-booking-categories').on('change', function (e) {
      e.preventDefault();
      tmsmAquosSpaBookingLoadProducts($(this));
    });

    // Product Selection
    $('#tmsm-aquos-spa-booking-products').on('change', function (e) {
      e.preventDefault();

      var product = $(this).val();

      var product_is_variable = $(this).find(':selected').data('variable');

      if(product_is_variable == '1'){

        // Show Product Variations Selection
        console.log('variable');

        tmsmAquosSpaBookingLoadProductVariations($(this));

      }
      else{
        // Show Date Selection
        console.log('not variable');

        if (product) {
          $('#tmsm-aquos-spa-booking-selected-product').val(product);
        }

        tmsmAquosSpaBookingLoadDate(product);

      }


    });


    // Product Variation Selection
    $('#tmsm-aquos-spa-booking-variations').on('change', function (e) {
      e.preventDefault();

      var product = $(this).val();

      if (product) {
        $('#tmsm-aquos-spa-booking-selected-product').val(product);
      }

      tmsmAquosSpaBookingLoadDate(product);

    });

    // Datepicker
    $('#tmsm-aquos-spa-booking-datepicker').datepicker({
      language: tmsm_aquos_spa_booking_params.locale,
      format: 'yyyy-mm-dd',
      startDate: tmsm_aquos_spa_booking_params.options.startdate,
      endDate: tmsm_aquos_spa_booking_params.options.enddate,
    }).on('changeDate', function (date) {
      selected_date = date;
      tmsmAquosSpaBookingLoadTimes(date);
    });

    // Time Selection
    /*$('#tmsm-aquos-spa-booking-times').on('click', '.tmsm-aquos-spa-booking-time', function (e) {
      e.preventDefault();

      var time = $(this).attr('data-time');

      if (time) {
        $('#tmsm-aquos-spa-booking-selected-time').val(time);

        $('.tmsm-aquos-spa-booking-time-group').removeClass('selected').addClass('not-selected');
        $('.tmsm-aquos-spa-booking-time').removeClass('disabled');
        var groupitem = $(this).closest('.tmsm-aquos-spa-booking-time-group');
        groupitem.removeClass('not-selected').addClass('selected');
        $(this).addClass('disabled');

        $('#tmsm-aquos-spa-booking-confirm').show();
      }
    });*/

    $('.tmsm-aquos-spa-booking-time-change-label').on('click', function(e){
      // TODO change label
      e.preventDefault();
      console.log('.tmsm-aquos-spa-booking-time-change-label');
      tmsmAquosSpaBookingLoadTimes(selected_date);
    });


    // Confirm
    $('#tmsm-aquos-spa-booking-cancel').on('click', function (e) {
      e.preventDefault();
      tmsmAquosSpaBookingReset();
    });

      // Confirm
    $('#tmsm-aquos-spa-booking-confirm').on('click', function (e) {
      e.preventDefault();

      $(this).addClass('disabled');
      $('#tmsm-aquos-spa-booking-confirm-loading').show();

      var has_voucher = $('#tmsm-aquos-spa-booking-selected-hasvoucher').val();
      var product_category = $('#tmsm-aquos-spa-booking-selected-productcategory').val();
      var product = $('#tmsm-aquos-spa-booking-selected-product').val();
      var date = $('#tmsm-aquos-spa-booking-selected-date').val();
      var time = $('#tmsm-aquos-spa-booking-selected-time').val();

      if (product && product_category && date && time) {

        $.ajax({
          url: _wpUtilSettings.ajax.url,
          type: 'post',
          dataType: 'json',
          enctype: 'multipart/form-data',
          data: {
            action: 'tmsm-aquos-spa-booking-addtocart',
            has_voucher: has_voucher,
            productcategory: product_category,
            product: product,
            date: date,
            time: time,
            security: $('#tmsm-aquos-spa-booking-nonce').val()
          },
          success: function (data) {
            if (data.success === true) {

              if(data.redirect){
                window.location = data.redirect;
              }

            }
            else {
              // App error
            }
          },
          error: function (jqXHR, textStatus) {
            // Ajax error
            console.log(jqXHR);
            console.log(textStatus);
          }
        });
      }

    });


    /**
     * Set initial data into view and start recurring display updates.
     */
    TmsmAquosSpaBooking.init = function() {
      console.log('TmsmAquosSpaBooking.init');
      TmsmAquosSpaBooking.voucherview = new TmsmAquosSpaBooking.VoucherView();
      //TmsmAquosSpaBooking.timesview.render();
      tmsmAquosSpaBookingReset();

    };


    $( document ).ready( function() {
      TmsmAquosSpaBooking.init();
    } );
  }



})(jQuery, TmsmAquosSpaBooking);
