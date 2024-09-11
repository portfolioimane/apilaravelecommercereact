<?php
namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    // Add item to the cart (API version)
    public function addToCart(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = (int) $request->input('quantity', 1);

        if ($quantity <= 0) {
            return response()->json(['error' => 'Quantity must be greater than zero.'], 400);
        }

        $product = Product::findOrFail($productId);

        if (Auth::check()) {
            $userId = Auth::id();
            $cart = $this->getCart($userId);

            $cartItem = CartItem::where('cart_id', $cart->id)
                                 ->where('product_id', $productId)
                                 ->first();

            if ($cartItem) {
                $cartItem->quantity += $quantity;
                $cartItem->save();
            } else {
                CartItem::create([
                    'cart_id' => $cart->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $product->price,
                ]);
            }
        } else {
            return response()->json(['error' => 'User not authenticated.'], 401);
        }

        return response()->json(['message' => 'Product added to cart successfully'], 200);
    }

    // Helper function to get the user's cart
    private function getCart($userId)
    {
        $cart = Cart::where('user_id', $userId)->first();

        if (!$cart) {
            $cart = Cart::create(['user_id' => $userId]);
        }

        return $cart;
    }
}
