import Ember from 'ember';
import Base from 'ember-simple-auth/authenticators/base';

const { $, RSVP: { Promise }, inject: { service } } = Ember;

export default Base.extend({

  restore: (data) => new Promise((resolve, reject) => {
    if (data.token) {
      resolve(data);
    } else {
      reject(new Error('No token present'));
    }
  }),

  authenticate: (username, password) => {
    const credentials = { username, password };
    const settings = {
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({ data: credentials })
    };
    return new Promise((resolve, reject) => {
      $.ajax('/auth/user/submit', settings)
        .done(({ data }) => resolve(data))
        .fail(e => reject(e));
    });
  },

  invalidate: () => $.get('/auth/user/destroy'),
});
