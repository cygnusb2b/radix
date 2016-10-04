function Ajax()
{
    this.supports = function() {
        return ('object' === typeof XMLHttpRequest || 'function' === typeof XMLHttpRequest) && 'withCredentials' in new XMLHttpRequest();
    }

    function isJson(xhr) {
        return 'application/json' === xhr.getResponseHeader('content-type') && xhr.response.length;
    }

    function parse(xhr) {
        if (isJson(xhr)) {
            try {
                return JSON.parse(xhr.response);
            } catch (e) {
                Debugger.error('Unable to parse JSON response!', e);
            }
        }
        return xhr.response;
    }

    this.send = function(endpoint, method, payload, headers) {
        if (false === this.supports()) {
            Debugger.error('XHR unsupported!');
            return;
        }
        method = method || 'POST';
        headers = 'object' === typeof headers ? headers : {};
        var url =  ClientConfig.values.host + endpoint;

        return new RSVP.Promise(function(resolve, reject) {
            var xhr = new XMLHttpRequest();

            headers['Content-Type']  = 'application/json';
            headers['X-Radix-AppId'] = ClientConfig.values.appId;

            xhr.open(method, url, true);
            for (var i in headers) {
                if (headers.hasOwnProperty(i)) {
                    xhr.setRequestHeader(i, headers[i]);
                }
            }

            xhr.withCredentials = true;

            xhr.onreadystatechange = function() {
                if (this.readyState === this.DONE) {
                    if (this.status >= 200 && this.status < 300) {
                        resolve(parse(this));
                    } else {
                        reject(parse(this));
                    }
                }
            }

            if (payload) {
                Debugger.info('Sending XHR request', method, url, headers, payload);
                xhr.send(JSON.stringify(payload));
            } else {
                Debugger.info('Sending XHR request', method, url, headers);
                xhr.send();
            }
        });
    }
}
