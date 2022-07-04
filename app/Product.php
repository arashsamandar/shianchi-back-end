<?php

namespace App;

use Carbon\Carbon;
use Dingo\Api\Exception\ValidationHttpException;
use Elasticsearch\ClientBuilder;
use Illuminate\Database\Eloquent\Model;
use Elasticquent\ElasticquentTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Prettus\Repository\Contracts\Transformable;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Wego\Helpers\JsonUtil;
use Wego\PictureHandler;

class Product extends Model implements Transformable
{
    use ElasticquentTrait;
    use SoftDeletes;

    const PAGINATE_NUMBER = 15;


    const PRE_CONFIRMATION = 0;
    const CONFIRMED = 1;
    const NOT_CONFIRMED = 2;

    const EXISTS = 1;
    const NOT_EXISTS = 0;

    const PRE_PURCHASE = 0;
    const AVAILABLE = 1;
    const UNAVAILABLE = 2;
    const PURCHASED = 3;

    protected $fillable = [
        'english_name', 'persian_name', 'key_name', 'type', 'made_in', 'description',
        'comment', 'weight', 'current_price', 'second_price', 'key_name', 'wego_coin_need',
        'quantity', 'warranty_name', 'warranty_text', 'store_id', 'category_id', 'confirmation_status',
        'width', 'height', 'length', 'brand_id', 'sale'
    ];

