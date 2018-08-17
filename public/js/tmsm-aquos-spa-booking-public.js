(function ($) {
  'use strict';

  // Check existence of shortcode
  if ($('#tmsm-aquos-spa-booking-form').length > 0) {

    // Load Product Category
    var tmsmAquosSpaBookingLoadProductCategories = function () {
      console.log('tmsmAquosSpaBookingLoadProductCategories');

      var productcategory_template = wp.template('tmsm-aquos-spa-booking-product-category');
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
            $.each(data.product_categories, function (index, product_category) {
              console.log(product_category);
              $('#tmsm-aquos-spa-booking-categories').append(productcategory_template(product_category));
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
    };
    tmsmAquosSpaBookingLoadProductCategories();

    // Load Times
    var tmsmAquosSpaBookingLoadTimes = function (date) {
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
            security: $('#tmsm-aquos-spa-booking-nonce').val(),
          },
          success: function (data) {
            if (data.success === true) {
              $.each(data.times, function (index, time) {
                var date = new Date();
                date.setHours(time.hour);
                date.setMinutes(0);
                date.setSeconds(0);
                var options = {
                  hour: '2-digit',
                  minute: '2-digit'
                };
                time.hour_formatted = date.toLocaleTimeString(tmsm_aquos_spa_booking_params.locale, options);
                $('#tmsm-aquos-spa-booking-times').append(time_template(time));
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

    // Load Products
    var tmsmAquosSpaBookingLoadProducts = function (productcategory_element) {

      $('#tmsm-aquos-spa-booking-confirm').hide();
      $('#tmsm-aquos-spa-booking-times').empty();
      $('#tmsm-aquos-spa-booking-date-display').empty();

      $('#tmsm-aquos-spa-booking-products').empty();
      var product_template = wp.template('tmsm-aquos-spa-booking-product');
      var product_category = productcategory_element.attr('data-product-category');

      if (product_category) {
        $('#tmsm-aquos-spa-booking-selected-productcategory').val(product_category);

        $('#tmsm-aquos-spa-booking-categories .list-group-item').removeClass('active');
        productcategory_element.closest('.list-group-item').addClass('active');

        $.ajax({
          url: _wpUtilSettings.ajax.url,
          type: 'post',
          dataType: 'json',
          enctype: 'multipart/form-data',
          data: {
            action: 'tmsm-aquos-spa-booking-products',
            productcategory: product_category,
            security: $('#tmsm-aquos-spa-booking-nonce').val(),
          },
          success: function (data) {
            if (data.success === true) {
              //console.log('data.product:');
              //console.log(data.products);
              $.each(data.products, function (index, product) {
                $('#tmsm-aquos-spa-booking-products').append(product_template(product));
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

    // Product Category Selection
    $('#tmsm-aquos-spa-booking-categories').on('click', '.tmsm-aquos-spa-booking-product-category', function (e) {
      e.preventDefault();
      tmsmAquosSpaBookingLoadProducts($(this))
    });

    // Product Selection
    $('#tmsm-aquos-spa-booking-products').on('click', '.tmsm-aquos-spa-booking-product-select', function (e) {
      e.preventDefault();

      var product = $(this).attr('data-product');

      if (product) {
        $('#tmsm-aquos-spa-booking-selected-product').val(product);
        $('.tmsm-aquos-spa-booking-product').hide();
        $(this).addClass('disabled');
        $('.tmsm-aquos-spa-booking-product-select-label', this).hide();
        $('.tmsm-aquos-spa-booking-product-selected-label', this).show();
        var groupitem = $(this).closest('.tmsm-aquos-spa-booking-product');
        groupitem.show().addClass('selected');
        $('.tmsm-aquos-spa-booking-product-change-label', groupitem).show();
      }

    });

    // Datepicker
    $('#tmsm-aquos-spa-booking-datepicker').datepicker({
      language: tmsm_aquos_spa_booking_params.locale,
      format: 'yyyy-mm-dd',
      startDate: tmsm_aquos_spa_booking_params.options.startdate,
      endDate: tmsm_aquos_spa_booking_params.options.enddate,
    }).on('changeDate', function (date) {
      tmsmAquosSpaBookingLoadTimes(date);
    });

    // Time Selection
    $('#tmsm-aquos-spa-booking-times').on('click', '.tmsm-aquos-spa-booking-time', function (e) {
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
    });

    // Confirm
    $('#tmsm-aquos-spa-booking-confirm').on('click', function (e) {
      e.preventDefault();

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
            productcategory: product_category,
            product: product,
            date: date,
            time: time,
            security: $('#tmsm-aquos-spa-booking-nonce').val(),
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


  }


})(jQuery);
