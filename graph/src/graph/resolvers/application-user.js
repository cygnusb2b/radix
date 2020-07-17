const { paginationResolvers } = require('@limit0/mongoose-graphql-pagination');
const CoreApplicationUser = require('../../models/core-application-user');
const CoreUser = require('../../models/core-user');
const CoreApplication = require('../../models/core-application');
const mailer = require('../../services/mailer');

module.exports = {

  CoreApplicationUser: {
    user: ({ user }) => CoreUser.findById(user),
    application: (model, input, { appId }) => CoreApplication.findById(appId),
    roles: ({ roles }) => (Array.isArray(roles) ? roles : [roles]),
  },

  /**
   *
   */
  CoreApplicationUserConnection: paginationResolvers.connection,

  /**
   *
   */
  Query: {
    /**
     *
     */
    allCoreApplicationUsers: async (root, { criteria, pagination, sort }, { auth, appId }) => {
      auth.check();
      const query = {
        roles: { $ne: 'ROLE_SUPERADMIN' },
        application: appId,
        ...criteria,
      };
      return CoreApplicationUser.paginate({ criteria: query, pagination, sort });
    },
  },

  /**
   *
   */
  Mutation: {
    /**
     *
     */
    addCoreApplicationUser: async (root, { input: { payload } }, { auth, appId, domain }) => {
      auth.check();
      const {
        email,
        givenName,
        familyName,
        roles,
      } = payload;
      const application = await CoreApplication.findById(appId);
      if (!application) throw new Error('Unable to retrieve application by requested id!');
      let user = await CoreUser.findOne({ email });
      if (!user) {
        const createdDate = new Date();
        const updatedDate = createdDate;
        user = await CoreUser.create({
          email,
          givenName,
          familyName,
          createdDate,
          updatedDate,
        });
        user.save();
      }
      const appUser = new CoreApplicationUser({ user, roles, application });
      await appUser.save();
      const token = await mailer.createToken({ id: user.id }, 60 * 60);
      await mailer.sendWelcomeEmail({ user, application, domain, token });
      return appUser;
    },
    /**
     *
     */
    updateCoreApplicationUser: async (root, { input: { id, payload } }, { auth }) => {
      auth.check();
      const { roles } = payload;
      const updatedDate = new Date();
      const appUser = await CoreApplicationUser.findById(id);
      if (!appUser) throw new Error('Unable to retrieve application user by requested id!');
      appUser.set({ roles, updatedDate });
      return appUser.save();
    },
    /**
     *
     */
    removeCoreApplicationUser: async (root, { input: { id } }, { auth }) => {
      auth.check();
      const appUser = await CoreApplicationUser.findById(id);
      if (!appUser) throw new Error('Unable to retrieve application user by requested id!');
      appUser.remove();
      return appUser.save();
    },
  },
};
