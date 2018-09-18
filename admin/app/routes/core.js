import SecureRoute from 'radix/routes/secure';

export default SecureRoute.extend({
    actions: {
        loadNavigation: function() {
            return [
                [
                    { linkTo: 'core.accounts', label: 'Accounts' },
                    { linkTo: 'core.applications',   label: 'Applications'   },
                ],
                [
                    { linkTo: 'core.users', label: 'Users' },
                ]
            ];
        }
    }
});
