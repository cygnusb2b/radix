import EmberRouter from '@ember/routing/router';
import config from './config/environment';

const Router = EmberRouter.extend({
  location: config.locationType,
  rootURL: config.rootURL
});

Router.map(function() {
    this.route('login');

    this.route('user', function() {
        this.route('settings');
    });

    this.route('comments', function() {
    });
    this.route('app', { path: 'app/:id' }, function() {
        this.route('users');
        this.route('settings');
    })

    this.route('identities', function() {
        this.route('edit', { path: '/edit/:id' });
    });

    this.route('core', function() {
        this.route('accounts', function() {
            this.route('create');
            this.route('edit', { path: '/edit/:id' });
        });
        this.route('applications', function() {
            this.route('create');
            this.route('edit', { path: '/edit/:id' });
        });
        this.route('users', function() {
            this.route('create');
            this.route('edit', { path: '/edit/:id' });
        });
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

    this.route('form', function() {
        this.route('definitions', function() {
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
