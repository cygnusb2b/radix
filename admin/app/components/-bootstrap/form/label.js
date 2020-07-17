import Ember from 'ember';

const { Component } = Ember;

export default Component.extend({
    tagName           : 'label',
    classNameBindings : ['srOnly'],
    attributeBindings : ['forId:for'],

    value  : null,
    srOnly : false,
    forId  : null,
});
