import Ember from 'ember';

const { inject: { service }, isEmpty, RSVP, Service } = Ember;

export default Service.extend({
    session : service('session'),
    store   : service(),
    loading : service(),
    user    : null,

    load: function() {
        let loading = this.get('loading');

        return new RSVP.Promise((resolve, reject) => {
            let userId = this.get('session.data.authenticated.id');
            if (!isEmpty(userId)) {
                this.get('store').find('core-user', userId).then((user) => {
                    this.set('user', user);
                    resolve();
                }, reject);
            } else {
                resolve();
            }
        });
    }
});
