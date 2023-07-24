<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;
use App\Exceptions\AffiliateCreateException;
    
class OrderService
{ 
  
    protected $affiliateService;

    public function __construct(AffiliateService $affiliateService)
    {
        $this->affiliateService = $affiliateService;
    }

    /**
     * Process an order and log any commissions.
     * This should create a new affiliate if the customer_email is not already associated with one.
     * This method should also ignore duplicates based on order_id.
     *
     * @param  array{order_id: string, subtotal_price: float, merchant_domain: string, discount_code: string, customer_email: string, customer_name: string} $data
     * @return void
     */
    public function processOrder(array $data)
    {
        // TODO: Complete this method
        $merchant = Merchant::where('domain', $data['merchant_domain'])->first();
        
        if (!$merchant) {
            // Merchant not found, do not process the order
            return;
        }

        // Check if an affiliate with the customer email exists, if not, create a new affiliate
        $affiliate = Order::where('customer_email', $data['customer_email'])->first();
        
        if (!$affiliate) {
            // If the affiliate does not exist, register a new affiliate
            // $affiliate = $this->affiliateService->register($merchant, $data['customer_email'], $data['customer_name'], $merchant->default_commission_rate);
            
            $user = User::create([
                'name' => $data['customer_name'],
                'email' => $data['customer_email'],
                'password' => bcrypt(Str::random(16)), // Generate a random password for the affiliate
                'type' => User::TYPE_AFFILIATE,
            ]);

            if (!$user) {
                // If user creation fails, delete the previously created affiliate
                $affiliate->delete();
                throw new AffiliateCreateException('Failed to create affiliate user.');
            }

            $affiliate = Affiliate::create([
                'user_id'    => $user->id,
                'merchant_id' => $merchant->id,
                'commission_rate' => $merchant->default_commission_rate,
            ]);

            if (!$affiliate) {
                throw new AffiliateCreateException('Failed to create affiliate.');
            }

        }
        
        // Check if the order already exists based on order_id, if not, create a new order
        $order = Order::where('external_order_id', $data['order_id'])->first();

        if (!$order) {
            $order = Order::create([
                        'external_order_id' => $data['order_id'],
                        'subtotal' => $data['subtotal_price'],
                        'affiliate_id' => $affiliate->id,
                        'merchant_id' => $merchant->id,
                        'discount_code' => $data['discount_code'],
                        'customer_email' => $data['customer_email'],
                    ]);

            // Log any commissions or perform other order processing tasks as needed
        }

        print($order);
    }
}
