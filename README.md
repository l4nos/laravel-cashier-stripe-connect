<h1 align=center>
	laravel-cashier-stripe-connect
</h1>
 
<p align="center">
	<a href="https://packagist.org/packages/expdev07/laravel-cashier-stripe-connect"><img src="https://img.shields.io/packagist/dt/expdev07/laravel-cashier-stripe-connect" alt="Total Downloads"></a>
	<a href="https://packagist.org/packages/expdev07/laravel-cashier-stripe-connect"><img src="https://img.shields.io/packagist/v/expdev07/laravel-cashier-stripe-connect" alt="Latest Stable Version"></a>
	<a href="https://packagist.org/packages/expdev07/laravel-cashier-stripe-connect"><img src="https://img.shields.io/packagist/l/expdev07/laravel-cashier-stripe-connect" alt="License"></a>
</p>

<p>
    <a href='https://ko-fi.com/C1C510DUQ' target='_blank'>
	<img height='36' style='border:0px;height:36px;' src='https://az743702.vo.msecnd.net/cdn/kofi3.png?v=2' border='0' alt='Buy Me a Coffee at ko-fi.com' />
    </a>
</p>

> Originally this package only supported adding ConnectBillable to the User Model. This has now been changed so you can add it to any model and even transfer funds between different models. This packagr also now supports UUID primary key's on billable models.

> 💲 Adds Stripe Connect functionality to Laravel's main billing package, Cashier. Simply works as a drop-in on top of Cashier, with no extra configuration.

## Installation for Single Tenancy Applications

1. Enable Stripe Connect in your [dashboard settings](https://dashboard.stripe.com/settings).
2. Install Cashier: ``composer require laravel/cashier``.
3. Install package: ``composer require expdev07/laravel-cashier-stripe-connect``.
4. Publish migrations ``php artisan vendor:publish --tag=cashier-connect-migrations``.
5. Run migrations: ``php artisan migrate``.
6. Configure Stripe keys for Cashier: [Cashier Docs](https://laravel.com/docs/9.x/billing#api-keys).

**Note:** the package will not work as intended if you do not install [Laravel's official Cashier package](https://laravel.com/docs/8.x/billing) first.

## Installation For Multi Tenancy Applications (Using Tenancy For Laravel (V3) )

1. Enable Stripe Connect in your [dashboard settings](https://dashboard.stripe.com/settings).
2. Install Cashier: ``composer require laravel/cashier``.
3. Install package: ``composer require expdev07/laravel-cashier-stripe-connect``.
4. Publish tenant migrations ``php artisan vendor:publish --tag=cashier-connect-tenancy-migrations``.
5. Run tenant migrations : ``php artisan tenants:migrate``
6. Configure Stripe keys for Cashier: [Cashier Docs](https://laravel.com/docs/9.x/billing#api-keys).

## Use

The library builds on the official [Cashier](https://laravel.com/docs/8.x/billing) library, so getting up and started is a breeze.

### Setup model

Add the ``Billable`` traits to your model. You can use them individually or together. You can also create your own ``Billable`` trait and put them together there. In 
addition, the model should also implement the ``StripeAccount`` interface.

```php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Lanos\CashierConnect\Contracts\StripeAccount;
use Laravel\Cashier\Billable as CashierBillable;
use Lanos\CashierConnect\Billable as ConnectBillable;

class User extends Authenticatable implements StripeAccount
{
    use CashierBillable;
    use ConnectBillable;

    ///

}
```

### Create controller

Create a controller to manage on-boarding process. The example below registers an Express account for the user.

```php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use URL;

class StripeController extends Controller
{

    /**
     * Creates an onboarding link and redirects the user there.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function board(Request $request): RedirectResponse
    {
        return $this->handleBoardingRedirect($request->user());
    }

    /**
     * Handles returning from completing the onboarding process.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function returning(Request $request): RedirectResponse
    {
        return $this->handleBoardingRedirect($request->user());
    }

    /**
     * Handles refreshing of onboarding process.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function refresh(Request $request): RedirectResponse
    {
        return $this->handleBoardingRedirect($request->user());
    }

    /**
     * Handles the redirection logic of Stripe onboarding for the given user. Will 
     * create account and redirect user to onboarding process or redirect to account 
     * dashboard if they have already completed the process.
     *
     * @param User $user
     * @return RedirectResponse
     */
    private function handleBoardingRedirect(User $user): RedirectResponse
    {
        // Redirect to dashboard if onboarding is already completed.
        if ($user->hasStripeAccountId() && $user->hasCompletedOnboarding()) {
            return $user->redirectToAccountDashboard();
        }

        // Delete account if already exists and create new express account with 
        // weekly payouts.
        $user->deleteAndCreateStripeAccount('express', [
            'settings' => [
                'payouts' => [ 
                    'schedule' => [ 
                        'interval' => 'weekly', 
                        'weekly_anchor' => 'friday',
                    ]
                ]
            ]
        ]);

        // Redirect to Stripe account onboarding, with return and refresh url, otherwise.
        return $user->redirectToAccountOnboarding(
            URL::to('/api/stripe/return?api_token=' . $user->api_token),
            URL::to('/api/stripe/refresh?api_token=' . $user->api_token)
        );
    }

}
```

## Example

```php
// Get user. This user has added the Billable trait and implements StripeAccount.
$user = User::query()->find(1);

// Transfer 10 USD to the user.
$user->transferToStripeAccount(1000);

// Payout 5 dollars to the user's bank account, which will arrive in 1 week.
$user->payoutStripeAccount(500, Date::now()->addWeek());

```

## UUID Usage

Some people prefer to use Ordered UUID's, this has become more common since Laravel 9. The package will automatically detect if you are using a non integer primary key on your model, as long as you have the following property set on your model it should work fine.

```php
public $incrementing = false;
```

## Custom Primary Key Usage

This package will correctly recognise your custom primary key, as long as you use the following correctly.

```php
protected $primaryKey = 'your_primary_key';
```

## License

Please refer to [LICENSE.md](https://github.com/Lanos/laravel-cashier-stripe-connect/blob/main/LICENSE) for this project's license.

## Contributors

This list only contains some of the most notable contributors. For the full list, refer to [GitHub's contributors graph](https://github.com/Lanos/laravel-cashier-stripe-connect/graphs/contributors).
* Lanos (Marius) - creator and maintainer.
* Haytam Bakouane [(hbakouane)](https://github.com/hbakouane) - contributor.
* Robert Lane

## Thanks to

[Taylor Otwell](https://twitter.com/taylorotwell) for his amazing framework and [all the contributors of Cashier](https://github.com/laravel/cashier-stripe/graphs/contributors).
