import Ember from 'ember';

const { Component } = Ember;

export default Component.extend({
    tagName    : 'small',
    classNames : ['form-text', 'text-muted'],
    value      : null,
});
