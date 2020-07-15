import Fragment     from 'ember-data-model-fragments/fragment';
import { fragment } from 'ember-data-model-fragments/attributes';

export default Fragment.extend({
    branding      : fragment('application-settings-branding'),
    notifications : fragment('application-settings-notifications'),
    support       : fragment('application-settings-support'),
});
