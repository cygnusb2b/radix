function InquiryModule()
{
    this.config = ClientConfig.values.modules.inquiry;

    this.getProps  = function() {
        var jqObj = this.getTarget();
        if (!jqObj) {
            return {};
        }
        return {
            title  : jqObj.data('title') || 'Request More Information',
            model  : {
                type       : jqObj.data('model-type'),
                identifier : jqObj.data('model-identifier')

            },
            notify : {
                enabled : jqObj.data('enable-notify') || false,
                email   : jqObj.data('notify-email')  || null
            }
        };
    },

    this.getTarget = function() {
        var jqObj = $(this.config.target);
        return (jqObj.length) ? jqObj : undefined;
    },

    this.propsAreValid = function(props) {
        if (!props.model.type || !props.model.identifier) {
            Debugger.error('InquiryModule', 'No model-type or model-identifier data attribues found on the element. Unable to render.');
            return false;
        }
        return true;
    },

    this.render = function() {
        var jqObj = this.getTarget();
        if (!jqObj) {
            // Element not present.
            Debugger.info('InquiryModule', 'No target element found on page. Skipping render.');
            return;
        }

        var props = this.getProps();
        if (this.propsAreValid(props)) {
            React.render(
                React.createElement(Radix.Forms.get('Inquiry'), props),
                jqObj[0]
            );
        }
    }
}
