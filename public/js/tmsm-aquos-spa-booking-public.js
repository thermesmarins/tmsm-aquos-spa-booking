var TmsmAquosSpaBookingApp = TmsmAquosSpaBookingApp || {};

(function ($, TmsmAquosSpaBooking) {
  'use strict';


  /**
   * A mixin for collections/models.
   * Based on http://taylorlovett.com/2014/09/28/syncing-backbone-models-and-collections-to-admin-ajax-php/
   */
  var AdminAjaxSyncableMixin = {
    url: TmsmAquosSpaBookingApp.ajaxurl,
    action: 'cron_pixie_request',

    sync: function( method, object, options ) {
      if ( typeof options.data === 'undefined' ) {
        options.data = {};
      }

      options.data.nonce = TmsmAquosSpaBookingApp.nonce; // From localized script.
      options.data.action_type = method;

      // If no action defined, set default.
      if ( undefined === options.data.action && undefined !== this.action ) {
        options.data.action = this.action;
      }

      console.log('sync action: '+options.data.action);

      // Reads work just fine.
      if ( 'read' === method ) {
        return Backbone.sync( method, object, options );
      }

      var json = this.toJSON();
      var formattedJSON = {};

      if ( json instanceof Array ) {
        formattedJSON.models = json;
      } else {
        formattedJSON.model = json;
      }

      _.extend( options.data, formattedJSON );

      // Need to use "application/x-www-form-urlencoded" MIME type.
      options.emulateJSON = true;

      // Force a POST with "create" method if not a read, otherwise admin-ajax.php does nothing.
      return Backbone.sync.call( this, 'create', object, options );
    }
  };

  /**
   * A model for all your syncable models to extend.
   * Based on http://taylorlovett.com/2014/09/28/syncing-backbone-models-and-collections-to-admin-ajax-php/
   */
  var BaseModel = Backbone.Model.extend( _.defaults( {
    // parse: function( response ) {
    // Implement me depending on your response from admin-ajax.php!
    // return response;
    // }
  }, AdminAjaxSyncableMixin ) );

  /**
   * A collection for all your syncable collections to extend.
   * Based on http://taylorlovett.com/2014/09/28/syncing-backbone-models-and-collections-to-admin-ajax-php/
   */
  var BaseCollection = Backbone.Collection.extend( _.defaults( {
    // parse: function( response ) {
    // 	Implement me depending on your response from admin-ajax.php!
    // return response;
    // }
  }, AdminAjaxSyncableMixin ) );


  /**
   * Single cron event.
   */
  TmsmAquosSpaBookingApp.EventModel = BaseModel.extend( {
    action: 'cron_pixie_events',
    defaults: {
      schedule: null,
      interval: null,
      hook: null,
      args: null,
      timestamp: null,
      seconds_due: null
    }
  } );

  /**
   * Collection of cron events.
   */
  TmsmAquosSpaBookingApp.EventsCollection = BaseCollection.extend( {
    action: 'cron_pixie_events',
    model: TmsmAquosSpaBookingApp.EventModel
  } );

  /**
   * Single cron schedule with nested cron events.
   */
  TmsmAquosSpaBookingApp.ScheduleModel = BaseModel.extend( {
    action: 'cron_pixie_schedules',
    defaults: {
      name: null,
      interval: null,
      display: null,
      events: new TmsmAquosSpaBookingApp.EventsCollection
    }
  } );

  /**
   * Collection of cron schedules.
   */
  TmsmAquosSpaBookingApp.SchedulesCollection = BaseCollection.extend( {
    action: 'cron_pixie_schedules',
    model: TmsmAquosSpaBookingApp.ScheduleModel
  } );

  /**
   * The main view for listing cron schedules.
   */
  TmsmAquosSpaBookingApp.SchedulesListView = Backbone.View.extend( {
    el: '#cron-pixie-main',

    initialize: function() {
      this.listenTo( this.collection, 'sync', this.render );
    },

    render: function() {
      var $list = this.$( 'ul.cron-pixie-schedules' ).empty();

      this.collection.each( function( model ) {
        var item = new TmsmAquosSpaBookingApp.SchedulesListItemView( { model: model } );
        $list.append( item.render().$el );
      }, this );

      return this;
    }
  } );

  /**
   * A single cron schedule's view.
   */
  TmsmAquosSpaBookingApp.SchedulesListItemView = Backbone.View.extend( {
    tagName: 'li',
    className: 'cron-pixie-schedule',
    template: _.template( $( '#cron-pixie-schedule-item-tmpl' ).html() ),

    initialize: function() {
      this.listenTo( this.model, 'change', this.render );
      this.listenTo( this.model, 'destroy', this.remove );
    },

    render: function() {
      var html = this.template( this.model.toJSON() );
      this.$el.html( html );

      // Need to render the cron schedule's events.
      var $list = this.$( 'ul.cron-pixie-events' ).empty();

      var events = new TmsmAquosSpaBookingApp.EventsCollection( this.model.get( 'events' ) );

      events.each( function( model ) {
        var item = new TmsmAquosSpaBookingApp.EventsListItemView( { model: model } );
        $list.append( item.render().$el );
      }, this );

      return this;
    }
  } );

  /**
   * A single cron event's view.
   */
  TmsmAquosSpaBookingApp.EventsListItemView = Backbone.View.extend( {
    tagName: 'li',
    className: 'cron-pixie-event',
    template: _.template( $( '#cron-pixie-event-item-tmpl' ).html() ),

    initialize: function() {
      this.listenTo( this.model, 'change', this.render );
      this.listenTo( this.model, 'destroy', this.remove );
    },

    events: {
      'click .cron-pixie-event-run': 'runNow'
    },

    render: function() {
      var html = this.template( this.model.toJSON() );
      this.$el.html( html );

      return this;
    },

    runNow: function() {
      TmsmAquosSpaBookingApp.pauseTimer();

      // Only bother to run update if not due before next refresh.
      var seconds_due = this.model.get( 'seconds_due' );

      if ( seconds_due > TmsmAquosSpaBookingApp.timer_period ) {
        var timestamp = this.model.get( 'timestamp' ) - seconds_due;
        this.model.save(
          { timestamp: timestamp, seconds_due: 0 },
          {
            success: function( model, response, options ) {
              /*
               console.log( options );
               console.log( response );
               */
              TmsmAquosSpaBookingApp.runTimer();
            },
            error: function( model, response, options ) {
              /*
               console.log( options );
               console.log( response );
               */
              TmsmAquosSpaBookingApp.runTimer();
            }
          }
        );
      }
    }
  } );


  /**
   * Have Voucher
   */
  TmsmAquosSpaBookingApp.HavevoucherModel = Backbone.Model.extend( {
    defaults: {
      name: null,
      slug: null,
      value: null
    }
  } );

  TmsmAquosSpaBookingApp.HavevoucherCollection = Backbone.Collection.extend( {
    model: TmsmAquosSpaBookingApp.HavevoucherModel
  } );

  TmsmAquosSpaBookingApp.HavevoucherListView = Backbone.View.extend( {
    el: '#tmsm-aquos-spa-booking-voucher-container',
    selectedValue: null,

    initialize: function() {
      console.log('HavevoucherListView initialize');
      this.listenTo( this.collection, 'sync', this.render );
      this.render();
    },

    render: function() {

      console.log('HavevoucherListView render');

      var $list = $( 'ul#tmsm-aquos-spa-booking-voucher-list' ).empty();

      var havevoucher = new TmsmAquosSpaBookingApp.HavevoucherCollection( );
      havevoucher.add([TmsmAquosSpaBookingApp.data.havevoucher.yes, TmsmAquosSpaBookingApp.data.havevoucher.no]);
      havevoucher.each( function( model ) {
        var item = new TmsmAquosSpaBookingApp.HavevoucherListItemView( { model: model } );
        $list.append( item.render().$el );
      }, this );

      return this;
    },
    element: function (){
      return this.$el;
    },
    hide: function (){
      this.$el.hide();
    },
    show: function (){
      this.$el.show();
    },

  } );

  TmsmAquosSpaBookingApp.HavevoucherListItemView = Backbone.View.extend( {
    tagName: 'li',
    className: 'tmsm-aquos-spa-booking-havevoucher-item',
    template: wp.template( 'tmsm-aquos-spa-booking-havevoucher' ),


    initialize: function() {
      console.log('HavevoucherListItemView initialize');

      TmsmAquosSpaBookingApp.HavevoucherListView.selectedValue = null;

      this.listenTo( this.model, 'change', this.render );
      this.listenTo( this.model, 'destroy', this.remove );
    },

    events: {
      'click input': 'select'
    },

    render: function() {
      var html = this.template( this.model.toJSON() );
      this.$el.html( html );
      return this;
    },

    select: function(event){
      console.log('HavevoucherListItemView select');
      TmsmAquosSpaBookingApp.HavevoucherListView.selectedValue = $(event.target).val();

      TmsmAquosSpaBookingApp.animateTransition(TmsmAquosSpaBookingApp.productCategoriesList.element());

      //$('#tmsm-aquos-spa-booking-selected-hasvoucher').val($(event.target).val());
      //tmsmAquosSpaBookingLoadProductCategories();
    },

  } );

  /**
   * Product Category
   */
  TmsmAquosSpaBookingApp.ProductCategoryModel = BaseModel.extend( {
    action: 'tmsm-aquos-spa-booking-product-categories',
    defaults: {
      name: null,
      parent: null,
      count: null,
      term_id: null,
    }
  } );

  TmsmAquosSpaBookingApp.ProductCategoriesCollection = BaseCollection.extend( {
    action: 'tmsm-aquos-spa-booking-product-categories',
    model: TmsmAquosSpaBookingApp.ProductCategoryModel
  } );

  TmsmAquosSpaBookingApp.ProductCategoriesListView = Backbone.View.extend( {
    el: '#tmsm-aquos-spa-booking-categories-container',
    selectedValue: null,

    initialize: function() {
      this.listenTo( this.collection, 'sync', this.render );
    },

    events : {
      'change select' : 'change'
    },

    render: function() {
      var $list = this.$( 'select#tmsm-aquos-spa-booking-categories-select' ).empty();

      $list.append( '<option>'+TmsmAquosSpaBookingApp.strings.no_selection+'</option>' );
      this.collection.each( function( model ) {
        var item = new TmsmAquosSpaBookingApp.ProductCategoriesListItemView( { model: model } );
        $list.append( item.render().$el );
      }, this );
      if (typeof $list.selectpicker === "function") {
        $list.selectpicker('refresh');
      }

      return this;
    },

    change: function(event){
      console.log('ProductCategoriesListView change');
      TmsmAquosSpaBookingApp.ProductCategoriesListView.selectedValue = $(event.target).val();
      TmsmAquosSpaBookingApp.animateTransition(TmsmAquosSpaBookingApp.productsList.element());
    },

    element: function (){
      return this.$el;
    },
    hide: function (){
      this.$el.hide();
    },
    show: function (){
      this.$el.show();
    },
  } );

  TmsmAquosSpaBookingApp.ProductCategoriesListItemView = Backbone.View.extend( {
    tagName: 'option',
    attributes: function() {
      return {
        value: this.model.get('term_id'),
      };
    },
    className: 'tmsm-aquos-spa-booking-product-category-option',
    template: wp.template( 'tmsm-aquos-spa-booking-product-category' ),

    initialize: function() {
      this.listenTo( this.model, 'change', this.change );
      this.listenTo( this.model, 'destroy', this.remove );
    },

    render: function() {
      var html = this.template( this.model.toJSON() );
      this.$el.html( html );
      return this;
    },

    change: function() {
      console.log('ProductCategoriesListItemView change');
    }
  } );


  /**
   * Product
   */
  TmsmAquosSpaBookingApp.ProductModel = BaseModel.extend( {
    action: 'tmsm-aquos-spa-booking-products',
    defaults: {
      id: null,
      permalink: null,
      name: null,
      thumbnail: null,
      variable: null,
      is_voucher: null,
      price: null,
    }
  } );


  TmsmAquosSpaBookingApp.ProductsCollection = BaseCollection.extend( {
    action: 'tmsm-aquos-spa-booking-products',
    model: TmsmAquosSpaBookingApp.ProductModel
  } );


  TmsmAquosSpaBookingApp.ProductsListView = Backbone.View.extend( {
    el: '#tmsm-aquos-spa-booking-products-container',
    selectedValue: null,

    initialize: function() {
      this.listenTo( this.collection, 'sync', this.render );
    },

    events : {
      'change select' : 'change'
    },

    render: function() {
      var $list = this.$( 'select#tmsm-aquos-spa-booking-products-select' ).empty();

      $list.append( '<option>'+TmsmAquosSpaBookingApp.strings.no_selection+'</option>' );
      this.collection.each( function( model ) {
        var item = new TmsmAquosSpaBookingApp.ProductsListItemView( { model: model } );
        $list.append( item.render().$el );
      }, this );
      if (typeof $list.selectpicker === "function") {
        $list.selectpicker('refresh');
      }

      return this;
    },

    change: function(event){
      console.log('ProductListView change');
      TmsmAquosSpaBookingApp.ProductListView.selectedValue = $(event.target).val();
    },

    element: function (){
      return this.$el;
    },
    hide: function (){
      this.$el.hide();
    },
    show: function (){
      this.$el.show();
    },
  } );

  /**
   * A single cron schedule's view.
   */
  TmsmAquosSpaBookingApp.ProductsListItemView = Backbone.View.extend( {
    tagName: 'option',
    attributes: function() {
      return {
        value: this.model.get('id'),
        'data-sku': this.model.get('sku'),
        'data-variable': this.model.get('variable')
    };
    },
    className: 'tmsm-aquos-spa-booking-product-option',
    template: wp.template( 'tmsm-aquos-spa-booking-product' ),

    initialize: function() {
      this.listenTo( this.model, 'change', this.change );
      this.listenTo( this.model, 'destroy', this.remove );
    },

    render: function() {
      var html = this.template( this.model.toJSON() );
      this.$el.html( html );
      return this;
    },

  } );


  // Animate Display
  TmsmAquosSpaBookingApp.animateTransition = function(element){
    console.log('animateTransition ' + element.attr('id'));
    element.show();
    $('html, body').animate({
      scrollTop: element.offset().top
    }, 400);
  };


  /**
   * Display an interval as weeks, days, hours, minutes and seconds.
   *
   * @param seconds
   * @returns string
   */
  TmsmAquosSpaBookingApp.displayInterval = function( seconds ) {
    // Cron runs max every 60 seconds.
    if ( 0 > (seconds + 60) ) {
      return TmsmAquosSpaBookingApp.strings.passed;
    }

    // If due now or in next refresh period, show "now".
    if ( 0 > (seconds - TmsmAquosSpaBookingApp.timer_period) ) {
      return TmsmAquosSpaBookingApp.strings.now;
    }

    var intervals = [
      { name: TmsmAquosSpaBookingApp.strings.weeks_abrv, val: 604800000 },
      { name: TmsmAquosSpaBookingApp.strings.days_abrv, val: 86400000 },
      { name: TmsmAquosSpaBookingApp.strings.hours_abrv, val: 3600000 },
      { name: TmsmAquosSpaBookingApp.strings.minutes_abrv, val: 60000 },
      { name: TmsmAquosSpaBookingApp.strings.seconds_abrv, val: 1000 }
    ];

    // Convert everything to milliseconds so we can handle seconds in map.
    var milliseconds = seconds * 1000;
    var results = intervals.map( function( divider ) {
      var count = Math.floor( milliseconds / divider.val );

      if ( 0 < count ) {
        milliseconds = milliseconds % divider.val;
        return count + divider.name;
      } else {
        return '';
      }
    } );

    return results.join( ' ' ).trim();
  };


  /**
   * Retrieves new data from server.
   */
  TmsmAquosSpaBookingApp.refreshData = function() {
    TmsmAquosSpaBookingApp.schedules.fetch();
    TmsmAquosSpaBookingApp.productcategories.fetch();
    TmsmAquosSpaBookingApp.products.fetch();

    console.log('TmsmAquosSpaBookingApp.HavevoucherListView.selectedValue: '+TmsmAquosSpaBookingApp.HavevoucherListView.selectedValue);
    console.log('TmsmAquosSpaBookingApp.ProductCategoriesListView.selectedValue: '+TmsmAquosSpaBookingApp.ProductCategoriesListView.selectedValue);
  };

  /**
   * Start the recurring display updates if not already running.
   */
  TmsmAquosSpaBookingApp.runTimer = function() {
    if ( undefined == TmsmAquosSpaBookingApp.timer ) {

      TmsmAquosSpaBookingApp.timer = setInterval( TmsmAquosSpaBookingApp.refreshData, TmsmAquosSpaBookingApp.timer_period * 1000 );
    }
  };

  /**
   * Stop the recurring display updates if running.
   */
  TmsmAquosSpaBookingApp.pauseTimer = function() {
    if ( undefined !== TmsmAquosSpaBookingApp.timer ) {
      clearInterval( TmsmAquosSpaBookingApp.timer );
      delete TmsmAquosSpaBookingApp.timer;
    }
  };

  /**
   * Toggle recurring display updates on or off.
   */
  TmsmAquosSpaBookingApp.toggleTimer = function() {
    if ( undefined !== TmsmAquosSpaBookingApp.timer ) {
      TmsmAquosSpaBookingApp.pauseTimer();
    } else {
      TmsmAquosSpaBookingApp.runTimer();
    }
  };

  /**
   * Set initial data into view and start recurring display updates.
   */
  TmsmAquosSpaBookingApp.init = function() {


    TmsmAquosSpaBookingApp.havevoucherList = new TmsmAquosSpaBookingApp.HavevoucherListView( );

    TmsmAquosSpaBookingApp.products = new TmsmAquosSpaBookingApp.ProductsCollection();
    TmsmAquosSpaBookingApp.products.reset( TmsmAquosSpaBookingApp.data.products );
    TmsmAquosSpaBookingApp.productsList = new TmsmAquosSpaBookingApp.ProductsListView( { collection: TmsmAquosSpaBookingApp.products } );
    TmsmAquosSpaBookingApp.productsList.render();

    TmsmAquosSpaBookingApp.productcategories = new TmsmAquosSpaBookingApp.ProductCategoriesCollection();
    TmsmAquosSpaBookingApp.productcategories.reset( TmsmAquosSpaBookingApp.data.productcategories );
    TmsmAquosSpaBookingApp.productCategoriesList = new TmsmAquosSpaBookingApp.ProductCategoriesListView( { collection: TmsmAquosSpaBookingApp.productcategories } );
    TmsmAquosSpaBookingApp.productCategoriesList.render();

    TmsmAquosSpaBookingApp.schedules = new TmsmAquosSpaBookingApp.SchedulesCollection();
    TmsmAquosSpaBookingApp.schedules.reset( TmsmAquosSpaBookingApp.data.schedules );
    TmsmAquosSpaBookingApp.schedulesList = new TmsmAquosSpaBookingApp.SchedulesListView( { collection: TmsmAquosSpaBookingApp.schedules } );
    TmsmAquosSpaBookingApp.schedulesList.render();

    // Start a timer for updating the data.
    TmsmAquosSpaBookingApp.runTimer();
  };

  $( document ).ready( function() {
    TmsmAquosSpaBookingApp.init();
  } );


  /*
  ***************************************************************************
  ***************************************************************************
  ***************************************************************************

   */















































})(jQuery, TmsmAquosSpaBookingApp);