    public function transform()
    {
        return [
            'id' => (int)$this->id,
            'english_name' => $this->english_name,
            'store' => [
                'store_id' => $this->store->id
            ]
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function reports()
    {
        return $this->hasMany('App\Report');
    }

    public function competitor()
    {
        return $this->morphToMany(Competitor::class, 'competitors');
    }

    public function category()
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    public function pictures()
    {
        return $this->hasMany(ProductPicture::class);
    }

    public function prices()
    {
        return $this->hasMany(Price::class);
    }

    public function product_details()
    {
        return $this->hasMany(ProductDetail::class);
    }

    public function setSpecialExpirationAttribute($value)
    {
        $this->attributes['special_expiration'] = Carbon::now()->addDay($value);
    }

    public function order()
    {
        return $this->belongsToMany(Order::class);
    }

    public function special_conditions()
    {
        return $this->hasMany(SpecialCondition::class);
    }

    public function comments()
    {
        return $this->belongsToMany(User::class, 'comments', 'product_id', 'user_id', 'comments')->withPivot('body');
    }

    public function values()
    {
        return $this->belongsToMany(Value::class);
    }

    public function colors()
    {
        return $this->belongsToMany(Color::class);
    }

    public function stalkers()
    {
        return $this->belongsToMany(User::class, 'stalker_user');
    }

    public function rejectionMessages()
    {
        return $this->hasMany('App\RejectionMessage');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function staff()
    {
        return $this->belongsToMany(Staff::class)->withTimestamps();
    }

    public function setQuantityAttribute($value)
    {
        if ($value == 0) {
            $this->attributes['exist_status'] = Product::NOT_EXISTS;
        } else {
            $this->attributes['exist_status'] = Product::EXISTS;
        }
        $this->attributes['quantity'] = $value;
    }

    public function setPersianNameAttribute($value)
    {
        $this->attributes['persian_name'] = str_replace('ي', 'ی', $value);
    }

    public function setKeyNameAttribute($value)
    {
        $this->attributes['key_name'] = str_replace('ي', 'ی', $value);
    }

    public function scopeElastic($query)
    {
        $answer = $query->where('confirmation_status', Product::CONFIRMED)->with(['values' => function ($query) {
            $query->select(['values.id', 'values.name', 'values.specification_id'])->with(['specification' => function ($query) {
                $query->select(['specifications.id', 'specifications.name', 'specifications.important',
                    'specifications.for_buy', 'specifications.is_text_field', 'specifications.multi_value', 'specifications.searchable',
                    'specifications.title_id'])->with('title');
            }]);
        }, 'category' => function ($query) {
            $query->select(['categories.id', 'categories.name', 'categories.persian_name', 'categories.path', 'categories.english_path', 'categories.unit', 'categories.isLeaf']);
        }, 'pictures' => function ($query) {
            $query->select(['product_pictures.id', 'product_pictures.path', 'product_pictures.type', 'product_pictures.product_id']);
        }, 'brand',
            'product_details' => function ($query) {
                $query->with(['special_conditions' => function ($query) {
                    $query->where('status', SpecialCondition::AVAILABLE);
                }, 'store' => function ($query) {
                    $query->with(['user' => function ($query) {
                        $query->select(['users.userable_id', 'users.name']);
                    }])->select(['stores.id']);
                }, 'warranty' => function ($query) {
                    $query->select(['warranties.warranty_name', 'warranties.id']);
                }]);
            }
        ])->get();
        $answer = $answer->each(function ($item) {
            $query = ProductDetail::where('product_id', $item['id'])->get();
            $color_ids = $query->pluck('color_id')->toArray();
            $item['colors'] = Color::whereIn('id', $color_ids)->get();
            $filteredDetail = array_filter($item['product_details']->toArray(), function ($detail) {
                return ($detail['quantity'] > 0);
            });
            $filteredDetail = array_values($filteredDetail);
            foreach ($filteredDetail as &$fDetail) {
                $discount = array_filter($fDetail['special_conditions'], function ($special) {
                    return ($special['type'] == 'discount');
                });
                $discount = array_values($discount);
                $fDetail['second_price'] = $fDetail['current_price'];
                if (!empty($discount)) {
                    $fDetail['second_price'] = $fDetail['current_price'] - $discount[0]['amount'];
                }
            }
            if (empty($filteredDetail)) {
                $item['default_details'] = null;
                $item['exist_status'] = self::NOT_EXISTS;
                $item['quantity'] = 0;
                $item['current_price'] = 0;
                $item['special_conditions'] = [];
            } else {
                $details = null;
                $index = array_search(min(array_column($filteredDetail, 'second_price')), array_column($filteredDetail, 'second_price'));
                $items[] = $filteredDetail[$index];
                if ($filteredDetail[$index]['color_id'] !== null) {
                    $colorId = $filteredDetail[$index]['color_id'];
                    $details = array_filter($item['product_details']->toArray(), function ($detail) use ($colorId) {
                        return ($detail['color_id'] == $colorId);
                    });
                    $details = array_sort($details, function ($value) {
                        return $value['current_price'];
                    });
                    $details = array_values($details);
                } else {
                    $details = array_sort($item['product_details']->toArray(), function ($detail) {
                        return $detail['current_price'];
                    });
                }
                $items = array_merge($items, $details);
                $unique_array = null;
                foreach ($items as $element) {
                    $hash = $element['warranty_id'];
                    if (!isset($unique_array[$hash])) {
                        $unique_array[$hash] = $element;
                    };
                }
                $details = array_values($unique_array);
                $item['default_details'] = $details;
                $item['current_price'] = $filteredDetail[$index]['current_price'];
                $item['quantity'] = $filteredDetail[$index]['quantity'];
                $item['exist_status'] = self::EXISTS;
                $item['special_conditions'] = $filteredDetail[$index]['special_conditions'];
            }
            JsonUtil::removeFields($item, ['product_details']);
        });
        return $answer;
    }

    public function addToElasticSearch($productId)
    {
        Product::where('id', $productId)->elastic()->addToIndex();
    }

    public function ScopeSetToZeroQuantity($query, $productId)
    {
        $product = $query->where('id', $productId)->first();
        $product->quantity = 0;
        $product->save();
        $this->addToElasticSearch($productId);
    }

    public function scopeIncreaseSale($query, $productId, $quantity)
    {
        $product = $query->where('id', '=', $productId)->first();
        $product->sale += $quantity;
        $product->save();
        $this->addToElasticSearch($product->id);
    }

    public function scopeReduceSale($query, $productId, $quantity)
    {
        $product = $query->where('id', '=', $productId)->first();
        $product->sale -= $quantity;
        $product->save();
        $this->addToElasticSearch($product->id);
    }


    public function scopeById($query, $id)
    {
        return $query->findOrFail($id);
    }

    public function scopeByStatus($query, $status)
    {
        return $query
            ->where('confirmation_status', $status)
            ->orderBy('created_at', 'asc')
            ->paginate(self::PAGINATE_NUMBER);
    }

    public function scopeByStore($query, $storeId)
    {

        return $query->where('store_id', $storeId);
    }

    public function scopeByRejectionMessage($query)
    {
        return $query->with(['rejectionMessages' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }]);
    }

    public function scopeByCategoriesByStore($query, $storeId, $categoryId)
    {

        return $query->byStoreDetail($storeId)->whereHas('category', function ($query) use ($categoryId) {
            $query->where('category_id', '=', $categoryId);
        })->byStatus(self::CONFIRMED);
    }

    public function scopeByStoreDetail($query, $storeId)
    {
        $query->with(['pictures' => function ($query) {
            $query->where('type', 0)->select(['id', 'path', 'product_id']);
        }, 'product_details' => function ($query) use ($storeId) {
            $query->where('store_id', $storeId)->select(DB::raw('id,product_id,current_price as min_price'))
                ->orderBy('current_price', 'asc')->with(['special_conditions' => function ($query) {
                    $query->where('type', 'discount')->where('status', SpecialCondition::AVAILABLE);
                }]);
        }])->whereHas('product_details', function ($query) use ($storeId) {
            $query->where('store_id', '=', $storeId);
        });
    }

    public function scopeByDetails($query, $type)
    {
        $query->with(['pictures' => function ($query) use ($type) {
            $query->where('type', 0)->select(['id', 'path', 'product_id']);
        }, 'product_details' => function ($query) use ($type) {
            $query->where(function ($query) use ($type) {
                if ($type == 1) {
                    $query->where('quantity', '>', 0);
                }
            })->with(['special_conditions' => function ($query) {
                $query->where('status', SpecialCondition::AVAILABLE);
            }, 'color', 'warranty',
                'store' => function ($query) {
                    $query->with('user');
                }
            ]);
        }]);
    }

    public function scopeDeleteById($query, $id)
    {
        $client = ClientBuilder::create()->build();
        $param = [
            'type' => 'products', 'index' => 'wego_1',
            'body' => ['query' => ['filtered' => ['filter' => ['terms' => ['id' => [
                $id
            ]]]]]]
        ];
        $client->deleteByQuery($param);
        return $query->findOrFail($id)->delete();
    }

    public function scopeCountByCategory($query, Product $product)
    {
        return $query->where('category_id', $product->category_id)
            ->where('store_id', $product->store_id)
            ->count();
    }

    public function scopeUpdateQuantity($query, Product $product, $newQuantity)
    {
        $product = $query->where('id', $product->id)->firstOrFail();
        $product->quantity = $newQuantity;
        $product->save();
        $this->addToElasticSearch($product->id);

        return true;
    }

    public function scopeUpdatePrice($query, Product $product, $newPrice)
    {
        $product->prices()->create(['prices' => $newPrice]);

        $query->where('id', $product->id)->update(['current_price' => $newPrice]);
        $this->addToElasticSearch($product->id);

        return true;
    }

    public function deleteIndexedProduct($id)
    {
        $product = Product::findOrFail($id);
        $pictures = $product->pictures;
        foreach ($pictures as $picture) {
            (new PictureHandler())->deletePicturesFromFile($product, $picture->path);
        }
        $isRemoved = $this->deleteStoreCategories($product);
        $this->deleteFromElasticSearch($id);
        $product->delete();
        if ($isRemoved) Store::updateElasticSearch($product->store_id);
    }


    /**
     * @param Product $product
     * @return bool
     */
    private function deleteStoreCategories(Product $product)
    {
        $categoryCount = Product::countByCategory($product);
        if ($categoryCount < 2) {
            Store::deleteCategory($product);
            return true;
        }
        return false;
    }


    public static function url($id, $englishName, $persianName)
    {
        $url = str_replace(" ", "-", $englishName) . '-' . str_replace(" ", "-", $persianName);
        $url = preg_replace('~[^\\pL0-9_]+~u', '-', $url);
        $url = trim($url, "-");
        $url = '/product/' . $id . '/' . $url;
        return $url;
    }

    /**
     * @param $id
     */
    private function deleteFromElasticSearch($id)
    {
        $client = ClientBuilder::create()->build();
        $param = [
            'type' => 'products',
            'index' => 'wego_1',
            'id' => $id
        ];
        $client->delete($param);
    }

    public function setToConfirmed($id)
    {
        $product = $this->setConfirmationStatus($id, Product::CONFIRMED);
        $this->addCategoryToStore($product);
        $product->addToElasticSearch($id);
    }

    /**
     * @param $productId
     * @param $status
     * @return mixed
     */
    public function setConfirmationStatus($productId, $status)
    {
        $product = Product::findOrFail($productId);
        $product->confirmation_status = $status;
        $product->save();
        return $product;
    }

    private function addCategoryToStore($product)
    {
        $product->store->categories()->sync([$product->category->id], false);
    }

    public function convertStatusToNumber($status)
    {
        switch ($status) {
            case "Available" :
                return self::AVAILABLE;
            case "Unavailable" :
                return self::UNAVAILABLE;
            case "Purchased" :
                return self::PURCHASED;
            case "PrePurchase" :
                return self::PRE_PURCHASE;
            default:
                throw new InvalidArgumentException('status not applied');
        }
    }

}
