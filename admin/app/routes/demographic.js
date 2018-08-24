import SecureRoute from 'radix/routes/secure';

export default SecureRoute.extend({
    actions: {
        loadNavigation: function() {
            return [
                [
                    { linkTo: 'demographic.questions', label: 'Questions' },
                    { linkTo: 'demographic.choices',   label: 'Choices'   },
                ],
                [
                    { linkTo: 'demographic.labels', label: 'Labels' },
                ]
            ];
        }
    }
});
