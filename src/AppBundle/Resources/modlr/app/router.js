import Ember from 'ember';
import config from './config/environment';

const Router = Ember.Router.extend({
  location: config.locationType
});

Router.map(function() {
    this.route('dashboard');
    this.route('not-found', {
        path: '/*wildcard'
    });

    this.route('model', function() {
        this.route('create');
        this.route('edit', { path: '/edit/:id' }, function() {
            this.route('attributes');
        });
    });

    this.route('mixin', function() {
        this.route('edit', {
            path: ':id'
        });
    });

    this.route('embed', function() {
        this.route('edit', {
            path: ':id'
        });
    });

    this.route('settings');
    this.route('logout');

});

export default Router;
