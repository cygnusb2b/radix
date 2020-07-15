const { paginationResolvers } = require('@limit0/mongoose-graphql-pagination');

module.exports = {

  PostStream: {
  },

  /**
   *
   */
  PostStreamConnection: paginationResolvers.connection,

  /**
   *
   */
  Query: {
    /**
     *
     */
    postStream: (root, { input }, { auth, db }) => {
      auth.check();
      const { id } = input;
      return db.model('post-stream').findById(id);
    },

    /**
     *
     */
    allPostStreams: (root, { pagination, sort }, { auth, db }) => {
      auth.check();
      const criteria = { deleted: false };
      return db.model('post-stream').paginate({ criteria, pagination, sort });
    },

    /**
     *
     */
    searchPostStreams: async (root, { pagination, phrase }, { auth, db }) => {
      auth.check();
      const filter = { term: { deleted: false } };
      return db.model('post-stream').search(phrase, { pagination, filter });
    },
  },

  /**
   *
   */
  Mutation: {
    /**
     *
     */
  },
};
