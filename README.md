<h1 align=center>
	Cashier For Connect
</h1>

## Cashier 15 Update Notes
The Cashier 15 update brought about a few changes to the package. These include:

- Migrations no longer auto publish, you must now publish them using the command stated in the GitBook readme.
  - Any use of ignoreMigrations() in your code can be and should be safely removed
- Stripe API Version is now 2023-10-16, changes have been made to accommodate this

## Intro

This package is designed to seamlessly connect all of your eloquent models, mapping them to the relevant stripe entities in order to make a marketplace or payments platform.

## Documentation

We now have a dedicated docs page for this plugin. You can view it [here](https://updev-1.gitbook.io/cashier-for-connect/).

We now roughly support webhooks (Due to flexible nature of connect, you will need to declare handlers yourself) - Follow our guide!

## License

Please refer to [LICENSE.md](https://github.com/l4nos/laravel-cashier-stripe-connect/blob/main/LICENSE) for this project's license.

## Contributors

This list only contains some of the most notable contributors. For the full list, refer to [GitHub's contributors graph](https://github.com/l4nos/laravel-cashier-stripe-connect/graphs/contributors).
* ExpDev07 [(Marius)](https://github.com/ExpDev07) - Creator of the original package
* Haytam Bakouane [(hbakouane)](https://github.com/hbakouane) - Contributor to original package.
* Robert Lane (Me) - Creator of the new package

## Thanks to

[Taylor Otwell](https://twitter.com/taylorotwell) for his amazing framework and [all the contributors of Cashier](https://github.com/laravel/cashier-stripe/graphs/contributors).
