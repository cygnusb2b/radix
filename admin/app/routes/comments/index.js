import Ember from 'ember';
import RouteQueryManager from 'ember-apollo-client/mixins/route-query-manager';
import { getObservable } from 'ember-apollo-client';

import query from 'radix/gql/queries/post/list';
import search from 'radix/gql/queries/post/search';

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
      case 'deleted':
        criteria.deleted = true;
        break;
      case 'active':
        criteria.approved = true;
        criteria.deleted = false;
        break;
      case 'hidden':
        criteria.approved = false;
        criteria.deleted = false;
      case 'flagged':
        criteria.flagged = true;
        criteria.deleted = false;
    }
    return criteria;
  },

  search(phrase, pagination) {
    const controller = this.controllerFor(this.get('routeName'));
    const variables = { pagination, phrase };
    const resultKey = 'searchPosts';
    controller.set('resultKey', resultKey);
    return this.get('apollo').watchQuery({ query: search, variables, fetchPolicy: 'network-only' }, resultKey)
      .then((result) => {
        controller.set('observable', getObservable(result));
        return result;
      }).catch(e => this.get('graphErrors').show(e))
    ;
  },

  model({ first, after, sortBy, ascending, phrase, filterBy }) {
    const controller = this.controllerFor(this.get('routeName'));
    const pagination = { first, after };
    const criteria = this.buildCriteria(filterBy);

    if (phrase) {
      return this.search(phrase, pagination, criteria);
    }
    const sort = { field: sortBy, order: ascending ? 1 : -1 };
    const variables = { criteria, pagination, sort };
    if (!sortBy) delete variables.sort.field;
    const resultKey = 'allPosts';
    controller.set('resultKey', resultKey);
    return this.get('apollo').watchQuery({ query, variables, fetchPolicy: 'network-only' }, resultKey)
      .then((result) => {
        controller.set('observable', getObservable(result));
        return result;
      }).catch(e => this.get('graphErrors').show(e))
    ;
  },
});

