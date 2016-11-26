import Ember from 'ember';
import config from './config/environment';

const Router = Ember.Router.extend({
  location: config.locationType,
  rootURL: config.rootURL
});

Router.map(function() {
    this.route('login');

    this.route('user', function() {
        this.route('settings');
    });

    this.route('modeling', function() {
        this.route('models', function() {
            this.route('create');
            this.route('edit', { path: '/edit/:id' });
        });
        this.route('mixins', function() {
            this.route('create');
            this.route('edit', { path: '/edit/:id' });
        });
        this.route('embeds', function() {
            this.route('create');
            this.route('edit', { path: '/edit/:id' });
        });
    });

    this.route('identity', function() {
        this.route('accounts', function() {
            this.route('edit', { path: '/edit/:id' });
        });
        this.route('internal', function() {
            this.route('edit', { path: '/edit/:id' });
        });
        this.route('external', function() {
            this.route('edit', { path: '/edit/:id' });
        });
    });

    // @todo This will eventually need to be fed by the enabled integration partners.
    // Should likely be an interface for turning on/off.
    this.route('integrations', function() {
        this.route('omeda', function() {
            this.route('create');
            this.route('edit', { path: '/edit/:id' });
        });
    });

    this.route('demographic', function() {
        this.route('questions', function() {
            this.route('create');
            this.route('edit', { path: '/edit/:id' });
        });
        this.route('choices', function() {
            this.route('edit', { path: '/edit/:id' });
        });
        this.route('labels', function() {
            this.route('create');
            this.route('edit', { path: '/edit/:id' });
        });
        this.route('integrations', function() {
            this.route('create');
            this.route('edit', { path: '/edit/:id' });
        });
    });

    this.route('product', function() {
        this.route('email-deployments', function() {
            this.route('create');
            this.route('edit', { path: '/edit/:id' });
        });
        this.route('tags', function() {
            this.route('create');
            this.route('edit', { path: '/edit/:id' });
        });
    });
});

export default Router;
