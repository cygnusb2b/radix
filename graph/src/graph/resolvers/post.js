const { paginationResolvers } = require('@limit0/mongoose-graphql-pagination');

module.exports = {

  Post: {
    stream: ({ stream }, input, { db }) => db.model('post-stream').findById(stream),
    account: ({ account }, input, { db }) => db.model('identity').findById(account),
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
    post: (root, { input }, { auth, db }) => {
      auth.check();
      const { id } = input;
      return db.model('post').findById(id);
    },

    /**
     *
     */
    allPosts: (root, { criteria, pagination, sort }, { auth, db }) => {
      auth.check();
      return db.model('post').paginate({
        criteria: {
          stream: { $exists: true },
          ...criteria,
        },
        pagination,
        sort,
      });
    },

    /**
     *
     */
    searchPosts: async (root, { criteria, pagination, phrase }, { auth, db }) => {
      auth.check();
      return db.model('post').search({
        criteria: {
          stream: { $exists: true },
          ...criteria,
        },
        pagination,
        phrase,
      });
    },
  },

  /**
   *
   */
  Mutation: {
    /**
     *
     */
    unapprovePost: async (root, { input: { id } }, { auth, db }) => {
      auth.check();
      const post = await db.model('post').findById(id);
      post.set('approved', false);
      return post.save();
    },
    /**
     *
     */
    approvePost: async (root, { input: { id } }, { auth, db }) => {
      auth.check();
      const post = await db.model('post').findById(id);
      post.set('approved', true);
      return post.save();
    },
    /**
     *
     */
    unflagPost: async (root, { input: { id } }, { auth, db }) => {
      auth.check();
      const post = await db.model('post').findById(id);
      post.set('flagged', false);
      return post.save();
    },
    /**
     *
     */
    flagPost: async (root, { input: { id } }, { auth, db }) => {
      auth.check();
      const post = await db.model('post').findById(id);
      post.set('flagged', true);
      return post.save();
    },
    /**
     *
     */
    deletePost: async (root, { input: { id } }, { auth, db }) => {
      auth.check();
      const post = await db.model('post').findById(id);
      post.set('deleted', true);
      return post.save();
    },
    /**
     *
     */
    undeletePost: async (root, { input: { id } }, { auth, db }) => {
      auth.check();
      const post = await db.model('post').findById(id);
      post.set('deleted', false);
      return post.save();
    },
    /**
     *
     */
    updatePost: async (root, { input: { id, payload } }, { auth, db }) => {
      auth.check();
      const post = await db.model('post').findById(id);
      post.set(payload);
      return post.save();
    },
  },
};
