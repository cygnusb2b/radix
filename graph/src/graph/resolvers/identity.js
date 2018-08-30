const { paginationResolvers } = require('@limit0/mongoose-graphql-pagination');
const Identity = require('../../models/identity');
const IdentityAccountEmail = require('../../models/identity-account-email');

module.exports = {

  Identity: {
    primaryEmail: async ({ _id }) => {
      const isPrimary = true;
      const model = await IdentityAccountEmail.findOne({ _id, isPrimary });
      if (model) return model.value;
      return null;
    },
  },

  /**
   *
   */
  IdentityConnection: paginationResolvers.connection,

  /**
   *
   */
  Query: {
    /**
     *
     */
    identity: (root, { input }, { auth }) => {
      // auth.check();
      const { id } = input;
      return Identity.findById(id);
    },

    /**
     *
     */
    allIdentities: (root, { pagination, sort }, { auth }) => {
      // auth.check();
      const criteria = { deleted: false };
      return Identity.paginate({ criteria, pagination, sort });
    },

    /**
     *
     */
    searchIdentities: async (root, { pagination, phrase }, { auth }) => {
      // auth.check();
      const filter = { term: { deleted: false } };
      return Identity.search(phrase, { pagination, filter });
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
