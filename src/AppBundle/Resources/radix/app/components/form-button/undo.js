import Ember from 'ember';
import Button from '../form-button';

export default Button.extend({
    label      : 'Undo',
    icon       : 'ion-ios-undo',
    classNames : ['btn-warning'],
    layoutName : 'components/form-button',
});
