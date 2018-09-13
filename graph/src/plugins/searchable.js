const { Pagination } = require('@limit0/mongoose-graphql-pagination');

module.exports = function searchablePlugin(schema, { fieldNames = [] } = {}) {
  const buildMongoQuery = (phrase = 'search', criteria = {}, prefix = '') => {
    const clean = phrase.replace(/[|&;$%@"<>()+,]/g, '');
    const $or = fieldNames.map(f => ({ [f]: new RegExp(`${prefix}${clean}`, 'i') }));
    return { ...criteria, $or };
  };

  /**
   * The `search` static method.
   */
  schema.static('search', function search({ criteria = {}, pagination, phrase } = {}) {
    const query = buildMongoQuery(phrase, criteria);
    return new Pagination(this, { pagination, criteria: query });
  });

  /**
   * The `autocomplete` static method.
   */
  schema.static('autocomplete', function search({ criteria = {}, pagination, phrase } = {}) {
    const query = buildMongoQuery(phrase, criteria, '^');
    return new Pagination(this, { pagination, criteria: query });
  });
};
