<?php

namespace App\Providers;


use App\Http\Controllers\CategoryController;
use App\Repositories\AddressRepository;
use App\Repositories\AddressRepositoryEloquent;
use App\Repositories\AuditRepository;
use App\Repositories\AuditRepositoryEloquent;
use App\Repositories\BazaarRepository;
use App\Repositories\BazaarRepositoryEloquent;
use App\Repositories\BrandRepository;
use App\Repositories\BrandRepositoryEloquent;
use App\Repositories\Category\CategoryRepository;
use App\Repositories\Category\CategoryRepositoryEloquent;
use App\Repositories\CommentRepository;
use App\Repositories\CommentRepositoryEloquent;
use App\Repositories\FavoriteRepository;
use App\Repositories\FavoriteRepositoryEloquent;
use App\Repositories\ProductPictureRepository;
use App\Repositories\ProductPictureRepositoryEloquent;
use App\Repositories\ProductRepository;
use App\Repositories\ProductRepositoryEloquent;
use App\Repositories\SpecificationRepository;
use App\Repositories\SpecificationRepositoryEloquent;
use App\Repositories\StorePictureRepository;
use App\Repositories\StorePictureRepositoryEloquent;
use App\Repositories\StoreRepository;
use App\Repositories\StoreRepositoryEloquent;
use App\Repositories\TitleRepository;
use App\Repositories\TitleRepositoryEloquent;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryEloquent;
use App\Repositories\ValueRepository;
use App\Repositories\ValueRepositoryEloquent;
use Dingo\Api\Auth\Provider\JWT;
use Illuminate\Support\ServiceProvider;
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(CategoryRepository::class, CategoryRepositoryEloquent::class);
        $this->app->bind(UserRepository::class, UserRepositoryEloquent::class);
        $this->app->bind(AddressRepository::class, AddressRepositoryEloquent::class);
        $this->app->bind(SpecificationRepository::class, SpecificationRepositoryEloquent::class);
        $this->app->bind(ValueRepository::class, ValueRepositoryEloquent::class);
        $this->app->bind(TitleRepository::class, TitleRepositoryEloquent::class);
        $this->app->bind(BrandRepository::class, BrandRepositoryEloquent::class);
        $this->app->bind(StoreRepository::class, StoreRepositoryEloquent::class);
        $this->app->bind(ProductRepository::class, ProductRepositoryEloquent::class);
        $this->app->bind(AuditRepository::class, AuditRepositoryEloquent::class);
        $this->app->bind(StorePictureRepository::class, StorePictureRepositoryEloquent::class);
        $this->app->bind(ProductPictureRepository::class, ProductPictureRepositoryEloquent::class);
        $this->app->bind(FavoriteRepository::class, FavoriteRepositoryEloquent::class);
        $this->app->bind(BazaarRepository::class, BazaarRepositoryEloquent::class);
        $this->app->bind(CommentRepository::class, CommentRepositoryEloquent::class);
    }
}
