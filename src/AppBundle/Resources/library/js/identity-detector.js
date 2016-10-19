function IdentityDetector()
{
    EventDispatcher.subscribe('CustomerManager.init', function() {
        this.init();
    }.bind(this));

    this.init = function() {
        if (CustomerManager.isLoggedIn()) {
            // Disallow detection while logged in.
            return;
        }
        var query = Utils.parseQueryString(null, true);
        if (!query.detect || (!query.id && !query.email)) {
            return;
        }

        var data = {
            handler    : query.detect,
            identifier : query.id || null,
            email      : query.email || null
        };
        delete query.detect;
        delete query.id;
        delete query.email;
        data['fields'] = query;

        Ajax.send('/app/identity', 'POST', { data: data }).then(function(response) {

        }, function() {
            Debugger.error('Backend processing of identity unsucessful.')
        });

        console.warn('IdentityDetector.init', CustomerManager.isLoggedIn(), data);
    }
}
