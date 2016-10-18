import Ember from 'ember';

const { inject: { service }, Component } = Ember;

export default Component.extend({
    username: null,
    password: null,
    errorMessage: null,
    session: service('session'),

    actions: {
        authenticate: function() {
            this.set('errorMessage', null);
            let { username, password } = this.getProperties('username', 'password');
            this.get('session')
                .authenticate('authenticator:core', username, password)
                .catch((error) => this.set('errorMessage', error.detail || null))
            ;
        }
    }
});
