import Ember from 'ember';
import RouteQueryManager from 'ember-apollo-client/mixins/route-query-manager';
import { getObservable } from 'ember-apollo-client';

import query from 'radix/gql/queries/core-application-user/list';

const { Route } = Ember;

export default Route.extend(RouteQueryManager, {
  queryParams: {
    phrase: {
      refreshModel: true
    },
    first: {
      refreshModel: true
    },
    after: {
      refreshModel: true
    },
    sortBy: {
      refreshModel: true
    },
    filterBy: {
      refreshModel: true
    },
    ascending: {
      refreshModel: true
    },
  },

  buildCriteria(filter) {
    const criteria = {};
    switch (filter) {
      case 'role_user':
        criteria.roles = "ROLE_USER";
        break;
      case 'role_admin':
        criteria.roles = "ROLE_ADMIN";
        break;
    }
    return criteria;
  },

  model({ first, after, sortBy, ascending, filterBy }, { params }) {
    const controller = this.controllerFor(this.get('routeName'));
    const pagination = { first, after };
    const criteria = this.buildCriteria(filterBy);
    criteria.application = params.app.id;

    const sort = { field: sortBy, order: ascending ? 1 : -1 };
    const variables = { criteria, pagination, sort };
    if (!sortBy) delete variables.sort.field;
    const resultKey = 'allCoreApplicationUsers';
    controller.set('resultKey', resultKey);
    return this.get('apollo').watchQuery({ query, variables, fetchPolicy: 'network-only' }, resultKey)
      .then((result) => {
        controller.set('observable', getObservable(result));
        return result;
      }).catch(e => this.get('graphErrors').show(e))
    ;
  },

  actions: {
    refresh() {
      this.refresh();
    }
  },
});

