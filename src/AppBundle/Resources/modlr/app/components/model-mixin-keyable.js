import Ember from 'ember';

export default Ember.Component.extend({

    name: null,

    key: null,

    linkName: true,

    disableKey: false,

    previousName: null,

    previousKey: null,

    nameChanged: Ember.observer('name', function() {
        if (!this.get('linkName')) {
            return;
        }

        let key      = this.get('key');
        let name     = this.get('name');
        let previous = this.get('previousName');

        if (!key || key === this._sluggifyValue(previous)) {
            this.set('key', name);
        }
        this.set('previousName', name);
    }),

    keyChanged: Ember.observer('key', function() {
        let key       = this.get('key');
        let previous  = this.get('previousKey') || null;
        let sluggfied = this._sluggifyValue(key);

        this.set('previousKey', sluggfied);

        if (previous === key) {
            return;
        }

        this.set('key', sluggfied);
    }),

    _sluggifyValue: function(value) {
        if (!value) {
            return;
        }
        return value
            .toLowerCase()
            .replace(/[^a-z0-9-]/g, '-')
            .replace(/-{2,}/g, '-')
            .replace(/^-|-$/g, '')
        ;
    }
});
