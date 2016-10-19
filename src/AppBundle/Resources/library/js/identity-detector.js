function IdentityDetector()
{
    // Should this happen before or after customer init?
    EventDispatcher.subscribe('appLoaded', function() {
        this.init();
    }.bind(this));

    this.init = function() {

        var query = Utils.parseQueryString(null, true);
        var run   = false;
        if (query.detect && (query.email || query.id)) {
            run = true;
        }

        Debugger.log('IdentityDetector.init()', query, run);

        if (!run) {
            return;
        }

        var data = {
            clientKey    : query.detect,
            primaryEmail : query.email || null,
            externalId   : query.id || null

        };
        delete query.detect;
        delete query.email;
        delete query.id;
        data['extra'] = query;

        Debugger.log('IdentityDetector.init() send', data);

        Ajax.send('/app/identity', 'POST', { data: data }).then(function(response) {

        }, function() {
            Debugger.error('Backend processing of identity unsucessful.')
        });


    }
}
