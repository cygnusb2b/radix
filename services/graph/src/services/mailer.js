const sgMail = require('@sendgrid/mail');
const jwt = require('jsonwebtoken');
const uuid = require('uuid/v4');

const {
  SENDGRID_API_KEY,
  SENDGRID_FROM,
  JWT_SECRET,
} = require('../env');

module.exports = {
  async createToken(payload = {}, ttl) {
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
  },

  async send({ to, subject, html }) {
    if (!SENDGRID_API_KEY) throw new Error('Required environment variable "SENDGRID_API_KEY" was not set.');
    if (!SENDGRID_FROM) throw new Error('Required environment variable "SENDGRID_FROM" was not set.');

    const payload = {
      to,
      from: SENDGRID_FROM,
      subject,
      html,
    };

    sgMail.setApiKey(SENDGRID_API_KEY);
    return sgMail.send(payload);
  },

  async sendPasswordReset({ user, domain, token }) {
    const to = user.email;
    const subject = 'Your Radix password reset request';
    const linkUrl = `${domain}/manage/reset/${token}`;
    const html = `<p>Hello ${user.givenName}!</p><p>Someone (hopefully you) has requested the password linked to this email address be reset. If you would like to continue, please click the link below. If you believe you have received this message in error, no further action is required.</p><hr><p>Click <a href="${linkUrl}">here</a> to reset your password.</p><p>If the link above doesn't work, copy and paste the URL into your browser: ${linkUrl}</p>`;

    return this.send({ to, subject, html });
  },

  async sendWelcomeEmail({
    user,
    application,
    domain,
    token,
  }) {
    const to = user.email;
    const subject = 'Welcome to Radix';
    const linkUrl = `${domain}/manage/reset/${token}`;
    const html = `<p>Hello ${user.givenName}!</p><p>You have been invited to use the Radix account ${application.name}. To accept the invitation and set a password for your account, click the link below.</p><hr><p>Click <a href="${linkUrl}">here</a> to set your password.</p><p>If the link above doesn't work, copy and paste the URL into your browser: ${linkUrl}</p>`;

    return this.send({ to, subject, html });
  },
};
