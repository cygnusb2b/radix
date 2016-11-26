import Ember from 'ember';

const { TextField } = Ember;

export default TextField.extend({
    classNames        : ['form-control'],
    attributeBindings : ['describedBy:aria-describedby'],
    describedBy       : null,
});
