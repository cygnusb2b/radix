import Product      from 'radix/models/product';
import { fragment } from 'ember-data-model-fragments/attributes';

export default Product.extend({
    frequency : fragment('product-frequency'),
});
