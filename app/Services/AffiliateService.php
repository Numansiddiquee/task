<?php

namespace App\Services;

use App\Exceptions\AffiliateCreateException;
use App\Mail\AffiliateCreated;
use App\Models\Affiliate;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Str;
class AffiliateService
{
    public function __construct(
        protected ApiService $apiService
    ) {}

    /**
     * Create a new affiliate for the merchant with the given commission rate.
     *
     * @param  Merchant $merchant
     * @param  string $email
     * @param  string $name
     * @param  float $commissionRate
     * @return Affiliate
     */
    public function register(Merchant $merchant, string $email, string $name, float $commissionRate): Affiliate
    {
        // TODO: Complete this method
        $user = User::where('email',$email)->first();
        if(!$user){
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt(Str::random(16)), // Generate a random password for the affiliate
                'type' => User::TYPE_AFFILIATE,
            ]);
        }
        

        if (!$user) {
            // If user creation fails, delete the previously created affiliate
            $affiliate->delete();
            throw new AffiliateCreateException('Failed to create affiliate user.');
        }

        $affiliate = Affiliate::create([
            'user_id'    => $user->id,
            'merchant_id' => $merchant->id,
            'commission_rate' => $commissionRate,
        ]);

        if (!$affiliate) {
            throw new AffiliateCreateException('Failed to create affiliate.');
        }

        return $affiliate;

    }
}
