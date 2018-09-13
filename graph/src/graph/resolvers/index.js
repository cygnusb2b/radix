const deepAssign = require('deep-assign');
const { DateType, CursorType } = require('@limit0/graphql-custom-types');
const GraphQLJSON = require('graphql-type-json');
const MixedType = require('../types/mixed');

const applicationUser = require('./application-user');
const identity = require('./identity');
const post = require('./post');
const postStream = require('./post-stream');

module.exports = deepAssign(
  applicationUser,
  identity,
  post,
  postStream,
  {
    /**
     *
     */
    Date: DateType,
    Cursor: CursorType,
    Mixed: MixedType,
    JSON: GraphQLJSON,

    /**
     *
     */
    Query: {
      /**
       *
       */
      ping: () => 'pong',
    },
  },
);
