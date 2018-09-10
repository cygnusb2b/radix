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
    allPosts: (root, { criteria, pagination, sort }, { auth }) => {
      // auth.check();
      return Post.paginate({
        criteria: {
          stream: { '$exists' : true },
          ...criteria
        },
        pagination,
        sort,
      });
    },

    /**
     *
     */
    searchPosts: async (root, { criteria, pagination, phrase }, { auth }) => {
      // auth.check();
      const filter = { term: criteria };
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
    unapprovePost: async (root, { input: { id } }, { auth }) => {
      const post = await Post.findById(id);
      console.warn(post);
      post.set('approved', false);
      return post.save();
    },
    /**
     *
     */
    approvePost: async (root, { input: { id } }, { auth }) => {
      const post = await Post.findById(id);
      console.warn(post);
      post.set('approved', true);
      return post.save();
    },
    /**
     *
     */
    unflagPost: async (root, { input: { id } }, { auth }) => {
      const post = await Post.findById(id);
      console.warn(post);
      post.set('flagged', false);
      return post.save();
    },
    /**
     *
     */
    flagPost: async (root, { input: { id } }, { auth }) => {
      const post = await Post.findById(id);
      console.warn(post);
      post.set('flagged', true);
      return post.save();
    },
    /**
     *
     */
    deletePost: async (root, { input: { id } }, { auth }) => {
      const post = await Post.findById(id);
      console.warn(post);
      post.set('deleted', true);
      return post.save();
    },
    /**
     *
     */
    undeletePost: async (root, { input: { id } }, { auth }) => {
      const post = await Post.findById(id);
      console.warn(post);
      post.set('deleted', false);
      return post.save();
    },
    /**
     *
     */
    updatePost: async (root, { input: { id, payload } }, { auth }) => {
      const post = await Post.findById(id);
      post.set(payload);
      return post.save();
    },
  },
};
