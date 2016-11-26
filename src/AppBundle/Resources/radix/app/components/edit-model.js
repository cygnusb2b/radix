import Ember from 'ember';

const { computed, typeOf, Component, inject: { service } } = Ember;

export default Component.extend({

    loading : service(),
    routing : service('-routing'),

    tagName    : 'div',
    classNames : ['card'],

    model     : {},
    routeName : null,

    tabs : computed('model', function() {
        let loader = this.get('loadTabsFrom');
        if ('function' !== typeOf(loader)) {
            throw new Error('No tab loader action specified!');
        }
        return loader();
    }),

    editRouteName : computed('routeName', function() {
        return `${this.get('routeName')}.edit`;
    }),

    routeInfo : computed('model', 'routeName', function() {
        let id = this.get('model.id');
        return [
            (id) ? [id] : undefined,
            (id) ? this.get('editRouteName') : this.get('routeName'),
        ];
    }),

    actions: {
        close: function() {
            // @todo Need to determine a way to prompt the confirm if unsaved changes.
            this.get('model').rollbackAttributes();
            this._redirectToRoute(this.get('routeName'));
        },
        undo: function() {
            // @todo Need to determine a way to prompt the confirm.
            this.get('model').rollbackAttributes();
        },
        save: function() {

            let _this          = this;
            let loading        = this.get('loading');
            let [name, params] = this.get('routeInfo');

            loading.show();

            this.get('model').save()
                .then(() => _this._redirectToRoute(name, params))
                .finally(() => loading.hide())
            ;
        },
    },

    _redirectToRoute: function(routeName, params) {
        return this.get('routing').transitionTo(routeName, params);
    },
});
