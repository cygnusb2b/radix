import Ember from 'ember';
import LoadingDisplay from 'modlr/mixins/loading-display';

export default Ember.Route.extend(LoadingDisplay, {
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
        error: function(error, transition) {

            error = error.errors[0];
            if (error && 404 === parseInt(error.status)) {
                transition.abort();
                return this.transitionTo('/not-found');
            }
            return true;
        },
        willTransition: function() {
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
