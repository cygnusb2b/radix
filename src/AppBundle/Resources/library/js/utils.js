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

    this.parseQueryString = function(str)
    {
        str = (str || document.location.search).replace(/(^\?)/,'');
        if (!str) {
            return {};
        }
        return str.split('&').map(function(n) {
            return n = n.split("="), this[n[0]] = window.decodeURIComponent(n[1]), this
        }.bind({}))[0];
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
