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
        this.route('edit', {
            path: ':model_id'
        });
    });

    this.route('mixin', function() {
        this.route('edit', {
            path: ':mixin_id'
        });
    });

    this.route('embed', function() {
        this.route('edit', {
            path: ':embed_id'
        });
    });

    this.route('settings');
    this.route('logout');

});

export default Router;
