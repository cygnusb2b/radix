import SecureRoute from 'radix/routes/secure';

export default SecureRoute.extend({
  actions: {
    loadNavigation() {
      return [
        [
            { linkTo: 'form.definitions', label: 'Definitions' },
        ],
      ];
    },
  },
});
