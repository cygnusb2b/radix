import Ember from 'ember';
import LoadingDisplay from 'modlr/mixins/loading-display'

export default Ember.Mixin.create({
    store: Ember.inject.service(),
    notify: Ember.inject.service('notify'),

    actions: {
        createModel: function(type) {
            this.showLoading();

            var _this = this;
            var record = _this.store.createRecord(type);

            record.save().then(function(record) {
                _this.transitionTo(type + '.edit', record.get('id'));
                _this.send('recordAdded');
            }, function() {
                _this.hideLoading();
                _this.send('showErrors');
            });
        },
        createRelated: function(type, key, reference) {
            var _this = this;
            var record = _this.store.createRecord(type);

            record.set(key, reference);

            record.save().then(function() {
                // _this.get('notify').success('Created successfully!');
            }, function() {
                _this.send('showErrors');
            });
        },
        delete: function(record, redirect, model) {
            var _this = this;

            if (confirm("Are you sure you want to delete this record?")) {
                record.set('deleted', true);
                record.save().then(function() {
                    // _this.get('notify').success('Deleted successfully!');
                    if (redirect) {
                        if (redirect && model) {
                            return _this.transitionTo(redirect, model);
                        }
                        return _this.transitionTo(redirect);
                    }
                }, function() {
                    _this.send('showErrors');
                });
            }
        },
        restore: function(record) {
            var _this = this;

            record.set('deleted', false);
            record.save().then(function() {
                // _this.get('notify').success('Updated successfully!');
            }, function() {
                _this.send('showErrors');
            });
        },
        undo: function(record) {
            record.rollbackAttributes();
        },
        save: function(record, redirect) {
            var _this = this;
            if (Ember.$.isEmptyObject(record.changedAttributes())) {
                return;
            }
            if (record.validate()) {
                record.save().then(function() {
                    // _this.get('notify').success('Created successfully!');
                    if (redirect) {
                        _this.transitionToRoute(redirect, record.get('id'));
                    }
                }, function() {
                    _this.send('showErrors');
                });
            } else {
                var errors = record.get('errors');
                _this.send('showErrors', errors);
            }
        },
        showErrors: function(errors) {
            var _this = this;
            if (errors && errors.length) {
                errors.forEach(function(error) {
                    _this.get('notify').warning(error.message[0], {
                        closeAfter: null
                    });
                });
            } else {
                _this.get('notify').warning({
                    html: 'Something went wrong, please try again. If the problem persists please contact support.',
                    closeAfter: null
                });
            }
        }
    }
});
