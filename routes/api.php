<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\SubCategoryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\WishlistController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('changeLanguage')->group(
    function () {
        //api get all categories this is when the user open the app
        Route::get('getCategory/{index?}/{limit?}', [CategoryController::class, 'getCategory']);

        //api get all sub categories this is when the user select a category we get the id from the frontend and get all subcategory from that id
        Route::get('getSubCategory/{id}/{index?}/{limit?}', [CategoryController::class, 'getSubCategory']);

        //api get all product this is when the user select a sub category we get the id from frontend and get all product of that sub category
        Route::get('getProduct/{id}/{index?}/{limit?}', [SubCategoryController::class, 'getProduct']);

        //api get all product this is when the user select a category we get the id from frontend and get all product of that category
        Route::get('getProductCategory/{id}/{index?}/{limit?}', [SubCategoryController::class, 'getProductCategory']);

        //api get user address this is when the user select address tab we get the id from frontend and get the address of that user
        Route::get('getAddress/{id}', [AddressController::class, 'getAddress']);

        //api to create address for user this is when the user enter all info and click save button we get the info from frontend and save the address of that user on db
        Route::post('createAddress', [AddressController::class, 'addAddress']);

        //api get user address this is when the user select address tab we get the id from frontend and get the address of that user
        Route::put('updateAddress/{id}', [AddressController::class, 'editAddress']);

        //api to delete user address this is when the user select remove button we get the id from frontend and delete the address of that user
        Route::delete('deleteAddress/{id}', [AddressController::class, 'deleteAddress']);

        //api to login user this is when the user enter his email and password we get it from frontend and send user info
        Route::get('login', [Controller::class, 'login']);

        //api to create user this is when the user enter email password etc and click signUp button we get the info from frontend and save the user info on db
        Route::post('signUp', [Controller::class, 'signUp']);

        //api to update user info this is when the user click on update button we get the info from frontend and update user info in our db
        Route::put('updateUser/{id}', [Controller::class, 'update']);

        //api to delete user this is when the user select delete button we get the id from frontend and delete the user
        Route::delete('deleteUser/{id}', [Controller::class, 'delete']);

        //api to get all bills for user when he click on my bills we get the id from the frontend and get the bills
        Route::get('getBill/{id}', [BillController::class, 'getBill']);

        //api to get all product of a specific bill for user when he click on my bills and then choose a bill to see what he ordered we get the id from the frontend and get the products
        Route::get('getBillProduct/{billID}', [BillController::class, 'getBillProduct']);

        //api to make bill we do this by getting the id of the cart and user from front end when user click on checkout then we save it in the table  
        Route::post('addBill/{id}', [BillController::class, 'addBill']);

        //api to cancel the bill we do this by checking if the time of the bill is over 15 min if it's over 15 min user can't cancel and if it's not over 15 min user can cancel the bill
        Route::put('cancelBill/{id}', [BillController::class, 'cancelBill']);

        //api to get all product that user added to his wishlist we get the id from the front end when user click on my wishlist button
        Route::get('getWishlist/{id}', [WishlistController::class, 'getWishlist']);

        //api to add product to wishlist of user we get the id of user and product from front end then we check if user have a wishlist or not then we also check if product is already in wishlist
        Route::post('addWishlist', [WishlistController::class, 'addToWishlist']);

        //api to get the cart for a user we get the id from the front end and get the last cart created for that user with the products and their quantity
        Route::get('getCart/{id}', [CartController::class, 'getCart']);

        //api to add product to user cart we get the user id and product id and quantity from front end then we make a new cart with the product user choose and quantity
        Route::post('addCart', [CartController::class, 'addCart']);

        //api to delete user cart we get the id from the front end then we delete the cart
        Route::delete('deleteCart/{id}', [CartController::class, 'deleteCart']);

        //api to update the cart this is when user want to add new product or change the quantity we get the cart id and product id and quantity form front end and update the cart
        Route::put('updateCart', [CartController::class, 'updateCart']);

        //api to update the cart this is when user want to remove the product from cart we get the cart id and product id and quantity form front end and update the cart by removing the product
        Route::put('removeProductFromCart', [CartController::class, 'removeProduct']);

        //api to add translation to category and sub category php file which store the translation 
        Route::post('translation', [Controller::class, 'translation']);

        //api to add translation to product php file which store the translation
        Route::post('translationProduct', [Controller::class, 'translationProduct']);
    }
);
