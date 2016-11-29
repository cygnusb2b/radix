import Ember from 'ember';

const { Component, inject: { service }, run, computed, set } = Ember;

export default Component.extend({
    /**
     * Services.
     */
    query   : service('model-query'),
    utility : service('model-utility'),

    /**
     * Public options
     */
    model        : null,
    relField     : null,
    delay        : 300,
    placeholder  : 'Begin typing to select...',
    displayField : 'name',

    /**
     * Computed properties
     */
    disabled : computed('_initialized', function() {
        return (!this.get('_initialized'));
    }),

    multiple : computed('relMeta', function() {
        return 'hasMany' === this.get('relMeta.kind');
    }),

    relMeta  : computed('model', 'relField', function() {
        return this.get('utility').extractRelationshipMeta(this.get('model'), this.get('relField'));
    }),

    /**
     * Private options.
     */
    _loading     : false,
    _initialized : false,

    _options           : computed.uniqBy('_optionsMerged', 'id'),
    _optionsMerged     : computed.union('_optionsFromModel', '_optionsFromSearch'),
    _optionsFromModel  : [],
    _optionsFromSearch : [],

    _selection : [],
    _textInput : null,

    didInsertElement: function() {
        this._getRelatedPromise().then((items) => {
            // Once the related model promise is resovles, add each item to the option and selection stack.
            // The options must be set before the selection, otherwise selectize will not see it as valid and it will not appear.

            if (this.get('multiple')) {
                items.forEach((item) => {
                    this.get('_optionsFromModel').pushObject(item);
                    this.get('_selection').pushObject(item);
                });
            } else if (items) {
                this.get('_optionsFromModel').pushObject(items);
                this.set('_selection', items);
            }

        }).finally(() => {
            this.set('_initialized', true);
            this.set('_loading', false);
        });
    },

    _clearSearchOptions: function() {
        this.get('_optionsFromSearch').clear();
    },

    _getRelatedPromise: function() {
        let field = this.get('relField');
        return this.get('model').get(field);
    },

    _loadSearchOptions: function() {

        let textInput = this.get('_textInput');

        if (!this.get('_loading')) {
            if (!textInput) {
                // If no text input was provided, prevent the query from executing.
                return;
            }

            this.set('_loading', true);

            this._getRelatedPromise().then(() => {
                // Ensure the query fires once the choices are loaded from the backened.
                // Once the promise is resolved, this will fire immediately.

                let field       = this.get('displayField');
                let criteria    = { };
                criteria[field] = { $regex : '/' + textInput + '/i' };

                this.get('query').execute(this.get('relMeta.type'), criteria, 0, 0, field).then((results) => {
                    // Set the results to the search options stack.
                    results.forEach((item) => this.get('_optionsFromSearch').pushObject(item));
                }).finally(() => this.set('_loading', false));
            });
        }
    },

    actions: {

        addItem: function(item) {
            // Add the item to the model option and selection stacks.
            this.get('_optionsFromModel').pushObject(item);
            this.get('_selection').pushObject(item);

            // Add the item to the model.
            this._getRelatedPromise().pushObject(item);
        },

        onBlur: function() {
            // Clear the search items when the control is blurred.
            this._clearSearchOptions();
        },

        removeItem: function(item) {
            // Remove the item from the selection and the option stacks.
            this.get('_selection').removeObject(item);
            this.get('_optionsFromModel').removeObject(item);
            this.get('_optionsFromSearch').removeObject(item);

            // Remove the item from the model.
            this._getRelatedPromise().removeObject(item);
        },

        selectItem: function(item) {
            // Clear tje current model option stack.
            this.get('_optionsFromModel').clear();
            if (item) {
                // If an item was selected, push it to the model option stack.
                this.get('_optionsFromModel').pushObject(item);
            }

            // Set the selection and model relationship to the selected item.
            this.set('_selection', item);
            set(this.get('model'), this.get('relField'), item);
        },

        updateFilter : function(value) {
            // Clear the options previously set by the search, so only the new ones appear.
            this._clearSearchOptions();
            this.set('_textInput', value);

            // Load the search options, only after the specified delay time (in ms) has been met.
            run.debounce(this, this._loadSearchOptions, this.get('delay'));
        },
    },
});
