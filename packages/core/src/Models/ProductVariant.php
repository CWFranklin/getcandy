<?php

namespace GetCandy\Models;

use GetCandy\Base\BaseModel;
use GetCandy\Base\Casts\AsAttributeData;
use GetCandy\Base\Purchasable;
use GetCandy\Base\Traits\HasDimensions;
use GetCandy\Base\Traits\HasMacros;
use GetCandy\Base\Traits\HasPrices;
use GetCandy\Base\Traits\HasTranslations;
use GetCandy\Base\Traits\LogsActivity;
use GetCandy\Database\Factories\ProductVariantFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ProductVariant extends BaseModel implements Purchasable
{
    use HasFactory;
    use HasPrices;
    use LogsActivity;
    use HasDimensions;
    use HasTranslations;
    use HasMacros;

    /**
     * Define the guarded attributes.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * {@inheritDoc}
     */
    protected $casts = [
        'requires_shipping' => 'bool',
        'attribute_data'    => AsAttributeData::class,
    ];

    /**
     * Return a new factory instance for the model.
     *
     * @return \GetCandy\Database\Factories\ProductVariantFactory
     */
    protected static function newFactory(): ProductVariantFactory
    {
        return ProductVariantFactory::new();
    }

    /**
     * The related product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    /**
     * Return the tax class relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function taxClass()
    {
        return $this->belongsTo(TaxClass::class);
    }

    /**
     * Return the related product option values.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function values()
    {
        $prefix = config('getcandy.database.table_prefix');

        return $this->belongsToMany(
            ProductOptionValue::class,
            "{$prefix}product_option_value_product_variant",
            'variant_id',
            'value_id'
        )->withTimestamps();
    }

    public function getPrices(): Collection
    {
        return $this->prices;
    }

    /**
     * Return the unit quantity for the variant.
     *
     * @return int
     */
    public function getUnitQuantity(): int
    {
        return $this->unit_quantity;
    }

    /**
     * Return the tax class.
     *
     * @return \GetCandy\Models\TaxClass
     */
    public function getTaxClass(): TaxClass
    {
        return $this->taxClass;
    }

    public function getTaxReference()
    {
        return $this->tax_ref;
    }

    /**
     * {@inheritDoc}
     */
    public function getType()
    {
        return $this->shippable ? 'physical' : 'digital';
    }

    /**
     * {@inheritDoc}
     */
    public function isShippable()
    {
        return $this->shippable;
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {
        return $this->product->translateAttribute('name');
    }

    /**
     * {@inheritDoc}
     */
    public function getOption()
    {
        return $this->values->map(fn ($value) => $value->translate('name'))->join(', ');
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions()
    {
        return $this->values->map(fn ($value) => $value->translate('name'));
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentifier()
    {
        return $this->sku;
    }

    public function images()
    {
        $prefix = config('getcandy.database.table_prefix');

        return $this->belongsToMany(Media::class, "{$prefix}media_product_variant")->withPivot('primary')->withTimestamps();
    }

    public function getThumbnail()
    {
        return $this->images()->wherePivot('primary', true)?->first();
    }
}
