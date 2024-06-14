### 2.2.25 June 14th, 2024
* New hardcoded prices (balneo duo paris)

### 2.2.24 May 02nd, 2024
* Add new text for gift voucher

### 2.2.22: January 17th, 2024
* New hardcoded prices (aquatonic spa)

### 2.2.21: January 10th, 2024
* Change Accomodation price for Rennes

### 2.2.20: December 14th, 2023
* Add possibility to do the message and set the end of display, for treatments prices annual augmentation, in the reservation path

### 2.2.19: November 6th, 2023
* Add new message in aquos for 'parcours aquatonic' in customers notes.
* 
### 2.2.14 August 14th, 2023
* Update min.js file for production

### 2.2.13 August 14th, 2023
* Added new message to show up base on backoffice's parameters for the prices updates for next year.

### 2.2.12 April 11th,2023
* Disable the checkout button in cart, if more than one appointment booked.

### 2.2.11 April 3rd, 2023
* Check if new Aquos prices array matches discounted total price, accounting for rounding errors

### 2.2.10 April 3rd, 2023
* Update file

### 2.2.9 April 3rd, 2023
* New error message for more than 1 appointment in cart with the phone number in case the customer wants more information

### 2.2.8 March 31th, 2023
* Forced path to cart before validation order
* Traduction to French notice in cart

### 2.2.7 January 9th, 2023
* Fix var type in get_product_price_from_aquos_or_product_id
* Loading icon with Font Awesome instead of Glyphicon
* Handle product with choice and product attribute at the same time
* New hardcoded prices for 2023

### 2.2.6: July 27th, 2022
* Link to booking page displayed as a well
* Fix preselect product from URL, when product has choices

### 2.2.5: July 26th, 2022
* Link from product page to booking page

### 2.2.4: July 25th, 2022
* Fix preselect product from URL

### 2.2.3: July 25th, 2022
* Fix removing Moment tests

