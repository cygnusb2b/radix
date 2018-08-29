import ApolloService from 'ember-apollo-client/services/apollo';
import Ember from 'ember';
import { setContext } from 'apollo-link-context';
import { IntrospectionFragmentMatcher, InMemoryCache } from 'apollo-cache-inmemory';
import { Promise } from 'rsvp';
import introspectionQueryResultData from 'radix/gql/fragment-types';

const { computed, get, inject: { service } } = Ember;

export default ApolloService.extend({
  session: service(),

  clientOptions: computed(function() {
    return {
      link: this.get('link'),
      cache: new InMemoryCache({ fragmentMatcher: this.get('fragmentMatcher') }),
    };
  }),

  fragmentMatcher: computed(function() {
    return new IntrospectionFragmentMatcher({
      introspectionQueryResultData
    });
  }),

  link: computed(function() {
    const httpLink = this._super(...arguments);
    const authMiddleware = setContext((req, ctx) => {
      // return this._runAuthorize(req, ctx);
    });
    return authMiddleware.concat(httpLink);
  }),

  _runAuthorize() {
    const hash = localStorage.getItem('portal-hash');
    const headers = {};
    if (hash) headers['X-Portal-Hash'] = hash;
    if (!this.get('session.isAuthenticated')) {
      return { headers };
    }
    return new Promise((resolve) => {
      const data = this.get('session.data.authenticated.session');
      headers['Authorization'] = `Bearer ${get(data, 'token')}`;
      resolve({ headers })
    });
  }

});
