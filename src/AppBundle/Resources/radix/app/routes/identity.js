import SecureRoute from 'radix/routes/secure';

export default SecureRoute.extend({
    actions: {
        loadNavItems: function() {
            return [
                { linkTo: 'identity.accounts', label: 'Accounts'    },
                { linkTo: 'identity.internal', label: 'Identities'  },
                { linkTo: 'identity.external', label: 'Third-Party' },
            ];
        }
    }
});
