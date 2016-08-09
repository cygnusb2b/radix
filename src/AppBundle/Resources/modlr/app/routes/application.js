import Ember from 'ember';

export default Ember.Route.extend({
    notify: Ember.inject.service('notify'),
    store: Ember.inject.service(),

    type: null,

    beforeModel: function() {
        this.set('type', this.get('type'));
        this._super(...arguments);
    },

    renderTemplate: function() {
        this.render();
        this.render('navigation', {
            into: 'application',
            outlet: 'navigation'
        });
    },

    actions: {
        willTransition: function() {
            Ember.$('body').addClass('show-loading');
            // if (this.controller.get('userHasEnteredData') &&
            //     !confirm('Are you sure you want to abandon progress?')) {
            //     transition.abort();
            // } else {
            //     // Bubble the `willTransition` action so that
            //     // parent routes can decide whether or not to abort.
            //     return true;
            // }
        },
        didTransition: function() {
            Ember.$('body').removeClass('show-aside');
            Ember.$('.dropdown-toggle').removeClass('active');
            Ember.$('body').removeClass('show-loading');
        },
        toggleNavigation: function() {
            Ember.$('body').toggleClass('hide-navigation');
        },
        showAside: function(template, model) {

            if (Ember.$('body').hasClass('show-aside') && Ember.$('.dropdown-toggle.' + template).hasClass('active')) {
                return this.send('removeAside');
            }

            Ember.$('body').addClass('show-aside');
            Ember.$('.dropdown-toggle').removeClass('active');
            Ember.$('.dropdown-toggle.' + template).addClass('active');

            template = 'partials.' + template;

            this.disconnectOutlet({
                outlet: 'aside'
            });

            this.render(template, {
                into: 'application',
                outlet: 'aside',
                model: model
            });
        },
        removeAside: function() {
            Ember.$('body').removeClass('show-aside');
            Ember.$('.dropdown-toggle').removeClass('active');
            this.disconnectOutlet({
                parentView: 'application',
                outlet: 'aside'
            });
        },
        showModal: function(name, model) {
            this.render(name, {
                into: 'application',
                outlet: 'modal',
                model: model
            });
        },
        removeModal: function() {
            this.disconnectOutlet({
                outlet: 'modal',
                parentView: 'application'
            });
        }
    }
});
