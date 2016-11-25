import SecureRoute from 'radix/routes/secure';

export default SecureRoute.extend({
    actions: {
        loadNavItems: function() {
            return [
                { linkTo: 'demographic.questions', label: 'Questions' },
                { linkTo: 'demographic.choices',   label: 'Choices'   },
                { linkTo: 'demographic.labels',    label: 'Labels'    },
            ];
        }
    }
});
