function Utils()
{
    this.isDefined = function(value)
    {
        return 'undefined' !== typeof value;
    }

    this.isFunction = function(value)
    {
        return 'function' === typeof value;
    }

    this.isObject = function(value)
    {
        return 'object' === typeof value;
    }

    this.isString = function(value)
    {
        return 'string' === typeof value;
    }

    this.show = function() {
        container.style.display = 'block';
    }

    this.hide = function() {
        container.style.display = 'none';
    }

    this.showElement = function(element, display)
    {
        display = display || getDefaultDisplay(element.tagName.toLowerCase());
        element.style.display = display;
    }

    this.hideElement = function(element)
    {
        element.style.display = 'none';
    }

    this.titleize = function(str) {
        var out = str.replace(/^\s*/, "");
        out = out.replace(/^[a-z]|[^\s][A-Z]/g, function(str, offset) {
            if (offset == 0) {
                return(str.toUpperCase());
            } else {
                return(str.substr(0,1) + " " + str.substr(1).toUpperCase());
            }
        });
        return(out);
    }

    this.lcFirst = function(str)
    {
        return str.charAt(0).toLowerCase() + str.slice(1);
    }

    this.ucFirst = function(str)
    {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    this.deparam = function( params, coerce, scoped ) {
        var
            decode       = decodeURIComponent,
            obj          = {},
            coerce_types = { 'true': !0, 'false': !1, 'null': null }
        ;
        // Iterate over all name=value pairs.
        $.each( params.replace(/\+/g, ' ').split('&'), function(j, v) {
            var
                param = v.split( '=' ),
                key   = decode(param[0])
            ;

            if (scoped) {
                var parts = key.split('.');
                if ('rdx' !== parts[0] || !parts[1]) return;
                key = parts[1];
            }

            var
                val,
                cur = obj,
                i   = 0,
                // If key is more complex than 'foo', like 'a[]' or 'a[b][c]', split it
                // into its component parts.
                keys      = key.split(']['),
                keys_last = keys.length - 1
            ;

            // If the first keys part contains [ and the last ends with ], then []
            // are correctly balanced.
            if (/\[/.test( keys[0] ) && /\]$/.test(keys[keys_last])) {
                // Remove the trailing ] from the last keys part.
                keys[keys_last] = keys[keys_last].replace(/\]$/, '');

                // Split first keys part into two parts on the [ and add them back onto
                // the beginning of the keys array.
                keys      = keys.shift().split('[').concat(keys);
                keys_last = keys.length - 1;
            } else {
                // Basic 'foo' style key.
                keys_last = 0;
            }

            // Are we dealing with a name=value pair, or just a name?
            if (param.length === 2) {
                val = decode(param[1]);

                // Coerce values.
                if (coerce) {
                    val = val && !isNaN(val)              ? +val              // number
                        : val === 'undefined'             ? undefined         // undefined
                        : coerce_types[val] !== undefined ? coerce_types[val] // true, false, null
                        : val;                                                // string
                }

                if (keys_last) {
                    // Complex key, build deep object structure based on a few rules:
                    // * The 'cur' pointer starts at the object top-level.
                    // * [] = array push (n is set to array length), [n] = array if n is
                    //   numeric, otherwise object.
                    // * If at the last keys part, set the value.
                    // * For each keys part, if the current level is undefined create an
                    //   object or array based on the type of the next keys part.
                    // * Move the 'cur' pointer to the next level.
                    // * Rinse & repeat.
                    for (; i <= keys_last; i++) {
                        key = keys[i] === '' ? cur.length : keys[i];
                        cur = cur[key] = i < keys_last
                            ? cur[key] || ( keys[i+1] && isNaN( keys[i+1] ) ? {} : [] )
                            : val
                        ;
                    }
                } else {
                    // Simple key, even simpler rules, since only scalars and shallow
                    // arrays are allowed.

                    if ($.isArray(obj[key])) {
                        // val is already an array, so push on the next value.
                        obj[key].push(val);

                    } else if (obj[key] !== undefined) {
                        // val isn't an array, but since a second value has been specified,
                        // convert val into an array.
                        obj[key] = [obj[key], val];

                    } else {
                        // val is a scalar.
                        obj[key] = val;
                    }
                }

            } else if ( key ) {
                // No value was defined, so set something meaningful.
                obj[key] = coerce
                    ? undefined
                    : ''
                ;
            }
        });
        return obj;
    }

    this.parseQueryString = function(str, scoped)
    {
        str = (str || document.location.search).replace(/(^\?)/,'');
        if (!str) {
            return {};
        }
        return this.deparam(str, true, scoped);
    }

    this.parseDataAttributes = function(element)
    {
        var formatValue = function(value)
        {
            if (0 === value.length || "null" === value) {
                return null;
            }
            return value;
        }

        var attributes = {};
        if ('object' === typeof element.dataset) {
            for (var prop in element.dataset) {
                if (element.dataset.hasOwnProperty(prop)) {
                    attributes[prop] = formatValue(element.dataset[prop]);
                }
            }
            return attributes;
        }

        for (var i = 0; i < element.attributes.length; i++) {
            var
                attribute = element.attributes[i],
                search = 'data-'
            ;
            if (0 === attribute.name.indexOf(search)) {
                var prop = attribute.name.replace(search, '');
                attributes[prop] = formatValue(attribute.value);
            }
        };
        return attributes;
    }

    function getDefaultDisplay(tagName)
    {
        var
            display,
            t = document.createElement(tagName),
            gcs = "getComputedStyle" in window
        ;
        document.body.appendChild(t);
        display = (gcs ? window.getComputedStyle(t, '') : t.currentStyle).display;
        document.body.removeChild(t);
        return display;
    }
}
