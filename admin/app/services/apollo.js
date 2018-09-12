import ApolloService from 'ember-apollo-client/services/apollo';
import { computed, get } from '@ember/object';
import { inject } from '@ember/service';
import { setContext } from 'apollo-link-context';
import { IntrospectionFragmentMatcher, InMemoryCache } from 'apollo-cache-inmemory';
import { Promise } from 'rsvp';
import introspectionQueryResultData from 'radix/gql/fragment-types';

export default ApolloService.extend({
  session: inject(),

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
      return this._runAuthorize(req, ctx);
    });
    return authMiddleware.concat(httpLink);
  }),

  _runAuthorize() {
    const headers = {};
    if (!this.get('session.isAuthenticated')) {
      return { headers };
    }
    return new Promise((resolve) => {
      const token = this.get('session.data.authenticated.token');
      const key = this.get('session.data.application.key')
      headers['Authorization'] = `Bearer ${token}`;
      headers['X-Radix-AppId'] = key;
      resolve({ headers })
    });
  }

});
