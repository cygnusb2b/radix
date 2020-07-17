const { paginationResolvers } = require('@limit0/mongoose-graphql-pagination');

module.exports = {

  Identity: {
    primaryEmail: async ({ _id }, input, { db }) => {
      const isPrimary = true;
      const model = await db.model('identity-account-email').findOne({ _id, isPrimary });
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
    identity: (root, { input }, { auth, db }) => {
      auth.check();
      const { id } = input;
      return db.model('identity').findById(id);
    },

    /**
     *
     */
    allIdentities: (root, { pagination, sort }, { auth, db }) => {
      auth.check();
      const criteria = { deleted: false };
      return db.model('identity').paginate({ criteria, pagination, sort });
    },

    /**
     *
     */
    searchIdentities: async (root, { pagination, phrase }, { auth, db }) => {
      auth.check();
      const filter = { term: { deleted: false } };
      return db.model('identity').search(phrase, { pagination, filter });
    },
  },

  /**
   *
   */
  Mutation: {
    /**
     *
     */
    banIdentity: async (root, { input: { id } }, { auth, db }) => {
      auth.check();
      const identity = await db.model('identity').findById(id);
      identity.set('settings.shadowbanned', true);
      return identity.save();
    },
    /**
     *
     */
    unbanIdentity: async (root, { input: { id } }, { auth, db }) => {
      auth.check();
      const identity = await db.model('identity').findById(id);
      identity.set('settings.shadowbanned', false);
      return identity.save();
    },
    /**
     *
     */
    updateIdentity: async (root, { input: { id, payload } }, { auth, db }) => {
      auth.check();
      const identity = await db.model('identity').findById(id);
      identity.set(payload);
      return identity.save();
    },
  },
};
