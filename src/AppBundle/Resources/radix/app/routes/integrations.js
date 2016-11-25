import SecureRoute from 'radix/routes/secure';

export default SecureRoute.extend({
    actions: {
        loadNavItems: function() {
            return [
                { linkTo: 'integrations.omeda', label: 'Omeda' },
            ];
        }
    }
});
