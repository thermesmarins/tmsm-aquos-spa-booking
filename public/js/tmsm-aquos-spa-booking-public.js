var TmsmAquosSpaBookingApp = TmsmAquosSpaBookingApp || {};

(function ($, TmsmAquosSpaBooking) {
  'use strict';


  /**
   * A mixin for collections/models.
   * Based on http://taylorlovett.com/2014/09/28/syncing-backbone-models-and-collections-to-admin-ajax-php/
   */
  var AdminAjaxSyncableMixin = {
    url: TmsmAquosSpaBookingApp.ajaxurl,
    action: 'tmsm-aquos-spa-booking-product-categories',

    sync: function( method, object, options ) {
      //console.log('method:');
      //console.log(method);
      //console.log('object:');
      //console.log(object);
      //console.log('options:');
      //console.log(options);


      if ( typeof options.data === 'undefined' ) {
        options.data = {};
      }

      options.data.nonce = TmsmAquosSpaBookingApp.nonce; // From localized script.
      options.data.action_type = method;



      // If no action defined, set default.
      if ( undefined === options.data.action && undefined !== this.action ) {
        options.data.action = this.action;
      }

      //console.log('sync action: '+options.data.action);


      return Backbone.sync( method, object, options );

      // Reads work just fine.
      /*if ( 'read' === method ) {
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
      return Backbone.sync.call( this, 'create', object, options );*/
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



  /*
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


  TmsmAquosSpaBookingApp.EventsCollection = BaseCollection.extend( {
    action: 'cron_pixie_events',
    model: TmsmAquosSpaBookingApp.EventModel
  } );


  TmsmAquosSpaBookingApp.ScheduleModel = BaseModel.extend( {
    action: 'cron_pixie_schedules',
    defaults: {
      name: null,
      interval: null,
      display: null,
      events: new TmsmAquosSpaBookingApp.EventsCollection
    }
  } );

  TmsmAquosSpaBookingApp.SchedulesCollection = BaseCollection.extend( {
    action: 'cron_pixie_schedules',
    model: TmsmAquosSpaBookingApp.ScheduleModel
  } );

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


  } );

  */

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

    events: {
      'click [value=1]': 'value1'
    },

    value1: function(){
      console.log('value1');
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

      this.selectedValue = null;

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
      TmsmAquosSpaBookingApp.havevoucherList.selectedValue = $(event.target).val();

      TmsmAquosSpaBookingApp.animateTransition(TmsmAquosSpaBookingApp.productCategoriesList.element());

      $('#tmsm-aquos-spa-booking-cancel').show();
      //$('#tmsm-aquos-spa-booking-selected-hasvoucher').val($(event.target).val());
      //tmsmAquosSpaBookingLoadProductCategories();
    }

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
      //$( 'select#tmsm-aquos-spa-booking-categories-select' ).val('');
      var $list = this.$( 'select#tmsm-aquos-spa-booking-categories-select' ).empty().val('');
      if (typeof $list.selectpicker === "function") {
        console.log('ProductCategoriesListView selectpicker refresh 1');
        $list.selectpicker('refresh');
      }

      //$list.append( '<option>'+TmsmAquosSpaBookingApp.strings.no_selection+'</option>' );
      this.collection.each( function( model ) {
        var item = new TmsmAquosSpaBookingApp.ProductCategoriesListItemView( { model: model } );
        $list.append( item.render().$el );
      }, this );
      if (typeof $list.selectpicker === "function") {
        console.log('ProductCategoriesListView selectpicker refresh 2');
        $list.selectpicker('refresh');
      }

      return this;
    },

    change: function(event){
      console.log('ProductCategoriesListView change');
      TmsmAquosSpaBookingApp.productsList.loading();
      this.selectedValue = $(event.target).val();
      console.log('selectedValue: '+this.selectedValue);
      TmsmAquosSpaBookingApp.products.fetch({ data: {
          productcategory: this.selectedValue,
        } });
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
        value: this.model.get('term_id')
      };
    },
    className: 'tmsm-aquos-spa-booking-product-category-option',
    template: wp.template( 'tmsm-aquos-spa-booking-product-category' ),

    initialize: function() {
      this.listenTo( this.model, 'change', this.render );
      this.listenTo( this.model, 'destroy', this.remove );
    },

    render: function() {
      var html = this.template( this.model.toJSON() );
      this.$el.html( html );
      return this;
    },

    change: function() {
      //console.log('ProductCategoriesListItemView change');
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
    model: TmsmAquosSpaBookingApp.ProductModel,

  } );

  TmsmAquosSpaBookingApp.ProductsListView = Backbone.View.extend( {
    el: '#tmsm-aquos-spa-booking-products-container',
    selectedValue: null,
    selectElement: '#tmsm-aquos-spa-booking-products-select',
    loadingElement: '#tmsm-aquos-spa-booking-products-loading',
    buttonElement: '[data-id=tmsm-aquos-spa-booking-products-select]',

    initialize: function() {
      console.log('ProductsListView initialize');
      $( this.selectElement ).empty().val('');
      this.listenTo( this.collection, 'sync', this.render );
    },

    events : {
      'change select' : 'change'
    },
    loading: function(){
      console.log('ProductsListView loading');
      $( this.loadingElement ).show();
      $( this.buttonElement ).hide();
      $( this.selectElement ).hide();
    },
    loaded: function(){
      console.log('ProductsListView loaded');
      $( this.loadingElement ).hide();
      $( this.buttonElement ).show();
      $( this.selectElement ).show();
    },

    render: function() {
      var $list = this.$( this.selectElement ).empty().val('');

      $list.hide();
      if (typeof $list.selectpicker === 'function') {
        $list.selectpicker('refresh');
      }

      //$list.append( '<option>'+TmsmAquosSpaBookingApp.strings.no_selection+'</option>' );
      this.collection.each( function( model ) {
        model.attributes.is_voucher = TmsmAquosSpaBookingApp.havevoucherList.selectedValue;
        var item = new TmsmAquosSpaBookingApp.ProductsListItemView( { model: model } );
        $list.append( item.render().$el );
      }, this );
      if (typeof $list.selectpicker === 'function') {
        $list.selectpicker('refresh');
      }
      this.loaded();

      return this;
    },

    change: function(event){
      console.log('ProductListView change');
      this.selectedValue = $(event.target).val();
      TmsmAquosSpaBookingApp.productAttributesList.loading();

      console.log('selectedValue: '+this.selectedValue);


      TmsmAquosSpaBookingApp.productvariations.fetch({ data: {
          product: this.selectedValue,
        } });
      TmsmAquosSpaBookingApp.productattributes.fetch({ data: {
          product: this.selectedValue,
        } });

      TmsmAquosSpaBookingApp.productVariationsList.matchattributes();

      TmsmAquosSpaBookingApp.animateTransition(TmsmAquosSpaBookingApp.productAttributesList.element());
    },

    element: function (){
      return this.$el;
    },
    hide: function (){
      this.$el.hide();
    },
    show: function (){
      this.$el.show();
    }
  } );


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
      this.listenTo( this.model, 'change', this.render );
      this.listenTo( this.model, 'destroy', this.remove );
    },

    render: function() {
      var html = this.template( this.model.toJSON() );
      this.$el.html( html );
      return this;
    },

  } );




  /**
   * Term

  TmsmAquosSpaBookingApp.TermModel = BaseModel.extend( {
    action: 'tmsm-aquos-spa-booking-terms',
    defaults: {
      schedule: null,
      interval: null,
      hook: null,
      args: null,
      timestamp: null,
      seconds_due: null
    }
  } );

  TmsmAquosSpaBookingApp.TermsCollection = BaseCollection.extend( {
    action: 'tmsm-aquos-spa-booking-terms',
    model: TmsmAquosSpaBookingApp.TermModel
  } );

  TmsmAquosSpaBookingApp.TermsListItemView = Backbone.View.extend( {
    tagName: 'li',
    className: 'tmsm-aquos-spa-booking-term',
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


  } );
   */

  /**
   * Attribute
   */
  TmsmAquosSpaBookingApp.AttributeModel = BaseModel.extend( {
    action: 'tmsm-aquos-spa-booking-attributes',
    defaults: {
      label: null,
      //events: new TmsmAquosSpaBookingApp.TermsCollection
    }
  } );

  TmsmAquosSpaBookingApp.AttributesCollection = BaseCollection.extend( {
    action: 'tmsm-aquos-spa-booking-attributes',
    model: TmsmAquosSpaBookingApp.AttributeModel
  } );

  TmsmAquosSpaBookingApp.AttributesListView = Backbone.View.extend( {
    el: '#tmsm-aquos-spa-booking-attributes-container',
    selectedValue: null,
    loadingElement: '#tmsm-aquos-spa-booking-attributes-loading',
    resetElement: '#tmsm-aquos-spa-booking-attributes-reset',
    confirmElement: '#tmsm-aquos-spa-booking-attributes-confirm',
    listElement: '#tmsm-aquos-spa-booking-attributes-list',

    initialize: function() {
      console.log('AttributesListView initialize');
      this.listenTo( this.collection, 'sync', this.render );
    },

    events: {
      'click #tmsm-aquos-spa-booking-attributes-reset': 'reset',
      'click #tmsm-aquos-spa-booking-attributes-confirm': 'confirm',
    },

    loading: function(){
      console.log('AttributesListView loading');
      $( this.loadingElement ).show();
      $( this.listElement ).hide();
    },
    loaded: function(){
      console.log('AttributesListView loaded');
      $( this.loadingElement ).hide();
      $( this.listElement ).show();

      if ( typeof TmsmAquosSpaBookingApp.productVariationsList !== 'undefined' ) {
        TmsmAquosSpaBookingApp.productVariationsList.matchattributes();
      }
    },
    render: function() {
      var $list = this.$( this.listElement ).empty();

      this.collection.each( function( model ) {
        //console.log('attribute model:');
        //console.log(model);
        var item = new TmsmAquosSpaBookingApp.AttributesListItemView( { model: model } );
        $list.append( item.render().$el );
      }, this );

      this.loaded();

      return this;
    },
    reset: function (event){
      event.preventDefault();
      console.log('AttributesListView reset');
      this.$('input').attr('checked', false).removeAttr('checked').prop('checked', false);
      this.$('input.checked-default').attr('checked', true).prop('checked', true);
      TmsmAquosSpaBookingApp.productVariationsList.matchattributes();
    },

    confirm: function(event){

      TmsmAquosSpaBookingApp.animateTransition(TmsmAquosSpaBookingApp.dateList.element());
    },

    checkattributes: function(){
      console.log('AttributesListView checkattributes');

      var $attributeGroups     = $( '.tmsm-aquos-spa-booking-attribute' );
      var $attributeFields     = $( '.tmsm-aquos-spa-booking-term' );
      console.log('$attributeGroups:');
      console.log($attributeGroups);

      var data   = {};
      var count  = 0;
      var chosen = 0;
      // Get chosen attributes from form.
      $attributeGroups.each( function() {
        var $fields = $( this ).find( 'input[type=radio]' );

        var attribute_name = $fields.data( 'attribute_name' ) || $fields.attr( 'name' );
        var value          = $fields.filter(':checked').val() || '';

        if ( value.length > 0 ) {
          chosen ++;
        }

        count ++;
        data[ attribute_name ] = value;
      });
      var attributes = {
        'count'      : count,
        'chosenCount': chosen,
        'data'       : data
      };
      var currentAttributes = attributes.data;
      console.log('currentAttributes:');
      console.log(currentAttributes);

      // Loop through radio buttons and disable/enable based on selections.
      $attributeGroups.each( function( index, el ) {
        var current_attr      = $( el ),
          $fields           = current_attr.find( 'input[type=radio]' ),
          current_attr_name = $fields.data( 'attribute_name' ) || $fields.attr( 'name' );

        // The attribute of this radio button should not be taken into account when calculating its matching variations:
        // The constraints of this attribute are shaped by the values of the other attributes.
        var checkAttributes = $.extend( true, {}, currentAttributes );

        checkAttributes[ current_attr_name ] = '';

      });
      var checkAttributes = currentAttributes;



      console.log('checkAttributes:');
      console.log(checkAttributes);
      return checkAttributes;
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

  TmsmAquosSpaBookingApp.AttributesListItemView = Backbone.View.extend( {
    tagName: 'li',
    className: 'tmsm-aquos-spa-booking-attribute',
    template: wp.template( 'tmsm-aquos-spa-booking-product-attribute' ),

    initialize: function() {
      this.listenTo( this.model, 'change', this.render );
      this.listenTo( this.model, 'destroy', this.remove );
    },

    events: {
      'click input': 'select'
    },

    select: function(event){
      console.log('AttributesListItemView select');
      console.log($(event.target));
      console.log($(event.target).val());




      TmsmAquosSpaBookingApp.productVariationsList.matchattributes();



      /*
      //var variations = form.findMatchingVariations( form.variationData, checkAttributes );
      var variations = TmsmAquosSpaBookingApp.productvariations.models;
      //console.log('variations:');
      //console.log(variations);

      for (var i = 0; i < variations.length; i++) {
        console.log('variation.attributes.attributes for variation '+variations[i].attributes.id);
        var variation_attributes = JSON.parse(variations[i].attributes.attributes);
        console.log(variation_attributes);

        // compare variations_attributes with checkAttributes

        var match = true;
        for ( var attr_name in variation_attributes ) {
          if ( variation_attributes.hasOwnProperty( attr_name ) ) {
            var val1 = variation_attributes[ attr_name ];
            var val2 = checkAttributes[ attr_name ];
            if ( val1 !== undefined && val2 !== undefined && val1.length !== 0 && val2.length !== 0 && val1 !== val2 ) {
              match = false;
            }
          }
        }
        if (match === true){
          console.log('variation match:');
          console.log(variations[i]);


        }
      }*/


    },




    render: function() {
      var html = this.template( this.model.toJSON() );
      this.$el.html( html );

      /*
      var $list = this.$( 'ul.cron-pixie-events' ).empty();

      var events = new TmsmAquosSpaBookingApp.TermsCollection( this.model.get( 'events' ) );

      events.each( function( model ) {
        var item = new TmsmAquosSpaBookingApp.TermsListItemView( { model: model } );
        $list.append( item.render().$el );
      }, this );
      */
      //TmsmAquosSpaBookingApp.productVariationsList.matchattributes(this.checkattributes());

      return this;
    }
  } );



  /**
   * Product Variation
   */
  TmsmAquosSpaBookingApp.ProductVariationModel = BaseModel.extend( {
    action: 'tmsm-aquos-spa-booking-variations',
    defaults: {
      id: null,
      permalink: null,
      name: null,
      thumbnail: null,
      sku: null,
      attributes: null,
      price: null,
      is_voucher: null,
    }
  } );


  TmsmAquosSpaBookingApp.ProductVariationsCollection = BaseCollection.extend( {
    action: 'tmsm-aquos-spa-booking-variations',
    model: TmsmAquosSpaBookingApp.ProductVariationModel,

  } );

  TmsmAquosSpaBookingApp.ProductVariationsListView = Backbone.View.extend( {
    el: '#tmsm-aquos-spa-booking-attributes-container',
    selectedValue: null,
    selectElement: '#tmsm-aquos-spa-booking-variations-select',
    buttonElement: '[data-id=tmsm-aquos-spa-booking-variations-select]',

    initialize: function() {
      console.log('ProductVariationsListView initialize');
      $( this.selectElement ).empty().val('');
      this.listenTo( this.collection, 'sync', this.render );
    },

    events : {
      'change select' : 'change'
    },

    render: function() {
      var $list = this.$( this.selectElement ).empty().val('');

      $list.hide();
      if (typeof $list.selectpicker === 'function') {
        $list.selectpicker('refresh');
      }

      this.collection.each( function( model ) {
        model.attributes.is_voucher = TmsmAquosSpaBookingApp.havevoucherList.selectedValue;
        var item = new TmsmAquosSpaBookingApp.ProductVariationsListItemView( { model: model } );
        $list.append( item.render().$el );
      }, this );

      if (typeof $list.selectpicker === 'function') {
        $list.selectpicker('refresh');
      }

      TmsmAquosSpaBookingApp.productVariationsList.matchattributes();

      return this;
    },

    change: function(event){
      console.log('ProductVariationListView change');
      this.selectedValue = $(event.target).val();
      console.log('selectedValue: '+this.selectedValue);

      //TmsmAquosSpaBookingApp.productattributes.fetch({ data: {          product: this.selectedValue,        } });
      //TmsmAquosSpaBookingApp.animateTransition(TmsmAquosSpaBookingApp.productAttributesList.element());
    },

    matchattributes: function(){
      console.log('ProductVariationsListView matchattributes');

      var checkAttributes = TmsmAquosSpaBookingApp.productAttributesList.checkattributes()

      //var variations = form.findMatchingVariations( form.variationData, checkAttributes );
      var variations = this.collection.models;
      //console.log('variations:');
      //console.log(variations);

      for (var i = 0; i < variations.length; i++) {
        console.log('variation.attributes.attributes for variation '+variations[i].attributes.id);
        var variation_attributes = JSON.parse(variations[i].attributes.attributes);
        console.log(variation_attributes);

        // compare variations_attributes with checkAttributes

        var match = true;
        for ( var attr_name in variation_attributes ) {
          if ( variation_attributes.hasOwnProperty( attr_name ) ) {
            var val1 = variation_attributes[ attr_name ];
            var val2 = checkAttributes[ attr_name ];
            if ( val1 !== undefined && val2 !== undefined && val1.length !== 0 && val2.length !== 0 && val1 !== val2 ) {
              match = false;
            }
          }
        }
        if (match === true){
          console.log('variation match:');
          console.log(variations[i]);


          $( this.selectElement ).selectpicker('val', variations[i].id);
        }
      }
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


  TmsmAquosSpaBookingApp.ProductVariationsListItemView = Backbone.View.extend( {
    tagName: 'option',
    attributes: function() {
      return {
        value: this.model.get('id'),
        'data-sku': this.model.get('sku'),
        'data-attributes': this.model.get('attributes'),
      };
    },
    className: 'tmsm-aquos-spa-booking-variation-option',
    template: wp.template( 'tmsm-aquos-spa-booking-variation' ),

    initialize: function() {
      this.listenTo( this.model, 'change', this.render );
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


  /*
   * Date
   */
  TmsmAquosSpaBookingApp.DateListView = Backbone.View.extend( {
    el: '#tmsm-aquos-spa-booking-date-container',
    selectedValue: null,
    datePicker: null,

    initialize: function() {
      console.log('DateListView initialize');

      this.render();
    },
    selectDate: function(date){
        console.log('DateListView selectDate');
        this.selectedValue = date.format('yyyy-mm-dd');
        console.log(this.selectedValue);
    },


    events : {
      //'change select' : 'change'
    },

    render: function() {

      // Datepicker
      this.datePicker = $('#tmsm-aquos-spa-booking-datepicker').datepicker({
        language: tmsm_aquos_spa_booking_params.locale,
        format: 'yyyy-mm-dd',
        startDate: tmsm_aquos_spa_booking_params.options.startdate,
        endDate: tmsm_aquos_spa_booking_params.options.enddate,
      }).on('changeDate', this.selectDate);

      return this;
    },

    change: function(event){
      console.log('ProductListView change');
      this.selectedValue = $(event.target).val();
      //TmsmAquosSpaBookingApp.timesList.loading();

      console.log('selectedValue: '+this.selectedValue);

      TmsmAquosSpaBookingApp.animateTransition(TmsmAquosSpaBookingApp.timesList.element());
      //TmsmAquosSpaBookingApp.animateTransition(TmsmAquosSpaBookingApp.timesList.element());
    },

    element: function (){
      return this.$el;
    },
    hide: function (){
      this.$el.hide();
    },
    show: function (){
      this.$el.show();
    }
  } );

  /**
   * Time
   */
  TmsmAquosSpaBookingApp.TimeModel = BaseModel.extend( {
    action: 'tmsm-aquos-spa-booking-times',
    defaults: {
      hour: null,
      minute: null,
      priority: null,
      hourminute: null,
    }
  } );


  TmsmAquosSpaBookingApp.TimesCollection = BaseCollection.extend( {
    action: 'tmsm-aquos-spa-booking-times',
    model: TmsmAquosSpaBookingApp.TimeModel,

  } );

  TmsmAquosSpaBookingApp.TimesListView = Backbone.View.extend( {
    el: '#tmsm-aquos-spa-booking-times-container',
    selectedValue: null,
    listElement: '#tmsm-aquos-spa-booking-times-list',
    loadingElement: '#tmsm-aquos-spa-booking-times-loading',
    errorElement: '#tmsm-aquos-spa-booking-times-loading',

    initialize: function() {
      console.log('TimesListView initialize');
      this.listenTo( this.collection, 'sync', this.render );
    },

    events : {
      'click .tmsm-aquos-spa-booking-time-button' : 'selectTime'
    },
    loading: function(){
      console.log('TimesListView loading');
      $( this.errorElement ).hide();
      $( this.loadingElement ).show();
      $( this.listElement ).hide();
    },
    loaded: function(){
      console.log('TimesListView loaded');
      $( this.loadingElement ).hide();
      $( this.listElement ).hide();
    },

    render: function() {
      var $list = this.$( this.selectElement ).empty().val('');

      $list.hide();

      this.collection.each( function( model ) {
        var item = new TmsmAquosSpaBookingApp.TimesListItemView( { model: model } );
        $list.append( item.render().$el );
      }, this );

      this.loaded();

      return this;
    },

    selectTime: function(event){
      console.log('TimeListView selectTime');


    },

    element: function (){
      return this.$el;
    },
    hide: function (){
      this.$el.hide();
    },
    show: function (){
      this.$el.show();
    }
  } );


  TmsmAquosSpaBookingApp.TimesListItemView = Backbone.View.extend( {
    tagName: 'li',
    className: 'tmsm-aquos-spa-booking-time-item',
    template: wp.template( 'tmsm-aquos-spa-booking-time' ),

    initialize: function() {
      this.listenTo( this.model, 'change', this.render );
      this.listenTo( this.model, 'destroy', this.remove );
    },

    render: function() {
      var html = this.template( this.model.toJSON() );
      this.$el.html( html );
      return this;
    },

  } );


  /**
   * Set initial data into view and start recurring display updates.
   */
  TmsmAquosSpaBookingApp.init = function() {


    TmsmAquosSpaBookingApp.dateList = new TmsmAquosSpaBookingApp.DateListView( );

    TmsmAquosSpaBookingApp.havevoucherList = new TmsmAquosSpaBookingApp.HavevoucherListView( );

    TmsmAquosSpaBookingApp.productattributes = new TmsmAquosSpaBookingApp.AttributesCollection();
    TmsmAquosSpaBookingApp.productattributes.reset( TmsmAquosSpaBookingApp.data.productattributes );
    TmsmAquosSpaBookingApp.productAttributesList = new TmsmAquosSpaBookingApp.AttributesListView( { collection: TmsmAquosSpaBookingApp.productattributes } );
    TmsmAquosSpaBookingApp.productAttributesList.render();


    TmsmAquosSpaBookingApp.times = new TmsmAquosSpaBookingApp.TimesCollection();
    TmsmAquosSpaBookingApp.times.reset( TmsmAquosSpaBookingApp.data.times );
    TmsmAquosSpaBookingApp.timesList = new TmsmAquosSpaBookingApp.TimesListView( { collection: TmsmAquosSpaBookingApp.times } );
    TmsmAquosSpaBookingApp.timesList.render();

    TmsmAquosSpaBookingApp.products = new TmsmAquosSpaBookingApp.ProductsCollection();
    TmsmAquosSpaBookingApp.products.reset( TmsmAquosSpaBookingApp.data.products );
    TmsmAquosSpaBookingApp.productsList = new TmsmAquosSpaBookingApp.ProductsListView( { collection: TmsmAquosSpaBookingApp.products } );
    TmsmAquosSpaBookingApp.productsList.render();

    TmsmAquosSpaBookingApp.productvariations = new TmsmAquosSpaBookingApp.ProductVariationsCollection();
    TmsmAquosSpaBookingApp.productvariations.reset( TmsmAquosSpaBookingApp.data.productvariations );
    TmsmAquosSpaBookingApp.productVariationsList = new TmsmAquosSpaBookingApp.ProductVariationsListView( { collection: TmsmAquosSpaBookingApp.productvariations } );
    TmsmAquosSpaBookingApp.productVariationsList.render();

    TmsmAquosSpaBookingApp.productcategories = new TmsmAquosSpaBookingApp.ProductCategoriesCollection();
    TmsmAquosSpaBookingApp.productcategories.reset( TmsmAquosSpaBookingApp.data.productcategories );
    TmsmAquosSpaBookingApp.productCategoriesList = new TmsmAquosSpaBookingApp.ProductCategoriesListView( { collection: TmsmAquosSpaBookingApp.productcategories } );
    TmsmAquosSpaBookingApp.productCategoriesList.render();

    /*TmsmAquosSpaBookingApp.schedules = new TmsmAquosSpaBookingApp.SchedulesCollection();
    TmsmAquosSpaBookingApp.schedules.reset( TmsmAquosSpaBookingApp.data.schedules );
    TmsmAquosSpaBookingApp.schedulesList = new TmsmAquosSpaBookingApp.SchedulesListView( { collection: TmsmAquosSpaBookingApp.schedules } );
    TmsmAquosSpaBookingApp.schedulesList.render();*/

  };

  $( document ).ready( function() {
    TmsmAquosSpaBookingApp.init();
  } );




})(jQuery, TmsmAquosSpaBookingApp);
