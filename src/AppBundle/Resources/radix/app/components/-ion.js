import Ember from 'ember';

const { Component, computed } = Ember;

const IonComponent = Component.extend({
  tagName: 'i',
  classNameBindings: ['_iconClassName'],

  _iconClassName: computed('name', function() {
    const name = this.get('name');
    if (!name) {
        return;
    }
    return `ion-${name}`;
  }),

});

IonComponent.reopenClass({
  positionalParams: ['name'],
});

export default IonComponent;
