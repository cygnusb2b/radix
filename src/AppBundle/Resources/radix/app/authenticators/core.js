import Ember from 'ember';
import Base from 'ember-simple-auth/authenticators/base';

const { $, RSVP, inject: { service } } = Ember;

export default Base.extend({

    session: service('session'),

    restore: function(data) {
        // @todo Re-validate that the user can use the selected app. Remove if cannot.
        let _self = this;
        return new RSVP.Promise(function(resolve, reject) {
            $.get('/auth/user/retrieve').done(function(response) {
                resolve(response.data);
            }).fail(function(jqXHR) {
                reject(_self.formatError(jqXHR));
            });
        });
    },

    authenticate: function(username, password) {
        let _self = this;
        return new RSVP.Promise(function(resolve, reject) {
            $.ajax('/auth/user/submit', {
                method      : 'POST',
                contentType : 'application/json',
                data: JSON.stringify({ data: { username: username, password: password } })
            }).done(function(response) {
                resolve(response.data);
            }).fail(function(jqXHR) {
                reject(_self.formatError(jqXHR));
            });
        });
    },

    invalidate: function() {
        let _self = this;
        this.get('session').set('data.selectedApp', null); // Kill the active app.

        return new RSVP.Promise(function(resolve, reject) {
            $.get('/auth/user/destroy').done(function(response) {
                resolve({});
            }).fail(function(jqXHR) {
                resolve({}); // Always resolve.
            });
        });
    },

    formatError: function(jqXHR) {
        if (jqXHR.responseJSON && jqXHR.responseJSON.errors) {
            return jqXHR.responseJSON.errors[0];
        }
        return 'An unknown, fatal error occurred. Please try again.';
    }
});
