import SecureRoute from 'radix/routes/secure';

export default SecureRoute.extend({
    actions: {
        loadNavItems: function() {
            return [
                { linkTo: 'product.email-deployments', label: 'Email Deployments' },
                { linkTo: 'product.tags',              label: 'Tags' },
            ];
        }
    }
});
