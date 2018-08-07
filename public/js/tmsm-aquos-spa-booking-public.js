(function ($) {
  'use strict';

  $.fn.datepicker.defaults.language = tmsm_aquos_spa_booking_params.locale;

  // Load Product Category
  var tmsmAquosSpaBookingLoadProductCategories = $(function() {
    console.log('tmsmAquosSpaBookingLoadProductCategories');

    var productcategory_template = wp.template( 'tmsm-aquos-spa-booking-product-category' );
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
          $.each(data.product_categories, function(index, product_category) {
            console.log(product_category);
            $( '#tmsm-aquos-spa-booking-categories' ).append( productcategory_template( product_category ) );
          } );
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

    // Load Times
    var tmsmAquosSpaBookingLoadTimes = function(date){
      console.log('tmsmAquosSpaBookingLoadTimes');
      var date_formatted = date.format('yyyy-mm-dd');
      $( '#tmsm-aquos-spa-booking-times' ).empty();
      var product_category = $('#tmsm-aquos-spa-booking-selected-productcategory').val();
      var product = $('#tmsm-aquos-spa-booking-selected-product').val();

      if(product && product_category && date_formatted){

        $('#tmsm-aquos-spa-booking-selected-date').val(date_formatted);
        var time_template = wp.template( 'tmsm-aquos-spa-booking-time' );
        var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
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
              $.each(data.times, function(index, time) {
                var date = new Date();
                date.setHours(time.hour);
                date.setMinutes(0);
                date.setSeconds(0);
                var options = {
                  hour: '2-digit',
                  minute:'2-digit'
                };
                time.hour_formatted = date.toLocaleTimeString(tmsm_aquos_spa_booking_params.locale, options);
                $( '#tmsm-aquos-spa-booking-times' ).append( time_template( time ) );
              } );
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

    var tmsmAquosSpaBookingLoadProducts = function(productcategory_element){

      $('#tmsm-aquos-spa-booking-confirm').hide();
      $('#tmsm-aquos-spa-booking-times').empty();
      $('#tmsm-aquos-spa-booking-date-display').empty();

      $( '#tmsm-aquos-spa-booking-products' ).empty();
      var product_template = wp.template( 'tmsm-aquos-spa-booking-product' );
      var product_category = productcategory_element.attr('data-product-category');

      if(product_category){
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
              $.each(data.products, function(index, product) {
                console.log(product);
                $( '#tmsm-aquos-spa-booking-products' ).append( product_template( product ) );
              } );
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
    $('#tmsm-aquos-spa-booking-categories').on('click', '.tmsm-aquos-spa-booking-product-category', function(e) {
      e.preventDefault();
      tmsmAquosSpaBookingLoadProducts($(this))
    });

    // Product Selection
    $('#tmsm-aquos-spa-booking-products').on('click', '.tmsm-aquos-spa-booking-product-select', function(e) {
      e.preventDefault();

      var product = $(this).attr('data-product');

      if(product){
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

    $('#tmsm-aquos-spa-booking-datepicker').datepicker({
    }).on('changeDate', function(date) {
      tmsmAquosSpaBookingLoadTimes(date);
    });


    // Time Selection
    $('#tmsm-aquos-spa-booking-times').on('click', '.tmsm-aquos-spa-booking-time', function(e) {
      e.preventDefault();

      var time = $(this).attr('data-time');

      if(time){
        $('#tmsm-aquos-spa-booking-selected-time').val(time);

        $('.tmsm-aquos-spa-booking-time-group').removeClass('selected').addClass('not-selected');
        $('.tmsm-aquos-spa-booking-time').removeClass('disabled');
        var groupitem = $(this).closest('.tmsm-aquos-spa-booking-time-group');
        groupitem.removeClass('not-selected').addClass('selected');
        $(this).addClass('disabled');

        $('#tmsm-aquos-spa-booking-confirm').show();
      }
    });


    /*
    wp.api.init( { 'versionString' : 'wc/v2/' } );

    console.log('wp.api.collections:');
    console.log(wp.api.collections);

    var productcategory_template = wp.template( 'tmsm-aquos-spa-booking-categories' );
    var productcategories = new wp.api.collections.Products();
    console.log('productcategories:');
    productcategories.fetch().done( function() {
      console.log('done');
      productcategories.each( function( productcategory ) {
        console.log( productcategory.attributes );
        //$( '#tmsm-aquos-spa-booking-categories' ).append( productcategory_template( productcategory.attributes ) );
      } );
    } );




    if ( $('#tmsm-aquos-spa-booking').length ) {
      wp.api.loadPromise.done(function () {
      })
    }


    $('.tmsm-aquos-spa-booking-categories-item').on('click', function(e){
      console.log('click category');
      var product_category_id = $(this).data('id');

      console.log(product_category_id);

      var productCollection = new wp.api.collections.Product();
      productCollection.fetch(
        {
          data: {
            per_page: 20,
            category: 51,
            product_cat: 51,
            category_ids: 51,
          }
        }
      );
      console.log(productCollection);

    });*/

 });


})(jQuery);
