import IntegrationService from 'radix/models/integration-service';
import attr               from 'ember-data/attr';

export default IntegrationService.extend({
    appId      : attr('string'),
    brandKey   : attr('string'),
    clientKey  : attr('string'),
    inputId    : attr('string'),
    useStaging : attr('boolean', { defaultValue: false }),
});
