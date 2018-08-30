const { paginationResolvers } = require('@limit0/mongoose-graphql-pagination');
const Post = require('../../models/post');
const PostStream = require('../../models/post-stream');
const Identity = require('../../models/identity');

module.exports = {

  Post: {
    stream: ({ stream }) => PostStream.findById(stream),
    account: ({ account }) => Identity.findById(account),
  },

  /**
   *
   */
  PostConnection: paginationResolvers.connection,

  /**
   *
   */
  Query: {
    /**
     *
     */
    post: (root, { input }, { auth }) => {
      // auth.check();
      const { id } = input;
      return Post.findById(id);
    },

    /**
     *
     */
    allPosts: (root, { pagination, sort }, { auth }) => {
      // auth.check();
      const criteria = { deleted: false };
      return Post.paginate({ criteria, pagination, sort });
    },

    /**
     *
     */
    searchPosts: async (root, { pagination, phrase }, { auth }) => {
      // auth.check();
      const filter = { term: { deleted: false } };
      return Post.search(phrase, { pagination, filter });
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
