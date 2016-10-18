import Ember from 'ember';
import Base from 'ember-simple-auth/authorizers/base';

const { isEmpty, inject: { service } } = Ember;

export default Base.extend({

    session: service('session'),

    authorize: function(sessionData, block) {
        const token     = sessionData['token'];
        const publicKey = this.get('session.data.selectedApp.key');

        if (!isEmpty(token)) {
            block('Authorization', `Bearer ${token}`);
        }
        if (!isEmpty(publicKey)) {
            block('X-Radix-AppId', publicKey);
        }
    }
});
