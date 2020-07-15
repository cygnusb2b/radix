import Ember from 'ember';

const { Component, inject: { service }, run, computed } = Ember;

export default Component.extend({
    /**
     * Services.
     */
    query : service('model-query'),

    /**
     * Public options
     */
    value        : null,
    multiple     : false, // @todo If a way can be found to get the rel meta off a belongsTo rel, this wouldn't be needed.
    modelType    : null,  // @todo If a way can be found to get the rel meta off a belongsTo rel, this wouldn't be needed.
    delay        : 300,
    placeholder  : 'Begin typing to select...',
    displayField : 'name',

    /**
     * Computed properties
     */
    disabled : computed('_initialized', function() {
        return (!this.get('_initialized'));
    }),

    /**
     * Private options.
     */
    _loading     : false,
    _initialized : false,

    _options           : computed.uniqBy('_optionsMerged', 'id'),
    _optionsMerged     : computed.union('_optionsFromModel', '_optionsFromSearch'),
    _optionsFromModel  : computed('value', function() {
        if (this.get('multiple')) {
            return this.get('value');
        } else if (this.get('value.id')) {
            return [this.get('value')];
        }
        return [];
    }),

    _optionsFromSearch : [],

    _selection : [],

    _textInput : null,

    init : function() {
        this._super(...arguments);
        this.get('value').finally(() => {
            this.set('_selection', this.get('value'));
            this.set('_initialized', true);
            this.set('_loading', false);
        });
    },

    _clearSearchOptions: function() {
        this.get('_optionsFromSearch').clear();
    },

    _loadSearchOptions: function() {

        let textInput = this.get('_textInput');

        if (!this.get('_loading')) {
            if (!textInput) {
                // If no text input was provided, prevent the query from executing.
                return;
            }

            this.set('_loading', true);

            this.get('value').then(() => {
                // Ensure the query fires once the choices are loaded from the backened.
                // Once the promise is resolved, this will fire immediately.
                let field       = this.get('displayField');
                let criteria    = { };
                criteria[field] = { $regex : '/' + textInput + '/i' };

                this.get('query').execute(this.get('modelType'), criteria, 0, 0, field).then((results) => {
                    // Set the results to the search options stack.
                    results.forEach((item) => this.get('_optionsFromSearch').pushObject(item));
                }).finally(() => this.set('_loading', false));
            });
        }
    },

    actions: {

        addItem: function(item) {
            // Add the item to the model.
            this.get('value').pushObject(item);

            // Add the item to the selection stack.
            this.get('_selection').pushObject(item);
        },

        onBlur: function() {
            // Clear the search items when the control is blurred.
            this._clearSearchOptions();
        },

        removeItem: function(item) {
            // Remove the item from the selection and option stacks.
            this.get('_selection').removeObject(item);
            this.get('_optionsFromSearch').removeObject(item);

            // Remove the item from the model.
            this.get('value').removeObject(item);
        },

        selectItem: function(item) {
            // Set the selection and model relationship to the selected item.
            this.set('value', item);
            this.set('_selection', item);
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
