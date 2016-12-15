import Ember from 'ember';

const { Component } = Ember;

export default Component.extend({
    label      : null,
    linkTo     : null,
    tagName    : 'li',
    classNames : ['nav-item'],
});
