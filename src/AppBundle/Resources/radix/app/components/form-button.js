import Ember from 'ember';

export default Ember.Component.extend({

    label: null,
    tagName: 'button',
    buttonType: 'button',
    icon: null,
    title: null,
    disabled: false,
    disabledReason: null,

    onClick: null,

    classNames: ['btn'],
    attributeBindings: ['disabled', 'determineTitle:title', 'buttonType:type', 'disabled:aria-disabled', 'determineTitle:aria-label'],

    determineTitle: Ember.computed('title', 'disabledReason', function() {
        let title  = this.get('title');
        let label  = this.get('label');
        let reason = this.get('disabledReason');

        if (title) {
            return title;
        }

        if (this.get('disabled')) {
            return (reason) ? reason : label;
        }
        return label;
    }),

    click: function() {
        this.sendAction('onClick');
    }

});
