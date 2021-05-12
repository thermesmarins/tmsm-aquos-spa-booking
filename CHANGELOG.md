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