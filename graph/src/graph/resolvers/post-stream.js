const { paginationResolvers } = require('@limit0/mongoose-graphql-pagination');
const PostStream = require('../../models/post-stream');

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
    postStream: (root, { input }, { auth }) => {
      // auth.check();
      const { id } = input;
      return PostStream.findById(id);
    },

    /**
     *
     */
    allPostStreams: (root, { pagination, sort }, { auth }) => {
      // auth.check();
      const criteria = { deleted: false };
      console.warn({ criteria, pagination, sort });
      return PostStream.paginate({ criteria, pagination, sort });
    },

    /**
     *
     */
    searchPostStreams: async (root, { pagination, phrase }, { auth }) => {
      // auth.check();
      const filter = { term: { deleted: false } };
      return PostStream.search(phrase, { pagination, filter });
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
