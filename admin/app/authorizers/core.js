import { isEmpty } from '@ember/utils';
import { inject } from '@ember/service';
import Base from 'ember-simple-auth/authorizers/base';

export default Base.extend({

  session: inject(),

  authorize: function (sessionData, block) {
    const token = sessionData['token'];
    const publicKey = this.get('session.data.application.key');

    if (!isEmpty(token)) block('Authorization', `Bearer ${token}`);
    if (!isEmpty(publicKey)) block('X-Radix-AppId', publicKey);
  }
});
