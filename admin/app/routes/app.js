import SecureRoute from 'radix/routes/secure';

export default SecureRoute.extend({
  beforeModel({ params: { app: { id } }, targetName }) {
    const appId = this.get('session.data.application._id');
    if (id !== appId) {
      this.transitionTo(targetName, appId);
    }
  }
});
