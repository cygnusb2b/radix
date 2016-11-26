import Product      from 'radix/models/product';
import { fragment } from 'model-fragments/attributes';

export default Product.extend({
    frequency : fragment('product-frequency'),
});
