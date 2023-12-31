<?php

namespace App\Services;

use App\Jobs\PayoutOrderJob;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Carbon;

class MerchantService
{
    /**
     * Register a new user and associated merchant.
     * Hint: Use the password field to store the API key.
     * Hint: Be sure to set the correct user type according to the constants in the User model.
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return Merchant
     */
    public function register(array $data): Merchant
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['api_key'],
            'type' => User::TYPE_MERCHANT,
        ]);

        $merchant = Merchant::create([
            'domain' => $data['domain'],
            'display_name' => $data['name'],
            'turn_customers_into_affiliates' => false,
            'default_commission_rate' => 0.0,
            'user_id' => $user->id,
        ]);

        return $merchant;

    }

    /**
     * Update the user
     *
     * @param array{domain: string, name: string, email: string, api_key: string} $data
     * @return void
     */
    public function updateMerchant(User $user, array $data)
    {
        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['api_key']),
        ]);

        $merchant = $user->merchant;
        $merchant->update([
            'domain' => $data['domain'],
            'display_name' => $data['name'],
        ]);
    }

    /**
     * Find a merchant by their email.
     * Hint: You'll need to look up the user first.
     *
     * @param string $email
     * @return Merchant|null
     */
    public function findMerchantByEmail(string $email): ?Merchant
    {
        $user = User::where('email', $email)->first();

        return $user ? $user->merchant : null;
    }

    /**
     * Pay out all of an affiliate's orders.
     * Hint: You'll need to dispatch the job for each unpaid order.
     *
     * @param Affiliate $affiliate
     * @return void
     */
    
    public function payout(Affiliate $affiliate)
    {
        // TODO: Complete this method
        $unpaidOrders = Order::where('affiliate_id', $affiliate->id)
            ->where('payout_status', Order::STATUS_UNPAID)
            ->get();

        foreach ($unpaidOrders as $order) {
            dispatch(new PayoutOrderJob($order));
        }
    }

    public function getOrderStats(Carbon $fromDate, Carbon $toDate): array
    {
        $ordersInRange = Order::whereBetween('created_at', [$fromDate, $toDate])->get();
        $totalOrderCount = $ordersInRange->count();
        $commissionOwed = $ordersInRange->whereNotNull('affiliate_id')->sum('affiliate_commission');
        $revenue = $ordersInRange->sum('subtotal');

        return [
            'count' => $totalOrderCount,
            'commissions_owed' => $commissionOwed,
            'revenue' => $revenue,
        ];
    }
}
