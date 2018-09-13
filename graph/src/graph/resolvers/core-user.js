const CoreUser = require('../../models/core-user');

const validatePassword = (value, confirm) => {
  if (!value || !confirm) throw new Error('You must provide and confirm your password.');
  if (value.length < 6) throw new Error('Passwords must be at least six characters long.');
  if (value !== confirm) throw new Error('The password does not match the confirmation password.');
};

module.exports = {
  /**
   *
   */
  Mutation: {
    /**
     *
     */
    changeUserPassword: async (root, { input }, { auth }) => {
      auth.check();
      const { id, value, confirm } = input;
      validatePassword(value, confirm);
      const user = await CoreUser.findById(id);
      if (!user) throw new Error('Unable to find user by requested id!');
      user.password = value;
      return user.save();
    },
  },
};
