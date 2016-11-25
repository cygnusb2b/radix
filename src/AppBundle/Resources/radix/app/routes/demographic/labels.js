import Ember from 'ember';

export default Ember.Route.extend({

    loading: Ember.inject.service(),

    limit:  25,
    offset: 0,

    model: function() {
        let _this = this;
        let criteria = {};

        this.get('loading').show();

        return this.store.query('question-tag', {
            page: {
                offset: parseInt(this.get('offset')),
                limit:  parseInt(this.get('limit'))
            },
            filter: {
                query: {
                    criteria: JSON.stringify(criteria)
                }
            },
            sort: "-createdDate,name",
        }).then(function(results) {
            _this.get('loading').hide();
            return results;
        }, function() {
            _this.get('loading').hide();
        });
    },

    setupController: function(controller, model) {
        this._super(controller, model);
    },

    actions: {
        loadTabs: function() {
            return [
                { key : 'general',      text : 'General',      icon : 'ion-document',            template : 'demographic/choices/-general', active : true },
                { key : 'demographics', text : 'Demographics', icon : 'ion-pricetag',            template : 'demographic/choices/-demographics' },
                { key : 'info',         text : 'Info',         icon : 'ion-information-circled', template : 'demographic/choices/-info' },
            ];
        },
        recordAdded: function() {
            this.refresh();
        }
    }

});
