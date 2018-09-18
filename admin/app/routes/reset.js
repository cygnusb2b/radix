import Route from '@ember/routing/route';
import UnauthenticatedRouteMixin from 'ember-simple-auth/mixins/unauthenticated-route-mixin';
import RouteQueryManager from 'ember-apollo-client/mixins/route-query-manager';

import query from 'radix/gql/queries/core-application-user/reset';

export default Route.extend(UnauthenticatedRouteMixin, RouteQueryManager, {
  model({ token }) {
    const controller = this.controllerFor(this.get('routeName'));
    controller.set('token', token);
    const variables = { token };
    const resultKey = 'coreUserReset';
    return this.get('apollo').watchQuery({ query, variables, fetchPolicy: 'network-only' }, resultKey);
  }
});
