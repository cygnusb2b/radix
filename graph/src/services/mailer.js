const sgMail = require('@sendgrid/mail');

const {
  SENDGRID_API_KEY,
  SENDGRID_FROM,
} = require('../env');

module.exports = {
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

  async sendPasswordReset(user, baseUri, token) {
    const to = user.email;
    const subject = 'Your Radix password reset request';
    const linkUrl = `${baseUri}/manage/reset/${token}`;
    const html = `<p>Hello ${user.givenName}!</p><p>Someone (hopefully you) has requested the password linked to this email address be reset. If you would like to continue, please click the link below. If you believe you have received this message in error, no further action is required.</p><hr><p>Click <a href="${linkUrl}">here</a> to reset your password.</p><p>If the link above doesn't work, copy and paste the URL into your browser: <pre>${linkUrl}</pre></p>`;

    return this.send({ to, subject, html });
  },
};
