var TmsmAquosSpaBookingApp = TmsmAquosSpaBookingApp || {};

(function ($, TmsmAquosSpaBooking) {
  'use strict';


  /**
   * A mixin for collections/models.
   * @see http://taylorlovett.com/2014/09/28/syncing-backbone-models-and-collections-to-admin-ajax-php/
   * @see https://deliciousbrains.com/building-reactive-wordpress-plugins-part-1-backbone-js/
   * @see https://www.synbioz.com/blog/tech/debuter-avec-backbonejs
   */
  var AdminAjaxSyncableMixin = {
    url: TmsmAquosSpaBookingApp.ajaxurl,
    action: 'tmsm-aquos-spa-booking-product-categories',

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

      //console.log('sync action: '+options.data.action);
      //console.log('sync options: ');
      //console.log(options);


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
      //console.log('HavevoucherListView initialize');
      this.listenTo( this.collection, 'sync', this.render );
      this.render();
    },

    render: function() {

      //console.log('HavevoucherListView render');

      var $list = $( 'ul#tmsm-aquos-spa-booking-voucher-list' ).empty();

      var havevoucher = new TmsmAquosSpaBookingApp.HavevoucherCollection( );
      havevoucher.add([TmsmAquosSpaBookingApp.data.havevoucher.yes, TmsmAquosSpaBookingApp.data.havevoucher.no]);
      havevoucher.each( function( model ) {
        var item = new TmsmAquosSpaBookingApp.HavevoucherListItemView( { model: model } );
        $list.append( item.render().$el );
      }, this );

      return this;
    },

    reset: function (event){
      //console.log('AttributesListView reset');
      this.$('input').attr('checked', false).removeAttr('checked').prop('checked', false);
      this.selectedValue = null;
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
      //console.log('HavevoucherListItemView initialize');

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
      //console.log('HavevoucherListItemView select');
      TmsmAquosSpaBookingApp.havevoucherList.selectedValue = $(event.target).val();

      TmsmAquosSpaBookingApp.productAttributesList.reset();
      //TmsmAquosSpaBookingApp.productsList.reset();
      TmsmAquosSpaBookingApp.dateList.reset();
      TmsmAquosSpaBookingApp.timesList.reset();
      TmsmAquosSpaBookingApp.selectedData.set('is_voucher', TmsmAquosSpaBookingApp.havevoucherList.selectedValue);

      //TmsmAquosSpaBookingApp.animateTransition(TmsmAquosSpaBookingApp.productCategoriesList.element());
      TmsmAquosSpaBookingApp.animateTransition(TmsmAquosSpaBookingApp.productsList.element());

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
    selectElement: '#tmsm-aquos-spa-booking-categories-select',

    initialize: function() {
      this.hide();
      this.listenTo( this.collection, 'sync', this.render );
    },

    events : {
      'change select' : 'change'
    },

    render: function() {
      //$( 'select#tmsm-aquos-spa-booking-categories-select' ).val('');
      var $list = this.$( this.selectElement ).empty().val('');
      if (typeof $list.selectpicker === "function") {
        //console.log('ProductCategoriesListView selectpicker refresh 1');
        $list.selectpicker('refresh');
      }

      //$list.append( '<option>'+TmsmAquosSpaBookingApp.strings.no_selection+'</option>' );
      this.collection.each( function( model ) {
        var item = new TmsmAquosSpaBookingApp.ProductCategoriesListItemView( { model: model } );
        $list.append( item.render().$el );
      }, this );
      if (typeof $list.selectpicker === "function") {
        //console.log('ProductCategoriesListView selectpicker refresh 2');
        $list.selectpicker('refresh');
      }

      return this;
    },

    change: function(event){
      //console.log('ProductCategoriesListView change');
      TmsmAquosSpaBookingApp.productsList.loading();
      this.selectedValue = $(event.target).val();
      //console.log('selectedValue: '+this.selectedValue);
      TmsmAquosSpaBookingApp.productAttributesList.reset();
      TmsmAquosSpaBookingApp.dateList.reset();
      TmsmAquosSpaBookingApp.timesList.reset();

      TmsmAquosSpaBookingApp.products.fetch({ data: {
          productcategory: this.selectedValue,
        } });

      TmsmAquosSpaBookingApp.selectedData.set('productcategory', this.selectedValue);

      TmsmAquosSpaBookingApp.animateTransition(TmsmAquosSpaBookingApp.productsList.element());


    },

    reset: function (){
      this.$( this.selectElement ).val('').selectpicker('refresh');
      this.selectedValue = null;
      TmsmAquosSpaBookingApp.selectedData.set('productcategory', null);
      this.hide();
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
      attributes_otherthan_voucher: null,
      voucher_variation_id: null,
      choices: null,
      is_voucher: null,
      price: null,
      category: null,
    }
  } );


  TmsmAquosSpaBookingApp.ProductsCollection = BaseCollection.extend( {
    action: 'tmsm-aquos-spa-booking-products',
    model: TmsmAquosSpaBookingApp.ProductModel,

  } );

  TmsmAquosSpaBookingApp.ProductsListView = Backbone.View.extend( {
    el: '#tmsm-aquos-spa-booking-products-container',
    selectedValue: null,
    selectedIsVariable: null,
    selectedHasChoicesVariable: null,
    selectElement: '#tmsm-aquos-spa-booking-products-select',
    loadingElement: '#tmsm-aquos-spa-booking-products-loading',
    buttonElement: '[data-id=tmsm-aquos-spa-booking-products-select]',

    initialize: function() {
      this.hide();
      console.log('ProductsListView initialize');
      ///$( this.selectElement ).empty().val('');
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

      console.log('ProductsListView collection:');
      console.log(this.collection);

      var $categories = [];
      //$list.append( '<option>'+TmsmAquosSpaBookingApp.strings.no_selection+'</option>' );
      this.collection.each( function( model ) {
          if(model.attributes.category && !$categories[model.attributes.category]){
            $categories[model.attributes.category] = $('<optgroup>', {
              label: model.attributes.category,
            });
            $list.append($categories[model.attributes.category]);
          }

        model.attributes.is_voucher = TmsmAquosSpaBookingApp.havevoucherList.selectedValue;
        var item = new TmsmAquosSpaBookingApp.ProductsListItemView( { model: model } );
        //if(model.attributes.category){
        //  $list.append( "<optgroup>"+model.attributes.category+"</optgroup>" );
        //}
        console.log('ProductsListView append');
        console.log(item);

        //$list.append( item.render().$el );
        if($categories[model.attributes.category]){
          $categories[model.attributes.category].append( item.render().$el );
        }


      }, this );

      console.log('ProductsListView append list');
      console.log($categories);
      $.each($categories, function( index, value ) {
        $list.append( $categories[index] );
      });

      if (typeof $list.selectpicker === 'function') {
        console.log('ProductsListView selectpicker refresh');
        $list.selectpicker('refresh');
      }
      this.loaded();

      return this;
    },

    change: function(event){
      console.log('ProductListView change');
      this.selectedValue = $(event.target).val();
      TmsmAquosSpaBookingApp.productAttributesList.reset();
      TmsmAquosSpaBookingApp.productAttributesList.loading();

      console.log('selectedValue: ' + this.selectedValue);
      console.log($(event.target));
      console.log('selectedTarget children selected: ');
      console.log($(event.target).children("option:selected"));

      this.selectedIsVariable = $(event.target).children("option:selected").attr('data-variable');
      this.selectedHasAttributesOtherthanVoucher = $(event.target).children("option:selected").attr('data-attributes_otherthan_voucher');
      this.voucher_variation_id = $(event.target).children("option:selected").attr('data-voucher_variation_id');
      console.log('selectedIsVariable: '+this.selectedIsVariable);
      console.log('selectedHasAttributesOtherthanVoucher: '+this.selectedHasAttributesOtherthanVoucher);

      var choices = JSON.parse($(event.target).children("option:selected").attr('data-choices'));
      this.selectedHasChoices = (choices.length !== 0);
      console.warn('selectedChoices: ');
      //console.log(choices);
      //console.log(choices.length);
      //console.log('selectedHasChoices: '+this.selectedHasChoices);

      TmsmAquosSpaBookingApp.selectedData.set('product', this.selectedValue);
      TmsmAquosSpaBookingApp.dateList.reset();
      TmsmAquosSpaBookingApp.timesList.reset();
      TmsmAquosSpaBookingApp.choicesList.reset();
      TmsmAquosSpaBookingApp.productAttributesList.reset();
      TmsmAquosSpaBookingApp.productVariationsList.reset();



      // Product has choices
      if(this.selectedHasChoices){
        console.warn('CASE 1');
        // Go to choices
        TmsmAquosSpaBookingApp.data.choices = choices;
        TmsmAquosSpaBookingApp.choices.reset( TmsmAquosSpaBookingApp.data.choices );
        TmsmAquosSpaBookingApp.choicesList.render();

        TmsmAquosSpaBookingApp.animateTransition(TmsmAquosSpaBookingApp.choicesList.element());
      }
      else{

        // Product has attributes (other than voucher)
        if(this.selectedIsVariable && this.selectedHasAttributesOtherthanVoucher){
          console.warn('CASE 2');
          // Go to variations
          TmsmAquosSpaBookingApp.productvariations.fetch({ data: {
              product: this.selectedValue,
            } });
          TmsmAquosSpaBookingApp.productattributes.fetch({ data: {
              product: this.selectedValue,
            } });

          TmsmAquosSpaBookingApp.productVariationsList.matchattributes();
          //TmsmAquosSpaBookingApp.productAttributesList.render();

          TmsmAquosSpaBookingApp.animateTransition(TmsmAquosSpaBookingApp.productAttributesList.element());
        }
        else{
          //console.log('product_id: ' + this.selectedValue);
          //console.log('voucher_variation_id: ' + this.voucher_variation_id);
          console.warn('CASE 3');
          // Set product data
          TmsmAquosSpaBookingApp.selectedData.set('product', this.selectedValue);
          //TmsmAquosSpaBookingApp.selectedData.set('productvariation', this.selectedValue);
          TmsmAquosSpaBookingApp.selectedData.set('productvariation', this.voucher_variation_id);
          TmsmAquosSpaBookingApp.animateTransition(TmsmAquosSpaBookingApp.dateList.element());

          if(this.voucher_variation_id !== null){

            TmsmAquosSpaBookingApp.selectedData.set('product', null);
            TmsmAquosSpaBookingApp.selectedData.set('product', this.voucher_variation_id);
            TmsmAquosSpaBookingApp.selectedData.set('productvariation', null);
            TmsmAquosSpaBookingApp.selectedData.unset('productvariation');

          }

        }
      }
      if(TmsmAquosSpaBookingApp.calendar.dateselection == 'weekdays'){
        TmsmAquosSpaBookingApp.weekdaysList.render();
      }

    },

    reset: function (){
      this.$( this.selectElement ).empty().val('');
      this.selectedValue = null;
      TmsmAquosSpaBookingApp.selectedData.set('product', null);
      this.hide();
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
        'data-category': this.model.get('category'),
        'data-variable': this.model.get('variable'),
        'data-attributes_otherthan_voucher': this.model.get('attributes_otherthan_voucher'),
        'data-voucher_variation_id': this.model.get('voucher_variation_id'),
        'data-choices': this.model.get('choices')
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
   * Attribute
   */
  TmsmAquosSpaBookingApp.AttributeModel = BaseModel.extend( {
    action: 'tmsm-aquos-spa-booking-attributes',
    defaults: {
      label: null,
      description: null,
      is_voucher: null,
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
    cancelElement: '#tmsm-aquos-spa-booking-attributes-cancel',
    confirmElement: '#tmsm-aquos-spa-booking-attributes-confirm',
    emptyElement: '#tmsm-aquos-spa-booking-attributes-empty',
    listElement: '#tmsm-aquos-spa-booking-attributes-list',

    initialize: function() {
      //console.log('AttributesListView initialize');
      this.hide();
      this.listenTo( this.collection, 'sync', this.render );
    },

    events: {
      'click #tmsm-aquos-spa-booking-attributes-cancel': 'cancel',
      'click #tmsm-aquos-spa-booking-attributes-confirm': 'confirm',
    },

    loading: function(){
      //console.log('AttributesListView loading');
      $( this.emptyElement ).hide();
      $( this.loadingElement ).show();
      $( this.cancelElement ).hide();
      $( this.confirmElement ).hide();
      $( this.listElement ).hide();
    },
    loaded: function(){

      $( this.loadingElement ).hide();
      $( this.cancelElement ).show();
      $( this.confirmElement ).show();
      $( this.listElement ).show();


      if ( typeof TmsmAquosSpaBookingApp.productVariationsList !== 'undefined' ) {
        if(TmsmAquosSpaBookingApp.productVariationsList.matchattributes()){
          //console.log('AttributesListView loaded');
          $( this.emptyElement ).hide();


        }
        else{
          //console.log('AttributesListView error');
          $( this.emptyElement ).show();
          $( this.cancelElement ).hide();
          $( this.confirmElement ).hide();
          $( this.listElement ).hide();

        }
      }
    },
    render: function() {
      //console.log('AttributesListView render');
      $( this.loadingElement ).show();
      var $list = this.$( this.listElement ).empty();

      this.collection.each( function( model ) {
        model.attributes.is_voucher = TmsmAquosSpaBookingApp.havevoucherList.selectedValue;
        //console.log('attribute model:');
        //console.log(model);
        var item = new TmsmAquosSpaBookingApp.AttributesListItemView( { model: model } );
        $list.append( item.render().$el );
      }, this );

      this.loaded();

      return this;
    },
    cancel: function (event){
      event.preventDefault();
      //console.log('AttributesListView cancel');

      TmsmAquosSpaBookingApp.courseParticipants = 0;
      TmsmAquosSpaBookingApp.selectedData.set('course_participants', TmsmAquosSpaBookingApp.courseParticipants);

      this.$('input').attr('checked', false).removeAttr('checked').prop('checked', false);
      this.$('input.checked-default').attr('checked', true).prop('checked', true);
      TmsmAquosSpaBookingApp.productVariationsList.matchattributes();
      this.selectedValue = null;
    },
    reset: function (event){
      //console.log('AttributesListView reset');

      TmsmAquosSpaBookingApp.courseParticipants = 0;

      this.$('input').attr('checked', false).removeAttr('checked').prop('checked', false);
      this.$('input.checked-default').attr('checked', true).prop('checked', true);
      TmsmAquosSpaBookingApp.productVariationsList.matchattributes();
      this.selectedValue = null;
      this.hide();
    },

    confirm: function(event){

      //console.log('AttributesListView confirm');
      
      TmsmAquosSpaBookingApp.timesList.reset();

      TmsmAquosSpaBookingApp.animateTransition(TmsmAquosSpaBookingApp.dateList.element());

      if(TmsmAquosSpaBookingApp.calendar.dateselection == 'weekdays'){
        TmsmAquosSpaBookingApp.weekdaysList.render();
      }

    },

    checkattributes: function(){
      //console.log('AttributesListView checkattributes');

      var $attributeGroups     = $( '.tmsm-aquos-spa-booking-attribute' );
      var $attributeFields     = $( '.tmsm-aquos-spa-booking-term' );
      //console.log('$attributeGroups:');
      //console.log($attributeGroups);

      var data   = {};
      var count  = 0;
      var chosen = 0;

      // Does product have attributes?
      if($attributeGroups.length == 0){
        //console.log('AttributesListView no attributes');
      }
      // Does product have variations?
      if(TmsmAquosSpaBookingApp.productVariationsList.variationsCount == 0){
        //console.log('productVariationsList no variations');
        return false;
      }
      else{
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
        //console.log('currentAttributes:');
        //console.log(currentAttributes);

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

        //console.log('checkAttributes:');
        //console.log(checkAttributes);
        return currentAttributes;
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
      //console.log('AttributesListItemView select');
      //console.log($(event.target));
      //console.log($(event.target).val());

      TmsmAquosSpaBookingApp.courseParticipants = 0;


      if($.inArray( $(event.target).val(), [ "avec-parcours-aquatonic", "pa1", "avec-parcours-aquatonic-1-personne" ] )){
        TmsmAquosSpaBookingApp.courseParticipants = 1;
      }

      if($.inArray( $(event.target).val(), [ "pa2", "avec-parcours-aquatonic-2-personnes" ] )){
        TmsmAquosSpaBookingApp.courseParticipants = 2;
      }

      console.info(' TmsmAquosSpaBookingApp.courseParticipants: ' +  TmsmAquosSpaBookingApp.courseParticipants);

      TmsmAquosSpaBookingApp.selectedData.set('course_participants', TmsmAquosSpaBookingApp.courseParticipants);

      TmsmAquosSpaBookingApp.productVariationsList.matchattributes();
      //TmsmAquosSpaBookingApp.productAttributesList.loading();
      //TmsmAquosSpaBookingApp.productAttributesList.render();

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
    variationsCount: 0,
    buttonElement: '[data-id=tmsm-aquos-spa-booking-variations-select]',

    initialize: function() {
      //console.log('ProductVariationsListView initialize');
      $( this.emptyElement ).hide();
      $( this.selectElement ).empty().val('');
      this.listenTo( this.collection, 'sync', this.render );
    },

    events : {
      'change select' : 'change',
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

      this.variationsCount = this.collection.length;
      /*if(this.collection.length > 0){
        this.$(TmsmAquosSpaBookingApp.productAttributesList.confirmElement).show();
      }
      else{
        this.$( this.emptyElement ).show();
        this.$(TmsmAquosSpaBookingApp.productAttributesList.confirmElement).hide();
        this.$(TmsmAquosSpaBookingApp.productAttributesList.cancelElement).hide();
        this.$(TmsmAquosSpaBookingApp.productAttributesList.selectElement).hide();
      }*/

      //TmsmAquosSpaBookingApp.productVariationsList.matchattributes();
      TmsmAquosSpaBookingApp.productAttributesList.loading();
      TmsmAquosSpaBookingApp.productAttributesList.render();

      return this;
    },

    change: function(event){
      //console.log('ProductVariationListView change');
      this.selectedValue = $(event.target).val();
      //console.log('selectedValue: '+this.selectedValue);

      TmsmAquosSpaBookingApp.dateList.reset();
      TmsmAquosSpaBookingApp.timesList.reset();
      TmsmAquosSpaBookingApp.selectedData.set('productvariation', this.selectedValue);
      //TmsmAquosSpaBookingApp.productattributes.fetch({ data: {          product: this.selectedValue,        } });
      //TmsmAquosSpaBookingApp.animateTransition(TmsmAquosSpaBookingApp.productAttributesList.element());
    },
    reset: function (){
      this.$( this.selectElement ).empty().val('');
      this.selectedValue = null;
      TmsmAquosSpaBookingApp.selectedData.set('productvariation', null);
      TmsmAquosSpaBookingApp.selectedData.unset('productvariation');
      this.hide();
    },
    matchattributes: function(){
      //console.log('ProductVariationsListView matchattributes');

      var checkAttributes = TmsmAquosSpaBookingApp.productAttributesList.checkattributes();

      if(checkAttributes){
        //var variations = form.findMatchingVariations( form.variationData, checkAttributes );
        var variations = this.collection.models;
        //console.log('variations:');
        //console.log(variations);

        for (var i = 0; i < variations.length; i++) {
          //console.log('variation.attributes.attributes for variation '+variations[i].attributes.id);
          var variation_attributes = JSON.parse(variations[i].attributes.attributes);
          //console.log(variation_attributes);

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
            //console.log('variation match:');
            //console.log(variations[i]);


            $( this.selectElement ).selectpicker('val', variations[i].id).trigger('change');
          }
        }
        return variations;
      }
      else{
        return false;
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





  /**
   * Choice
   */
  TmsmAquosSpaBookingApp.ChoiceModel = BaseModel.extend( {
    action: 'tmsm-aquos-spa-booking-choices',
    defaults: {
      aquos_id: null,
      name: null,
    }
  } );


  TmsmAquosSpaBookingApp.ChoicesCollection = BaseCollection.extend( {
    action: 'tmsm-aquos-spa-booking-choices',
    model: TmsmAquosSpaBookingApp.ChoiceModel,

  } );

  TmsmAquosSpaBookingApp.ChoicesListView = Backbone.View.extend( {
    el: '#tmsm-aquos-spa-booking-choices-container',
    selectedValue: null,
    selectedIsVariable: null,
    selectedHasAttributesOtherthanVoucher: null,
    selectElement: '#tmsm-aquos-spa-booking-choices-select',
    loadingElement: '#tmsm-aquos-spa-booking-choices-loading',
    buttonElement: '[data-id=tmsm-aquos-spa-booking-choices-select]',

    initialize: function() {
      this.hide();
      //console.log('ChoicesListView initialize');
      $( this.selectElement ).empty().val('');
      this.listenTo( this.collection, 'sync', this.render );
    },

    events : {
      'change select' : 'change'
    },
    loading: function(){
      //console.log('ChoicesListView loading');
      $( this.loadingElement ).show();
      $( this.buttonElement ).hide();
      $( this.selectElement ).hide();
    },
    loaded: function(){
      //console.log('ChoicesListView loaded');
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

      //console.log('ChoicesListView collection:');
      //console.log(this.collection);

      this.collection.each( function( model ) {
        var item = new TmsmAquosSpaBookingApp.ChoicesListItemView( { model: model } );
        $list.append( item.render().$el );
      }, this );
      if (typeof $list.selectpicker === 'function') {
        $list.selectpicker('refresh');
      }
      this.loaded();

      return this;
    },

    change: function(event){
      //console.log('ChoicesListView change');
      this.selectedValue = $(event.target).val();

      //console.log('selectedValue: '+this.selectedValue);

      TmsmAquosSpaBookingApp.selectedData.set('choice', this.selectedValue);
      TmsmAquosSpaBookingApp.animateTransition(TmsmAquosSpaBookingApp.dateList.element());


      if(TmsmAquosSpaBookingApp.calendar.dateselection == 'weekdays'){
        TmsmAquosSpaBookingApp.weekdaysList.render();
      }
    },

    reset: function (){
      this.$( this.selectElement ).empty().val('');
      this.selectedValue = null;
      TmsmAquosSpaBookingApp.selectedData.set('choice', null);
      this.hide();
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


  TmsmAquosSpaBookingApp.ChoicesListItemView = Backbone.View.extend( {
    tagName: 'option',
    attributes: function() {
      return {
        value: this.model.get('aquos_id'),
        'data-aquosid': this.model.get('aquos_id'),
      };
    },
    className: 'tmsm-aquos-spa-booking-choice-option',
    template: wp.template( 'tmsm-aquos-spa-booking-choice' ),

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
    //console.log('animateTransition ' + element.attr('id'));
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
      //console.log('DateListView initialize');
      this.hide();
      this.render();
    },
    selectDate: function (date) {
      console.warn('DateListView selectDate');
      this.selectedValue = date.format('yyyy-mm-dd');

      var options = {weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'};
      $('#tmsm-aquos-spa-booking-date-display').html(date.date.toLocaleDateString(TmsmAquosSpaBookingApp.locale, options));


      TmsmAquosSpaBookingApp.timesList.loading();

      //console.log('TmsmAquosSpaBookingApp.productsList.selectedValue: '+TmsmAquosSpaBookingApp.productsList.selectedValue);
      //console.log('TmsmAquosSpaBookingApp.productVariationsList.selectedValue: '+TmsmAquosSpaBookingApp.productVariationsList.selectedValue);
      //console.log('TmsmAquosSpaBookingApp.choicesList.selectedValue: '+TmsmAquosSpaBookingApp.choicesList.selectedValue);

      TmsmAquosSpaBookingApp.times.fetch({
        data: {
          productcategory: TmsmAquosSpaBookingApp.productCategoriesList.selectedValue,
          product: TmsmAquosSpaBookingApp.productsList.selectedValue,
          productvariation: TmsmAquosSpaBookingApp.productVariationsList.selectedValue,
          choice: TmsmAquosSpaBookingApp.choicesList.selectedValue,
          date: this.selectedValue
        }
      });

      //console.log('selectedValue: ' + this.selectedValue);

      TmsmAquosSpaBookingApp.selectedData.set('date', this.selectedValue);

      TmsmAquosSpaBookingApp.animateTransition(TmsmAquosSpaBookingApp.timesList.element());
    },


    events : {
      //'change select' : 'change'
    },

    render: function() {

      // Datepicker
      this.datePicker = $('#tmsm-aquos-spa-booking-datepicker').datepicker({
        language: TmsmAquosSpaBookingApp.locale,
        format: 'yyyy-mm-dd',
        startDate: TmsmAquosSpaBookingApp.calendar.startdate,
        endDate: TmsmAquosSpaBookingApp.calendar.enddate,
      }).on('changeDate', this.selectDate);

      return this;
    },

    reset: function (){
      this.selectedValue = null;
      TmsmAquosSpaBookingApp.selectedData.set('date', null);
      this.hide();
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
      date: null,
      hour: null,
      minute: null,
      priority: null,
      hourminutes: null,
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
    errorElement: '#tmsm-aquos-spa-booking-times-error',
    anotherDateElement: '#tmsm-aquos-spa-booking-times-anotherdate',
    selectButtons: '.tmsm-aquos-spa-booking-time-button',
    cancelButtons: '.tmsm-aquos-spa-booking-time-change-label',

    initialize: function() {

      //console.log('TimesListView initialize');
      this.hide();
      this.listenTo( this.collection, 'sync', this.render );
    },

    events : {
      'click .tmsm-aquos-spa-booking-time-button' : 'selectTime',
      'click .tmsm-aquos-spa-booking-time-change-label' : 'cancelTime',
      'click #tmsm-aquos-spa-booking-times-anotherdate' : 'changeDate',
      'click .previous': 'previous',
      'click .next': 'next',
    },

    loading: function(){
      //console.log('TimesListView loading');
      $( this.errorElement ).hide();
      $( this.loadingElement ).show();
      $( this.listElement ).hide();
    },
    loaded: function(){
      //console.log('TimesListView loaded');
      $( this.loadingElement ).hide();
      $( this.listElement ).show();
    },

    render: function() {
      //console.log('TimesListView render');
      var $list = this.$( this.listElement ).empty().val('');

      $list.hide();

      //console.log('TimesListView collection:');
      //console.log(this.collection);
      //console.log('TimesListView collection length: ' + this.collection.length);

      var i = 0;
      this.collection.each( function( model ) {
        i++;
        if(i===1){
          $( '.tmsm-aquos-spa-booking-weekday-times[data-date="'+model.attributes.date+'"]').empty();
        }
        var item = new TmsmAquosSpaBookingApp.TimesListItemView( { model: model } );
        if ($('.tmsm-aquos-spa-booking-weekday-times[data-date="' + model.attributes.date + '"]').length > 0) {
          //$( '.tmsm-aquos-spa-booking-weekday-times[data-date="' + model.attributes.date + '"]').append(item.render().$el.context.outerHTML);
          $( '.tmsm-aquos-spa-booking-weekday-times[data-date="' + model.attributes.date + '"]').append(item.render().el.outerHTML);
        }
        else{
          //console.log('tmsm-aquos-spa-booking-weekday-times not added for '+model.attributes.date);
        }
        //
        //$( '.tmsm-aquos-spa-booking-weekday-times[data-date=\''+model.attributes.date+'\']').append(item.render().$el);

        $list.append( item.render().$el );
      }, this );

      this.loaded();

      if(this.collection.length === 0){
        $( this.errorElement ).show();
      }
      else{
        $( this.errorElement ).hide();
      }

      return this;
    },

    selectTime: function(event){
      event.preventDefault();
      //console.log('TimeListView selectTime');
      this.selectedValue = $(event.target).data('hourminutes');
      //console.log('TimeListView selectedValue: '+ this.selectedValue);
      $( this.selectButtons ).hide().removeClass('disabled').removeClass('selected').addClass('not-selected');
      $(event.target).show().addClass('selected').removeClass('not-selected').find('.tmsm-aquos-spa-booking-time').addClass('disabled');

      TmsmAquosSpaBookingApp.selectedData.set('hourminutes', this.selectedValue);
    },

    cancelTime: function(event){
      event.preventDefault();
      $( this.selectButtons ).show().removeClass('disabled').removeClass('selected').addClass('not-selected');
      this.selectedValue = null;

      TmsmAquosSpaBookingApp.selectedData.set('hourminutes', null);
    },

    changeDate: function(event){
      event.preventDefault();
      TmsmAquosSpaBookingApp.dateList.reset();
      TmsmAquosSpaBookingApp.timesList.reset();
      TmsmAquosSpaBookingApp.animateTransition(TmsmAquosSpaBookingApp.dateList.element());
    },

    reset: function (){
      this.selectedValue = null;
      TmsmAquosSpaBookingApp.selectedData.set('hourminutes', null);
      this.hide();
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
   * WeekDay
   */
  TmsmAquosSpaBookingApp.WeekDayModel = BaseModel.extend( {
    action: 'tmsm-aquos-spa-booking-weekday',
    defaults: {
      date_label: null,
      date_computed: null,
    }
  } );

  TmsmAquosSpaBookingApp.WeekDayCollection = BaseCollection.extend( {
    action: 'tmsm-aquos-spa-booking-weekday',
    model: TmsmAquosSpaBookingApp.WeekDayModel,

  } );

  TmsmAquosSpaBookingApp.WeekDayListView = Backbone.View.extend( {
    el: '#tmsm-aquos-spa-booking-date-container',
    listElement: '#tmsm-aquos-spa-booking-weekdays-list',
    selectButtons: '.tmsm-aquos-spa-booking-time-button',
    addAppointmentButton: '#tmsm-aquos-spa-booking-confirm',
    daysPage: 1,

    templateHelpers: {
      moment: moment // <-- this is the reference to the moment in your view
    },

    initialize: function() {

      //console.log('WeekDayListView initialize');

      moment.locale(TmsmAquosSpaBookingApp.locale);
      //console.log("moment", moment().format());
      //console.log("moment locale: "+ moment.locale());

      //console.log("moment fromnow: "+ moment().fromNow());
      this.listenTo( this.collection, 'sync', this.render );
    },

    events : {
      'click .tmsm-aquos-spa-booking-time-button' : 'selectTime',
      'click #tmsm-aquos-spa-booking-weekdays-previous': 'previous',
      'click #tmsm-aquos-spa-booking-weekdays-next': 'next',
    },

    previous: function(event){
      //console.log('WeekDayListView previous');

      event.preventDefault();
      this.daysPage = this.daysPage - 1;
      this.render();
    },

    next: function(event){
      //console.log('WeekDayListView next');

      event.preventDefault();
      this.daysPage = this.daysPage + 1;

      this.render();
    },

    render: function() {
      console.log('WeekDayListView render');

      var tmpDaysPage = this.daysPage;

      var $list = this.$( this.listElement ).empty().val('');

      this.collection.reset();

      $('#tmsm-aquos-spa-booking-weekdays-previous').attr('disabled', true);
      $('#tmsm-aquos-spa-booking-weekdays-next').attr('disabled', true);


      var i = 0;

      if(TmsmAquosSpaBookingApp.productsList.selectedValue){
        var loaded_days = 1;
        for (i = (parseInt(TmsmAquosSpaBookingApp.calendar.daysrangefrom)+(this.daysPage-1) * 7); i < (parseInt(TmsmAquosSpaBookingApp.calendar.daysrangefrom)+7+(this.daysPage-1) * 7); i++) {

          this.collection.push( {
            date_label: moment().add(i, 'days').format('dddd Do MMMM'),
            date_label_secondline: moment().add(i, 'days').format('MMMM'),
            date_label_firstline: moment().add(i, 'days').format('dddd Do'),
            date_computed: moment().add(i, 'days').format('YYYY-MM-DD')
          });
        }

        console.log('WeekDayListView collection:');
        console.log(this.collection);

        console.log('WeekDayListView collection length: ' + this.collection.length);

        this.collection.each( function( model ) {


          //console.log('WeekDayListView each');
          //console.log(model);
          var item = new TmsmAquosSpaBookingApp.WeekDayListItemView( { model: model } );
          $list.append( item.render().$el );

          //console.log('WeekDayListView fetch:');
          TmsmAquosSpaBookingApp.times.fetch({
            data: {
              productcategory: TmsmAquosSpaBookingApp.productCategoriesList.selectedValue,
              product: TmsmAquosSpaBookingApp.productsList.selectedValue,
              productvariation: TmsmAquosSpaBookingApp.productVariationsList.selectedValue,
              choice: TmsmAquosSpaBookingApp.choicesList.selectedValue,
              date: model.attributes.date_computed
            },
            complete: function(xhr) {
              //console.log('complete fetch');

              loaded_days++;
              if(loaded_days == 7){
                //console.warn('ALl days loaded ****************');
                //console.warn('tmpDaysPage: ' + tmpDaysPage);

                $('#tmsm-aquos-spa-booking-weekdays-previous').attr('disabled', (tmpDaysPage === 1) );

                $('#tmsm-aquos-spa-booking-weekdays-next').attr('disabled', ( (TmsmAquosSpaBookingApp.data.daysrangeto / 7) < tmpDaysPage ) );

              }
            }

          });

        }, this );

      }

      return this;
    },
    selectTime: function(event){
      event.preventDefault();
      console.log('WeekDayListView selectTime');
      this.selectedValue = $(event.target).data('hourminutes');
      var date = $(event.target).data('date');
      //console.log('WeekDayListView selectedValue: '+ this.selectedValue);
      $( this.selectButtons ).removeClass('btn-primary').removeClass('disabled').removeClass('selected').addClass('not-selected');
      $(event.target).addClass('btn-primary').addClass('disabled').removeClass('not-selected');

      //console.warn($(this.addAppointmentButton));
      TmsmAquosSpaBookingApp.selectedData.set('hourminutes', this.selectedValue);
      TmsmAquosSpaBookingApp.selectedData.set('date', date);

      // Has participants, show course time selection
      if(TmsmAquosSpaBookingApp.selectedData.get('course_participants') > 0 && TmsmAquosSpaBookingApp.courseplugin){
        //console.info('Has participants, show course time selection');
        TmsmAquosSpaBookingApp.animateTransition(TmsmAquosSpaBookingApp.courseTimesList.element());
      }
      else{
        TmsmAquosSpaBookingApp.animateTransition($(this.addAppointmentButton));

      }




    },

  } );

  TmsmAquosSpaBookingApp.WeekDayListItemView = Backbone.View.extend( {
    tagName: 'li',
    className: 'tmsm-aquos-spa-booking-weekday-item',
    template: wp.template( 'tmsm-aquos-spa-booking-weekday' ),

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
   * Course Time
   */
  TmsmAquosSpaBookingApp.CourseTimeModel = BaseModel.extend( {
    action: 'tmsm-aquos-spa-booking-course-times',
    defaults: {
      date: null,
      hour: null,
      minute: null,
      priority: null,
      hourminutes: null,
    }
  } );


  TmsmAquosSpaBookingApp.CourseTimesCollection = BaseCollection.extend( {
    action: 'tmsm-aquos-spa-booking-course-times',
    model: TmsmAquosSpaBookingApp.CourseTimeModel,

  } );

  TmsmAquosSpaBookingApp.CourseTimesListView = Backbone.View.extend( {
    el: '#tmsm-aquos-spa-booking-course-times-container',
    selectedValue: null,
    listElement: '#tmsm-aquos-spa-booking-course-times-list',
    loadingElement: '#tmsm-aquos-spa-booking-course-times-loading',
    errorElement: '#tmsm-aquos-spa-booking-course-times-error',
    anotherDateElement: '#tmsm-aquos-spa-booking-course-times-anotherdate',
    selectButtons: '.tmsm-aquos-spa-booking-course-time-button',
    cancelButtons: '.tmsm-aquos-spa-booking-course-time-change-label',

    initialize: function() {
      //console.log('CourseTimesListView initialize');
      this.hide();
      this.listenTo( this.collection, 'sync', this.render );
    },

    events : {
      'click .tmsm-aquos-spa-booking-course-time-button' : 'selectTime',
      'click .tmsm-aquos-spa-booking-course-time-change-label' : 'cancelTime',
      //'click #tmsm-aquos-spa-booking-course-times-anotherdate' : 'changeDate',
      'click .previous': 'previous',
      'click .next': 'next',
    },

    loading: function(){
      //console.log('CourseTimesListView loading');
      $( this.errorElement ).hide();
      $( this.loadingElement ).show();
      $( this.listElement ).hide();
    },
    loaded: function(){
      //console.log('CourseTimesListView loaded');
      $( this.loadingElement ).hide();
      $( this.listElement ).show();
    },

    render: function() {
      //console.log('CourseTimesListView render');
      var $list = this.$( this.listElement ).empty().val('');

      $list.hide();

      //console.log('CourseTimesListView collection:');
      //console.log(this.collection);
      //console.log('CourseTimesListView collection length: ' + this.collection.length);

      var i = 0;
      this.collection.each( function( model ) {
        i++;
        if(i===1){
          $( '.tmsm-aquos-spa-booking-weekday-times[data-date="'+model.attributes.date+'"]').empty();
        }
        var item = new TmsmAquosSpaBookingApp.TimesListItemView( { model: model } );
        //if ($('.tmsm-aquos-spa-booking-weekday-course-times[data-date="' + model.attributes.date + '"]').length > 0) {
          //$( '.tmsm-aquos-spa-booking-weekday-course-times[data-date="' + model.attributes.date + '"]').append(item.render().$el.context.outerHTML);
        //}
        //else{
          //console.log('tmsm-aquos-spa-booking-weekday-times not added for '+model.attributes.date);
        //}
        //
        //$( '.tmsm-aquos-spa-booking-weekday-times[data-date=\''+model.attributes.date+'\']').append(item.render().$el);

        $list.append( item.render().$el );
      }, this );

      this.loaded();

      if(this.collection.length === 0){
        $( this.errorElement ).show();
      }
      else{
        $( this.errorElement ).hide();
      }

      return this;
    },

    selectTime: function(event){
      event.preventDefault();
      //console.log('CourseTimeListView selectTime');
      this.selectedValue = $(event.target).data('hourminutes');
      //console.log('CourseTimeListView selectedValue: '+ this.selectedValue);
      $( this.selectButtons ).hide().removeClass('disabled').removeClass('selected').addClass('not-selected');
      $(event.target).show().addClass('selected').removeClass('not-selected').find('.tmsm-aquos-spa-booking-course-time').addClass('disabled');

      TmsmAquosSpaBookingApp.selectedData.set('hourminutes', this.selectedValue);
    },

    cancelTime: function(event){
      event.preventDefault();
      $( this.selectButtons ).show().removeClass('disabled').removeClass('selected').addClass('not-selected');
      this.selectedValue = null;

      TmsmAquosSpaBookingApp.selectedData.set('hourminutes', null);
    },

    changeDate: function(event){
      event.preventDefault();
      //TmsmAquosSpaBookingApp.dateList.reset();
      TmsmAquosSpaBookingApp.courseTimesList.reset();
      //TmsmAquosSpaBookingApp.animateTransition(TmsmAquosSpaBookingApp.dateList.element());
    },

    reset: function (){
      this.selectedValue = null;
      TmsmAquosSpaBookingApp.selectedData.set('hourminutes', null);
      this.hide();
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


  TmsmAquosSpaBookingApp.CourseTimesListItemView = Backbone.View.extend( {
    tagName: 'li',
    className: 'tmsm-aquos-spa-booking-course-time-item',
    template: wp.template( 'tmsm-aquos-spa-booking-course-time' ),

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
   * Selected Data
   */
  TmsmAquosSpaBookingApp.SelectedDataModel = Backbone.Model.extend( {
    defaults: {
      productcategory: null,
      product: null,
      productvariation: null,
      date: null,
      hourminutes: null,
      course_participants: null,
      course_hourminutes: null,
      is_voucher: null
    },

  } );


  TmsmAquosSpaBookingApp.SelectedDataView = Backbone.View.extend( {
    el: '#tmsm-aquos-spa-booking-confirm-container',
    cancelButton: '#tmsm-aquos-spa-booking-cancel',
    confirmButton: '#tmsm-aquos-spa-booking-confirm',
    errorElement: '#tmsm-aquos-spa-booking-confirm-error',

    initialize: function() {
      //console.log('SelectedDataView initialize');
      this.hideError();
      this.hideConfirm();
      this.hideCancel();
      this.listenTo(this.model, 'change', this.change);
    },

    events: {
      'click #tmsm-aquos-spa-booking-cancel': 'cancel',
      'click #tmsm-aquos-spa-booking-confirm': 'confirm'
    },

    cancel: function(event){
      event.preventDefault();

      this.hideError();

      //console.log('SelectedDataView cancel');

      TmsmAquosSpaBookingApp.selectedData.clear().set({});

      TmsmAquosSpaBookingApp.animateTransition(TmsmAquosSpaBookingApp.havevoucherList.element());

      TmsmAquosSpaBookingApp.havevoucherList.reset();
      TmsmAquosSpaBookingApp.productCategoriesList.reset();
      TmsmAquosSpaBookingApp.productsList.reset();
      TmsmAquosSpaBookingApp.productVariationsList.reset();
      TmsmAquosSpaBookingApp.dateList.reset();
      TmsmAquosSpaBookingApp.timesList.reset();
    },

    confirm: function(event) {
      event.preventDefault();

      //console.log('SelectedDataView confirm');
      $(this.errorElement).empty();
      this.showLoading();
      var container = this;

      wp.ajax.send('tmsm-aquos-spa-booking-addtocart', {
        success: function(data){
          //console.log('wp.ajax.send success');
          //console.log(data);
          if(data.redirect){
            //console.log('redirect!');
            //window.location = data.redirect;
          }
          else{
            console.log('no redirect...');
            console.log(data.redirect);
          }
        },
        error: function(data){
          /*console.log('wp.ajax.send error');
          console.log(data);
          container.hideLoading();
          console.log('wp.ajax.send error');
          console.log(data);*/
          if(data.errors){
            container.showError();
            $(container.errorElement).html( data.errors );
          }

        },
        data: {
          nonce: TmsmAquosSpaBookingApp.nonce,
          selecteddata: TmsmAquosSpaBookingApp.selectedData.attributes,
        }
      });



    },

    change: function (){
      //console.log('SelectedDataView change');
      //console.log(this.model);

      if(this.canConfirm(this.model.attributes)){
        this.showConfirm();
      }
      else{
        this.hideConfirm();
      }

      if(this.canCancel(this.model.attributes)){
        this.showCancel();
      }
      else{
        this.hideCancel();
      }


    },

    showLoading: function(){
      //console.log('SelectedDataView showLoading');
      $( this.confirmButton ).prop('disabled', true).addClass('btn-disabled');
    },
    hideLoading: function(){
      //console.log('SelectedDataView hideLoading');
      $( this.confirmButton ).prop('disabled', false).removeClass('btn-disabled');
    },
    showError: function(){
      $( this.errorElement ).show();
    },
    hideError: function(){
      $( this.errorElement ).hide();
    },
    showConfirm: function(){
      //console.log('SelectedDataView showConfirm');
      $( this.confirmButton ).show();
    },
    hideConfirm: function(){
      $( this.confirmButton ).hide();
    },

    showCancel: function(){
      //console.log('SelectedDataView showCancel');
      $( this.cancelButton ).show();
    },
    hideCancel: function(){
      $( this.cancelButton ).hide();
    },

    canConfirm: function(attributes) {
      return (attributes.productcategory != null && attributes.product != null && attributes.date != null && attributes.hourminutes != null );
    },

    canCancel: function(attributes) {
      return (attributes.is_voucher != null && attributes.productcategory != null );
    },

    render: function() {

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
    }

  } );

  /**
   * Set initial data into view and start recurring display updates.
   */
  TmsmAquosSpaBookingApp.init = function() {

    TmsmAquosSpaBookingApp.courseParticipants = 0;

    TmsmAquosSpaBookingApp.havevoucherList = new TmsmAquosSpaBookingApp.HavevoucherListView( );

    TmsmAquosSpaBookingApp.productcategories = new TmsmAquosSpaBookingApp.ProductCategoriesCollection();
    TmsmAquosSpaBookingApp.productcategories.reset( TmsmAquosSpaBookingApp.data.productcategories );
    TmsmAquosSpaBookingApp.productCategoriesList = new TmsmAquosSpaBookingApp.ProductCategoriesListView( { collection: TmsmAquosSpaBookingApp.productcategories } );
    TmsmAquosSpaBookingApp.productCategoriesList.render();

    TmsmAquosSpaBookingApp.productattributes = new TmsmAquosSpaBookingApp.AttributesCollection();
    TmsmAquosSpaBookingApp.productattributes.reset( TmsmAquosSpaBookingApp.data.productattributes );
    TmsmAquosSpaBookingApp.productAttributesList = new TmsmAquosSpaBookingApp.AttributesListView( { collection: TmsmAquosSpaBookingApp.productattributes } );
    TmsmAquosSpaBookingApp.productAttributesList.render();

    TmsmAquosSpaBookingApp.products = new TmsmAquosSpaBookingApp.ProductsCollection();
    TmsmAquosSpaBookingApp.products.reset( TmsmAquosSpaBookingApp.data.products );
    TmsmAquosSpaBookingApp.productsList = new TmsmAquosSpaBookingApp.ProductsListView( { collection: TmsmAquosSpaBookingApp.products } );
    TmsmAquosSpaBookingApp.productsList.render();

    TmsmAquosSpaBookingApp.productvariations = new TmsmAquosSpaBookingApp.ProductVariationsCollection();
    TmsmAquosSpaBookingApp.productvariations.reset( TmsmAquosSpaBookingApp.data.productvariations );
    TmsmAquosSpaBookingApp.productVariationsList = new TmsmAquosSpaBookingApp.ProductVariationsListView( { collection: TmsmAquosSpaBookingApp.productvariations } );
    TmsmAquosSpaBookingApp.productVariationsList.render();

    TmsmAquosSpaBookingApp.choices = new TmsmAquosSpaBookingApp.ChoicesCollection();
    TmsmAquosSpaBookingApp.choices.reset( TmsmAquosSpaBookingApp.data.choices );
    TmsmAquosSpaBookingApp.choicesList = new TmsmAquosSpaBookingApp.ChoicesListView( { collection: TmsmAquosSpaBookingApp.choices } );
    TmsmAquosSpaBookingApp.choicesList.render();

    TmsmAquosSpaBookingApp.dateList = new TmsmAquosSpaBookingApp.DateListView( );

    TmsmAquosSpaBookingApp.times = new TmsmAquosSpaBookingApp.TimesCollection();
    TmsmAquosSpaBookingApp.times.reset( TmsmAquosSpaBookingApp.data.times );
    TmsmAquosSpaBookingApp.timesList = new TmsmAquosSpaBookingApp.TimesListView( { collection: TmsmAquosSpaBookingApp.times } );
    TmsmAquosSpaBookingApp.timesList.render();

    TmsmAquosSpaBookingApp.weekdays = new TmsmAquosSpaBookingApp.WeekDayCollection();
    TmsmAquosSpaBookingApp.weekdays.reset( TmsmAquosSpaBookingApp.data.times );
    TmsmAquosSpaBookingApp.weekdaysList = new TmsmAquosSpaBookingApp.WeekDayListView( { collection: TmsmAquosSpaBookingApp.weekdays } );
    TmsmAquosSpaBookingApp.weekdaysList.render();


    TmsmAquosSpaBookingApp.coursetimes = new TmsmAquosSpaBookingApp.CourseTimesCollection();
    TmsmAquosSpaBookingApp.coursetimes.reset( TmsmAquosSpaBookingApp.data.coursetimes );
    TmsmAquosSpaBookingApp.courseTimesList = new TmsmAquosSpaBookingApp.CourseTimesListView( { collection: TmsmAquosSpaBookingApp.coursetimes } );
    TmsmAquosSpaBookingApp.courseTimesList.render();


    TmsmAquosSpaBookingApp.selectedData = new TmsmAquosSpaBookingApp.SelectedDataModel();
    TmsmAquosSpaBookingApp.selectedDataList = new TmsmAquosSpaBookingApp.SelectedDataView( { model: TmsmAquosSpaBookingApp.selectedData } );

    /*TmsmAquosSpaBookingApp.schedules = new TmsmAquosSpaBookingApp.SchedulesCollection();
    TmsmAquosSpaBookingApp.schedules.reset( TmsmAquosSpaBookingApp.data.schedules );
    TmsmAquosSpaBookingApp.schedulesList = new TmsmAquosSpaBookingApp.SchedulesListView( { collection: TmsmAquosSpaBookingApp.schedules } );
    TmsmAquosSpaBookingApp.schedulesList.render();*/

  };

  $( document ).ready( function() {

    if($('#tmsm-aquos-spa-booking-form').length > 0){
      TmsmAquosSpaBookingApp.init();
    }

  } );




})(jQuery, TmsmAquosSpaBookingApp);

// OptinMonster compatibility
document.addEventListener('om.Scripts.init', function(evt) {
  window._omapp.scripts.moment.status = 'loaded';
  window._omapp.scripts.moment.object = window.moment ? window.moment : null;
  window._omapp.scripts.momentTz.status = 'loaded';
  window._omapp.scripts.momentTz.object = window.moment ? window.moment.tz : null;
});
