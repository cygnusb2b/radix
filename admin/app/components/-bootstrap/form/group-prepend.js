import Ember from 'ember';

const { Component } = Ember;

export default Component.extend({
    tagName    : 'div',
    classNames : ['input-group-text input-group-prepend'],
    value      : null,
});
