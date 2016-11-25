import SecureRoute from 'radix/routes/secure';

export default SecureRoute.extend({
    actions: {
        loadNavigation: function() {
            return [
                [
                    { linkTo: 'integrations.omeda', label: 'Omeda' },
                ],
            ];
        }
    }
});
