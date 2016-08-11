import Ember from 'ember';
import LoadingDisplay from 'modlr/mixins/loading-display';

export default Ember.Mixin.create(LoadingDisplay, {
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
        deleteModel: function(record, redirect, model) {
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
        undoChanges: function(record, redirect, prompt) {
            let rollback;
            prompt = prompt || true;

            if (prompt) {
                rollback = confirm('All changes will be discarded. Are you sure you want to continue?');
            } else {
                rollback = true;
            }
            if (true === rollback) {
                record.rollbackAttributes();
                if (redirect) {
                    this.transitionTo(redirect);
                }
            }
        },
        saveModel: function(record) {
            var _this = this;
            if (Ember.$.isEmptyObject(record.changedAttributes())) {
                return;
            }

            let isNew = record.get('isNew');


            // if (record.validate()) {
                this.showLoading();
                record.save().then(function() {
                    _this.hideLoading();
                    // _this.get('notify').success('Created successfully!');
                    if (isNew) {
                        _this.transitionTo('model.edit', record.get('id'));
                        _this.send('recordAdded');
                    }
                }, function() {
                    _this.hideLoading();
                    _this.send('showErrors');
                });
            // } else {
            //     var errors = record.get('errors');
            //     _this.send('showErrors', errors);
            // }
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
