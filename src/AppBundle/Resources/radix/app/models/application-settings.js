import Fragment     from 'model-fragments/fragment';
import { fragment } from 'model-fragments/attributes';

export default Fragment.extend({
    branding      : fragment('application-settings-branding'),
    notifications : fragment('application-settings-notifications'),
    support       : fragment('application-settings-support'),
});
