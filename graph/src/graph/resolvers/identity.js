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
    banIdentity: async (root, { input: { id } }, { auth }) => {
      const identity = await Identity.findById(id);
      console.warn(identity);
      identity.set('settings.shadowbanned', true);
      return identity.save();
    },
    /**
     *
     */
    unbanIdentity: async (root, { input: { id } }, { auth }) => {
      const identity = await Identity.findById(id);
      console.warn(identity);
      identity.set('settings.shadowbanned', false);
      return identity.save();
    },
    /**
     *
     */
    updateIdentity: async (root, { input: { id, payload } }, { auth }) => {
      const identity = await Identity.findById(id);
      identity.set(payload);
      return identity.save();
    },
  },
};
