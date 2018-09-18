const jwt = require('jsonwebtoken');
const uuid = require('uuid/v4');
const CoreUser = require('../../models/core-user');
const mailer = require('../../services/mailer');

const { JWT_SECRET } = require('../../env');

const validatePassword = (value, confirm) => {
  if (!value || !confirm) throw new Error('You must provide and confirm your password.');
  if (value.length < 6) throw new Error('Passwords must be at least six characters long.');
  if (value !== confirm) throw new Error('The password does not match the confirmation password.');
};

const createToken = async (payload = {}, ttl) => {
  const now = new Date();
  const iat = Math.floor(now.valueOf() / 1000);

  const exp = ttl ? iat + ttl : undefined;

  const toSign = {
    jti: uuid(),
    iat,
    exp,
    ...payload,
  };
  return jwt.sign(toSign, JWT_SECRET);
};

const validateToken = (encoded) => {
  if (!encoded) throw new Error('Unable to verify token: no value was provided.');
  const verified = jwt.verify(encoded, JWT_SECRET, { algorithms: ['HS256'] });
  if (!verified) throw new Error('Invalid token');
  return verified;
};

module.exports = {
  Query: {
    coreUserReset: async (root, { token }) => {
      const decoded = await validateToken(token);
      return CoreUser.findById(decoded.id);
    },
  },
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
    /**
     *
     */
    resetUserPassword: async (root, { input }, { auth }) => {
      auth.check();
      const { token, value, confirm } = input;
      const decoded = await validateToken(token);
      validatePassword(value, confirm);
      const user = await CoreUser.findById(decoded.id);
      if (!user) throw new Error('Unable to find user by requested id!');
      user.password = value;
      return user.save();
    },
    /**
     *
     */
    sendPasswordResetEmail: async (root, { email }, { domain }) => {
      const user = await CoreUser.findOne({ email });
      if (!user) return true;
      const token = await createToken({ id: user.id }, 60 * 60);
      await mailer.sendPasswordReset(user, domain, token);
      return true;
    },
  },
};
