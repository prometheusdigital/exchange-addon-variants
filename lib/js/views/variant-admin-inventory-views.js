var itExchangeVariants = itExchangeVariants || {};

(function($){

	'use strict';

	/**
	 * Core admin variants view
	*/
	itExchangeVariants.ProductInventoryVariantsMetaBoxView = Backbone.View.extend({

		// Metabox container
		el : function() {
			return $('.inner', '#it-exchange-product-inventory');
		},

		template: wp.template( 'it-exchange-product-inventory-variants-container' ),

		initialize : function() {

			// Init the productVariants collection property. (We'll fetch the variants in the render)
			this.inventoryCombos = new itExchangeVariants.ProductInventoryVariantCombos;
			this.inventoryCombos.url = ajaxurl + '?action=it-exchange-variants-json-api&endpoint=available-inventory-combos&product-id=72';

			// Init the productVariants collection property. (We'll fetch the variants in the render)
			this.missingCombos = new itExchangeVariants.ProductInventoryVariantMissingCombos;
			this.missingCombos.url = ajaxurl + '?action=it-exchange-variants-json-api&endpoint=missing-inventory-combos&product-id=72';

			// Register some listners to fire when productVariants collection is altered
			this.listenTo(this.inventoryCombos, 'reset', this.addAllCombos);
			this.listenTo(this.missingCombos, 'reset', this.addAllMissingCombos);
		},

		/**
		 * Event Handlers
		*/
		events : {
			'change #it-exchange-enable-product-variant-inventory': 'toggleEnableVariantInventory',
		},

		/**
		 * Render the subviews
		*/
		render : function(){
			// Render
			this.$el.append( this.template );
			this.inventoryCombos.fetch({reset:true});
			//console.log(this.inventoryCombos);
		},

		// Shows/Hids the product variants div
		toggleEnableVariantInventory : function() {
			$('.it-exchange-product-inventory-variants-inner').toggleClass('hide-if-js');
		},

		/**
		 * Reprint all variants (fires on resest of variants collections)
		*/
		addAllCombos : function() {
			this.$combos = $('.it-exchange-product-inventory-variants-table');
			this.$combos.html('');

			this.headerTemplate = wp.template('it-exchange-product-inventory-table-header');
			this.footerTemplate = wp.template('it-exchange-product-inventory-table-footer');
			var parents = this.inventoryCombos.first().get('variants');
			parents = _.keys(parents);
			this.$combos.append( this.headerTemplate( { variants: parents, version: this.inventoryCombos.first().get('version') } ) );

			if ( this.inventoryCombos.length ){
				//this.$variants.removeClass('no-variants');
				this.inventoryCombos.each( this.addOneCombo, this );
			}
			this.$combos.append( this.footerTemplate() );
			this.missingCombos.fetch({reset:true});
		},

		/**
		 * Receive an instance of a ProductVariant model, pass it to the ProductVariant View
		 * and grab its rendered $el and append it to the list of product variants
		*/
		addOneCombo: function ( combo ) {
			var view = new itExchangeVariants.ProductInventoryVariantComboView( { model: combo } );
			this.$combos.append( view.render().$el );
		},

		/**
		 * Reprint all variants (fires on resest of variants collections)
		*/
		addAllMissingCombos : function() {
			this.$missingCombos = $('.it-exchange-product-inventory-variants-missing-table');
			this.$missingCombos.html('');

			//this.headerTemplate = wp.template('it-exchange-product-inventory-table-header');
			//this.footerTemplate = wp.template('it-exchange-product-inventory-table-footer');
			var parents = this.missingCombos.first().get('variants');
			parents = _.keys(parents);
			//this.$missingCombos.append( this.headerTemplate( { variants: parents, version: this.inventoryCombos.first().get('version') } ) );

			if ( this.missingCombos.length ){
				//this.$variants.removeClass('no-variants');
				this.missingCombos.each( this.addOneMissingCombo, this );
			}
			//this.$combos.append( this.footerTemplate() );
		},

		/**
		 * Receive an instance of a ProductVariant model, pass it to the ProductVariant View
		 * and grab its rendered $el and append it to the list of product variants
		*/
		addOneMissingCombo: function ( combo ) {
			combo.set( 'existingVariants', this.inventoryCombos.models );
			var view = new itExchangeVariants.ProductInventoryVariantMissingComboView( { model: combo } );
			this.$missingCombos.append( view.render().$el );
		}

	});

	itExchangeVariants.ProductInventoryVariantComboView = Backbone.View.extend({

		tagName : 'div',

		className : 'inventory-variant-row',

		template : wp.template( 'it-exchange-product-inventory-variants-combo' ),

		/*
		initialize : function() {
			this.listenTo(this.model.values, 'reset', this.addAllVariantValues);
			this.listenTo(this.model.values, 'add', this.addOneVariantValue);
			this.listenTo(this.model.values, 'reset', this.makeVariantValuesSortable);
			this.listenTo(this.model.values, 'reset add remove', this.updateVariantValuesPreview);
			this.listenTo(this.model.values, 'reset add', this.initIThemesColorPicker);
		},
		*/

		render : function () {
			this.$el.html( this.template( this.model.toJSON() ) );
			this.$el
				.addClass('inventory-variant-row-' + this.model.get('hash'));

			return this;
		},

	});

	itExchangeVariants.ProductInventoryVariantMissingComboView = Backbone.View.extend({

		tagName : 'div',

		className : 'inventory-variant-missing-row',

		template : wp.template( 'it-exchange-product-inventory-variants-missing-combo' ),

		/*
		initialize : function() {
			this.listenTo(this.model.values, 'reset', this.addAllVariantValues);
			this.listenTo(this.model.values, 'add', this.addOneVariantValue);
			this.listenTo(this.model.values, 'reset', this.makeVariantValuesSortable);
			this.listenTo(this.model.values, 'reset add remove', this.updateVariantValuesPreview);
			this.listenTo(this.model.values, 'reset add', this.initIThemesColorPicker);
		},
		*/

		/**
		 * Event Handlers
		*/
		events : {
			'click .notification-transfer': 'toggleNotificationTransferDialogs',
			'click .notification-discard': 'removeMissingComboDiv',
			'click .transfer-cancel': 'toggleNotificationTransferDialogs',
			'click .transfer-save': 'applyMissingInventoryToExistingInventory',
		},

		render : function () {
			this.$el.html( this.template( this.model.toJSON() ) );
			this.$el
				.addClass('inventory-variant-missing-row-' + this.model.get('hash'));

			return this;
		},

		toggleNotificationTransferDialogs: function(event) {
			event.preventDefault();
			this.$('.missing-inventory-notification-dialog').toggleClass('hidden');
			this.$('.existing-inventory-variant-transfer-dialog').toggleClass('hidden');
		},

		applyMissingInventoryToExistingInventory: function(event) {
			event.preventDefault();
			var that = this;
			$('.existing-inventory-variant-checkbox:checked', this.$el).each(function() {
				var hash  = $(this).attr('data-hash');
				var value = this.value;

				// Remove all other instances of these checboxes
				$('.existing-inventory-variant-checkbox-' + hash + '-label').remove();

				// Apply value to any new combos they checked
				$('.inventory-variant-input-' + hash).val(value);
			});

			// Remove this missing div
			that.removeMissingComboDiv(event);

			// Check count of remaining missing combos. If it is zero, remove the disable property from the version and unlock the updates
			if ( ! $('.inventory-variant-missing-row').length ) {
				$('#it-exchange-inventory-variants-version').removeProp('disabled');
			}
		},

		removeMissingComboDiv: function(event) {
			event.preventDefault();
			this.$el.slideUp().remove();
		}
	});
}(jQuery));