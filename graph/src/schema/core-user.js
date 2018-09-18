const { Schema } = require('mongoose');
const bcrypt = require('bcrypt');
const crypto = require('crypto');

const schema = new Schema({
  email: String,
  picture: String,
  givenName: String,
  familyName: String,
  createdDate: Date,
  updatedDate: Date,
  lastSeen: Date,
  password: String,
}, { collection: 'core-user' });

schema.pre('save', function setPassword(next) {
  if (!this.isModified('password') || this.password.match(/^\$2[ayb]\$.{56}$/)) {
    next();
  } else {
    bcrypt.hash(this.password, 13).then((hash) => {
      this.password = hash;
      next();
    }).catch(next);
  }
});

schema.pre('save', function setPicture(next) {
  if (!this.picture) {
    const hash = crypto.createHash('md5').update(this.email).digest('hex');
    this.picture = `https://www.gravatar.com/avatar/${hash}`;
  }
  next();
});

module.exports = schema;
