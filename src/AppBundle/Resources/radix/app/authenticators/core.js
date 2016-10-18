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
                if (!response.data.id || (response.data.id !== data.id)) {
                    // The backend either killed the user session, or there was an identifier mismatch.
                    reject();
                } else {
                    resolve(response.data);
                }
            }).fail(function(jqXHR) {
                reject(_self.formatError(jqXHR));
            });
        });
    },

    authenticate: function(username, password) {
        // @todo Check for the presence of a selected app in session. If user can use it, leave it, else remove it.
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
        return new RSVP.Promise(function(resolve) {
            $.get('/auth/user/destroy').done(function() {
                resolve({});
            }).fail(function() {
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
