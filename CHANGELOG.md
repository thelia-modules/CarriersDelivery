# 1.0.1

- Fix the back-office pages answering 500: controllers and forms are now registered as services
- Fix the product edit page crashing while the module is active (legacy string form types)
- Fix forms on Thelia 3.2: static form names, form type classes, constraints with named arguments
- Fix redirects after saving, the packing cost save buttons and the product carrier choices
- Send the CSRF token in the request body
- Offer the computed postage as a delivery option in the checkout
- Declare the admin routes with Route attributes instead of Config/routing.xml
- Log the postage computation only when enabled in the configuration, to var/log/carriersdelivery.log
- Skip the GeoNames lookup when no username is configured, and time it out after 3 seconds
- Leave the module out of the checkout instead of failing when no rate covers the cart weight

# 1.0

- Initial release