### 2.2.2: July 25th, 2022
* Allow product ID in URL (after hashtag, example #33605) to pre-select the product in the product list

### 2.2.1: June 9th, 2022
* When customer doesn't have a voucher, display the price anyway

### 2.2.0: April 4th, 2022
* Convert "Days Date From" to "Hours Date From" to allow users to book a spa within 2 hours

### 2.1.9: April 4th, 2022
* Hardcoded price for Rennes: 80>82 for Oceania
* Enable again timestamp for appointments to expire them after 2 hours
* Fix product not added to cart when product is not published

### 2.1.8: February 9th, 2022
* Non-matching Aquos price: add user info to email
* Hardcoded price for Paris: 31>30 for EBE
* Hardcoded ID for modelage dos accueil Nantes 

### 2.1.7: January 31st, 2022
* Non-matching Aquos price: display warning in variation tabs
* Non-matching Aquos price: exclude non existing products

### 2.1.6: January 26th, 2022
* Fix scrolling after selecting a product
* Hardcoded price for Paris: 30>31 for Aquatonic, 30>31 for EBE
* Booking form style: reduce number of products shown (16) at same time in bootstrap select

### 2.1.5: January 17th, 2022
* Bootstrap Select liveSearchPlaceholder customization
* Fix HTML tag not closed correctly

### 2.1.4: January 5th, 2022
* Booking form style: previous/next buttons
* Booking form style: loading times with glyphicon
* Booking form style: center confirm button

### 2.1.3: January 5th, 2022
* Aquos price generation: only consider published products (and variations of published variable products)
* Add to cart validation: aquos price must match product/variation price
* Add to cart validation: aquos ID must be set

### 2.1.2: January 3rd, 2022
* Hardcoded price for Rennes: 20>21 for Aquatonic

### 2.1.1: January 3rd, 2022
* Hardcoded price for Nantes: 23>24 for Aquatonic

### 2.1.0: December 20th, 2021
* Filter published products when "I don't have a voucher"
* CSS optimisation

### 2.0.9: December 20th, 2021
* Fix "Case 2" selection

### 2.0.8: December 9th, 2021
* Removed "product category" selection
* Display products with bootstrap select and live search enabled, sorted by sub category of main booking category

### 2.0.7: December 6th, 2021
* Fix bug preventing appointment of treatment with only voucher as product attribute

### 2.0.6: November 3rd, 2021
* Check Aquos ID when empty

### 2.0.5: October 1st, 2021
* Fix bug erasing _aquos_id after seeing variations

### 2.0.4: September 21st, 2021
* Check cart items can't contain products and appointments

### 2.0.3: September 6th, 2021
* wp_remote_post timeout now 10 seconds (instead of default 5 seconds)
* Check WooCommerce is activated

### 2.0.2: August 30th, 2021
* Fix logs
* Fix Aquos site ID check

### 2.0.1: August 30th, 2021
* Update translations

### 2.0.0: August 30th, 2021
* Web services now called with POST instead of GET

### 1.7.8: August 25th, 2021
* Fix wrong setting variable

### 1.7.7: August 24th, 2021
* Remove slashes in birthdate format
* Debug order line item doesn't have price or id

### 1.7.6: July 20th, 2021
* Curl limit timeout to 10 seconds

### 1.7.5: July 2nd, 2021
* Optimization preventing passing many days pages too fast (possibly causing server load)

### 1.7.4: June 15th, 2021
* Fix error message was absent after removing error email
* Removed Datepicker scripts from JS queue
* Fix error message was absent in some cases

### 1.7.3: June 11th, 2021
* Update pricing

### 1.7.2: May 19th, 2021
* Delay sending the message/error appointment email

### 1.7.1: May 17th, 2021
* Disabled the old appointment error email 

### 1.7.0: May 12th, 2021
* New admin email "Appointment Message" sent when appointment has failed or when the customer leaves a customer note
* Appointments emails gets the status of the appointment: it says "Appointment error" when the web service gives an error

### 1.6.1: May 11th, 2021
* Prevent appointments at same date and time to be added twice to cart

### 1.6.0: May 4th, 2021
* Check if WC cart exists
* Fix product get_type() incorrectly called
* Fix WC->cart undefined
* Fix $el.content for WordPress 5.7
* New setting for enabling course booking integration

### 1.5.4: February 23nd, 2021
* Remove web service urls called in error emails

### 1.5.3: February 22nd, 2021
* Better trace Web Service calls

### 1.5.2: February 17th, 2021
* Explicit description of _aquos_id field in some cases of products without choice and with option like course
* Trace Web Service calls

### 1.5.1: December 21st, 2020
* Sanitize 3 consecutive dot characters when sent to Web Service (had to enable again single dots for emails)
* Fix PHP notice
* Fix email headers

### 1.5.0: December 16th, 2020
* Alert when Aquos ID field is empty in a variation
* Cron action to regenerate prices and check price consistency and missing aquos ids, executed daily at midnight

### 1.4.0: December 15th, 2020
* Tool to see if products miss the _aquos_id field

### 1.3.9: December 14th, 2020
* Sanitize plus characters when sent to Web Service

### 1.3.8: November 25rd, 2020
* Handling of payment gateways "onsite" or "cod" in different conditions
* When using coupon on checkout, prices (aquos_price) need to reflect the discount in the item meta data

### 1.3.7: November 23rd, 2020
* Remove Cash on Delivery for other types of orders when shipping is not needed
* Create delay between 2 web service calls when adding appointment 
* If products are virtual, do not allow delivery or on site payments
* Consider new "Payment On Site" gateway
* Debug more on Unknown Error
* Sanitize dot characters when sent to Web Service

### 1.3.6: November 5th, 2020
* Fix Appointment Processed value was No instead of Yes in backend 
* New setting for Order Gift Wrap Aquos ID 
* New settings page to calculate product meta "aquos_price" which is detailed pricing of a product with variations 

### 1.3.5: November 5th, 2020
* Handle special hard coded prices
* Update translations

### 1.3.4: November 5th, 2020
* Check cart items if any appointment has expired on appointment form
* New field _aquos_price needed to pass exact price to web service
* New settings page to generate prices needed for web service synchonization

### 1.3.3: October 30th, 2020
* Check if appointments have expired, if expired the order can't be processed

### 1.3.2: October 29th, 2020
* Fix styling on mobile
* Sanitize values sent to Web Service
* Changed expire hours to expire minutes

### 1.3.1: October 28th, 2020
* Load JS only if #tmsm-aquos-spa-booking-form is present in the page
* New date setting datebeforeforbidden to forbid date selection before this date

### 1.3.0: October 27th, 2020
* Weekdays view optimizations, button placement, alignment, scrollto, button appearance

### 1.2.9: October 19th, 2020
* Remove commas when calling the web service endpoint
* Do not execute frontend javascript in backend

### 1.2.8: October 14th, 2020
* Fix dates in week view were not listed properly (3 by 3)

### 1.2.7: October 13th, 2020
* Fix OptinMonster momentjs conflict
* WeekDay selecting time adds a disabled state
* Fix missing momentjs dependency
* Uglify public js file and remove logs

### 1.2.6: October 9th, 2020
* New date view by week days
* New setting: date selection (calendar or week days)

### 1.2.5: October 1st, 2020
* New setting: contact page that is clickable when no time slot is available

### 1.2.4: September 9th, 2020
* Fix "Your appointment was added" translation
* Hide "Cancel" button
* Remove CSS radio to checkbox override
* Add "Bookable" checkbox for products
* Filter frontend products, display only products with _bookable meta key = yes

### 1.2.3: September 4th, 2020
* Remove product count in product list
* Fix ignored products are separated by comma, not + sign

### 1.2.2: August 3rd, 2020
* Fix Info screen product counter
* Checkout notice for customers willing to add another appointment

### 1.2.1: July 31st, 2020
* Renamed fields for better understanding
* Info screen now has two parts: publish products and draft products

### 1.2.0: July 29th, 2020
* Info screen now displays draft products
* Update FR translation

### 1.1.9: July 29th, 2020
* Settings screen containing all products with an empty Aquos ID

### 1.1.8: July 28th, 2020
* Plugin files for icon and banner 
* Update FR translation
* Check $contact_page_id is defined
* Save Aquos ID in order data for regular sale process (not appointments)

### 1.1.7: July 28th, 2020
* New setting field to specify excluded WooCommerce product categories

### 1.1.6: July 27th, 2020
* New setting field to specify ignored products
* Exclude ignored product on get times web service
* Exclude ignored product on insert appointment web service

### 1.1.5: July 16th, 2020
* Stop encoding apostrophes before sending to web service

### 1.1.4: July 15th, 2020
* CSS optimization for mobile
* Handle no product ID before calling web service

### 1.1.3: July 15th, 2020
* Remove unused params
* Remove regular price when on sale

### 1.1.2: July 15th, 2020
* Option choice was displayed even if there was only voucher attribute

### 1.1.1: July 9th, 2020
* Code refactoring
* Code formatting
* Change of plans: do not show non-priorities for now

### 1.1.0: July 2nd, 2020
* Hide Aquos Product ID in frontend
* Allow translation of Appointments in statuses navigation
* Fix sorting times
* Randomize "not priority" times

### 1.0.9: July 2nd, 2020
* Add all variation data attributes when adding to cart, allows the customer to see the complete product information
* Remove debug test

### 1.0.8: July 1st, 2020
* Display from price only for variable products
* Display price contained in attribute description (only if no voucher)
* Rename "Your order" by "Your appointments" when appointment only
* Rename order received page title
* Sort product categories by manual order

### 1.0.7: June 30th, 2020
* Fix checking cart contents when on "order-pay" page

### 1.0.6: June 9th, 2020
* Check WooCommerce cart exists before calling it

### 1.0.5: June 8th, 2020
* Check if appointment confirmation email was sent

### 1.0.4: June 8th, 2020
* Add debug variable for development

### 1.0.3: June 8th, 2020
* Fix version number

### 1.0.2: June 8th, 2020
* Remove debug log

### 1.0.1: June 8th, 2020
* Cart count of appointments was wrong

### 1.0.0: August 6th, 2018
* Plugin boilerplate