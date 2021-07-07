<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Product functions
 *
 * @package    local_intellicart
 * @author     SEBALE
 * @copyright  2018 SEBALE
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_intellicart\privacy;

defined('MOODLE_INTERNAL') || die();

use \core_privacy\local\metadata\collection;
use \core_privacy\local\request\approved_contextlist;
use \core_privacy\local\request\contextlist;
use \core_privacy\local\request\transform;
use \core_privacy\local\request\writer;
use \core_privacy\local\request\user_preference_provider;
use local_intellicart\helpers\DBHelper;

if (interface_exists('\core_privacy\local\request\userlist')) {
    interface ic_userlist extends \core_privacy\local\request\userlist{}
} else {
    interface ic_userlist {};
}
if (interface_exists('\core_privacy\local\request\approved_userlist')) {
    interface ic_approved_userlist extends \core_privacy\local\request\approved_userlist{}
} else {
    interface ic_approved_userlist {};
}
if (interface_exists('\core_privacy\local\request\core_userlist_provider')) {
    interface ic_userlist_provider extends \core_privacy\local\request\core_userlist_provider{}
} else {
    interface ic_userlist_provider {};
}


/**
 * Privacy class for requesting user data.
 *
 * @copyright  2018 Sara Arjona <sara@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider,
        \core_privacy\local\request\user_preference_provider,
        ic_userlist,
        ic_approved_userlist,
        ic_userlist_provider {

    /**
     * Return the fields which contain personal data.
     *
     * @param   collection $collection The initialised collection to add items to.
     * @return  collection A listing of user data stored through this system.
     */
    public static function get_metadata(collection $collection) : collection {

        // Paypal payment.
        $collection->add_external_location_link(
            'paypal.com',
            [
                'os0'        => 'privacy:metadata:local_intellicart:paypal_com:os0',
                'custom'     => 'privacy:metadata:local_intellicart:paypal_com:custom',
                'first_name' => 'privacy:metadata:local_intellicart:paypal_com:first_name',
                'last_name'  => 'privacy:metadata:local_intellicart:paypal_com:last_name',
                'address'    => 'privacy:metadata:local_intellicart:paypal_com:address',
                'city'       => 'privacy:metadata:local_intellicart:paypal_com:city',
                'email'      => 'privacy:metadata:local_intellicart:paypal_com:email',
                'country'    => 'privacy:metadata:local_intellicart:paypal_com:country',
            ],
            'privacy:metadata:local_intellicart:paypal_com'
        );

        // Stripe payment.
        $collection->add_external_location_link(
            'stripe.com',
            [
                'os0'        => 'privacy:metadata:local_intellicart:stripe_com:os0',
                'custom'     => 'privacy:metadata:local_intellicart:stripe_com:custom',
                'first_name' => 'privacy:metadata:local_intellicart:stripe_com:first_name',
                'last_name'  => 'privacy:metadata:local_intellicart:stripe_com:last_name',
                'address'    => 'privacy:metadata:local_intellicart:stripe_com:address',
                'city'       => 'privacy:metadata:local_intellicart:stripe_com:city',
                'email'      => 'privacy:metadata:local_intellicart:stripe_com:email',
                'country'    => 'privacy:metadata:local_intellicart:stripe_com:country',
            ],
            'privacy:metadata:local_intellicart:stripe_com'
        );

        // Twocheckout payment.
        $collection->add_external_location_link(
            '2checkout.com',
            [
                'os0'        => 'privacy:metadata:local_intellicart:twocheckout_com:os0',
                'custom'     => 'privacy:metadata:local_intellicart:twocheckout_com:custom',
                'first_name' => 'privacy:metadata:local_intellicart:twocheckout_com:first_name',
                'last_name'  => 'privacy:metadata:local_intellicart:twocheckout_com:last_name',
                'street_address'    => 'privacy:metadata:local_intellicart:twocheckout_com:street_address',
                'city'       => 'privacy:metadata:local_intellicart:twocheckout_com:city',
                'email'      => 'privacy:metadata:local_intellicart:twocheckout_com:email',
                'country'    => 'privacy:metadata:local_intellicart:twocheckout_com:country',
            ],
            'privacy:metadata:local_intellicart:twocheckout_com'
        );

        // Authorize.net payment.
        $collection->add_external_location_link(
            'authorize.net',
            [
                'x_cust_id'     => 'privacy:metadata:local_intellicart:authorize_net:x_cust_id',
            ],
            'privacy:metadata:local_intellicart:authorize_net'
        );

        // Payu payment.
        $collection->add_external_location_link(
            'corporate.payu.com',
            [
                'merchantId'        => 'privacy:metadata:local_intellicart:corporate_payu_com:merchantid',
                'accountId'         => 'privacy:metadata:local_intellicart:corporate_payu_com:accountid',
                'referenceCode'     => 'privacy:metadata:local_intellicart:corporate_payu_com:referencecode',
                'extra1'            => 'privacy:metadata:local_intellicart:corporate_payu_com:extra1',
                'buyerEmail'        => 'privacy:metadata:local_intellicart:corporate_payu_com:buyeremail',
                'buyerFullName'     => 'privacy:metadata:local_intellicart:corporate_payu_com:buyerfullname',
                'billingAddress'    => 'privacy:metadata:local_intellicart:corporate_payu_com:billingaddress',
                'billingCity'       => 'privacy:metadata:local_intellicart:corporate_payu_com:billingcity',
                'billingCountry'    => 'privacy:metadata:local_intellicart:corporate_payu_com:billingcountry',
            ],
            'privacy:metadata:local_intellicart:corporate_payu_com'
        );

        // Cybersource payment.
        $collection->add_external_location_link(
            'cybersource.com',
            [
                'access_key'                => 'privacy:metadata:local_intellicart:cybersource_com:access_key',
                'profile_id'                => 'privacy:metadata:local_intellicart:cybersource_com:profile_id',
                'reference_number'          => 'privacy:metadata:local_intellicart:cybersource_com:reference_number',
                'locale'                    => 'privacy:metadata:local_intellicart:cybersource_com:locale',
                'bill_to_forename'          => 'privacy:metadata:local_intellicart:cybersource_com:bill_to_forename',
                'bill_to_surname'           => 'privacy:metadata:local_intellicart:cybersource_com:bill_to_surname',
                'bill_to_email'             => 'privacy:metadata:local_intellicart:cybersource_com:bill_to_email',
                'bill_to_address_line1'     => 'privacy:metadata:local_intellicart:cybersource_com:bill_to_address_line1',
                'bill_to_address_city'      => 'privacy:metadata:local_intellicart:cybersource_com:bill_to_address_city',
                'bill_to_address_state'     => 'privacy:metadata:local_intellicart:cybersource_com:bill_to_address_state',
                'bill_to_address_country'   => 'privacy:metadata:local_intellicart:cybersource_com:bill_to_address_country',
                'bill_to_address_postal_code'    => 'privacy:metadata:local_intellicart:cybersource_com:bill_to_address_postal_code',
                'customer_ip_address'       => 'privacy:metadata:local_intellicart:cybersource_com:customer_ip_address',
            ],
            'privacy:metadata:local_intellicart:cybersource_com'
        );

        // Redsys payment.
        $collection->add_external_location_link(
            'redsys_es',
            [
                'Ds_SignatureVersion'       => 'privacy:metadata:local_intellicart:redsys_es:signatureversion',
                'Ds_MerchantParameters'     => 'privacy:metadata:local_intellicart:redsys_es:merchantparameters',
                'Ds_Signature'              => 'privacy:metadata:local_intellicart:redsys_es:signature',
            ],
            'privacy:metadata:local_intellicart:redsys_es'
        );

        // The 'local_intellicart' table stores the metadata about what user added to the cart.
        $collection->add_database_table(
            'local_intellicart',
            [
                'userid' => 'privacy:metadata:local_intellicart:userid',
                'productid' => 'privacy:metadata:local_intellicart:productid',
                'timecreated' => 'privacy:metadata:local_intellicart:timecreated',
                'quantity' => 'privacy:metadata:local_intellicart:quantity',
                'type' => 'privacy:metadata:local_intellicart:type',
            ],
            'privacy:metadata:local_intellicart'
        );

        // The 'local_intellicart_checkout' table stores the metadata about users orders.
        $collection->add_database_table(
            'local_intellicart_checkout',
            [
                'item_name' => 'privacy:metadata:local_intellicart_checkout:item_name',
                'userid' => 'privacy:metadata:local_intellicart_checkout:userid',
                'items' => 'privacy:metadata:local_intellicart_checkout:items',
                'payment_status' => 'privacy:metadata:local_intellicart_checkout:payment_status',
                'pending_reason' => 'privacy:metadata:local_intellicart_checkout:pending_reason',
                'reason_code' => 'privacy:metadata:local_intellicart_checkout:reason_code',
                'txn_id' => 'privacy:metadata:local_intellicart_checkout:txn_id',
                'amount' => 'privacy:metadata:local_intellicart_checkout:amount',
                'subtotal' => 'privacy:metadata:local_intellicart_checkout:subtotal',
                'discount' => 'privacy:metadata:local_intellicart_checkout:discount',
                'tax' => 'privacy:metadata:local_intellicart_checkout:tax',
                'payment_type' => 'privacy:metadata:local_intellicart_checkout:payment_type',
                'timeupdated' => 'privacy:metadata:local_intellicart_checkout:timeupdated',
                'paymentid' => 'privacy:metadata:local_intellicart_checkout:paymentid',
                'invoicepayment' => 'privacy:metadata:local_intellicart_checkout:invoicepayment',
                'billingtype' => 'privacy:metadata:local_intellicart_checkout:billingtype',
                'subscr_id' => 'privacy:metadata:local_intellicart_checkout:subscr_id',
            ],
            'privacy:metadata:local_intellicart_checkout'
        );

        // The 'local_intellicart_logs' table stores all logs about orders, discounts, coupons, waitlist.
        $collection->add_database_table(
            'local_intellicart_logs',
            [
                'userid' => 'privacy:metadata:local_intellicart_logs:userid',
                'instanceid' => 'privacy:metadata:local_intellicart_logs:instanceid',
                'type' => 'privacy:metadata:local_intellicart_logs:type',
                'status' => 'privacy:metadata:local_intellicart_logs:status',
                'timecreated' => 'privacy:metadata:local_intellicart_logs:timecreated',
                'timemodified' => 'privacy:metadata:local_intellicart_logs:timemodified',
                'checkoutid' => 'privacy:metadata:local_intellicart_logs:checkoutid',
                'price' => 'privacy:metadata:local_intellicart_logs:price',
                'discountprice' => 'privacy:metadata:local_intellicart_logs:discountprice',
                'discount' => 'privacy:metadata:local_intellicart_logs:discount',
                'items' => 'privacy:metadata:local_intellicart_logs:items',
                'details' => 'privacy:metadata:local_intellicart_logs:details',
                'quantity' => 'privacy:metadata:local_intellicart_logs:quantity',
                'tax' => 'privacy:metadata:local_intellicart_logs:tax',
                'sessionid' => 'privacy:metadata:local_intellicart_logs:sessionid',
                'groupid' => 'privacy:metadata:local_intellicart_logs:groupid',
                'customint1' => 'privacy:metadata:local_intellicart_logs:customint1',
            ],
            'privacy:metadata:local_intellicart_logs'
        );

        // The 'local_intellicart_waitlist' table stores the metadata about checkout waitlist.
        $collection->add_database_table(
            'local_intellicart_waitlist',
            [
                'userid' => 'privacy:metadata:local_intellicart_waitlist:userid',
                'productid' => 'privacy:metadata:local_intellicart_waitlist:productid',
                'seatkey' => 'privacy:metadata:local_intellicart_waitlist:seatkey',
                'timemodified' => 'privacy:metadata:local_intellicart_waitlist:timemodified',
                'sent' => 'privacy:metadata:local_intellicart_waitlist:sent',
            ],
            'privacy:metadata:local_intellicart_waitlist'
        );

        // The 'local_intellicart_vendors' table stores the metadata about vendors.
        $collection->add_database_table(
            'local_intellicart_vendors',
            [
                'name' => 'privacy:metadata:local_intellicart_vendors:name',
                'type' => 'privacy:metadata:local_intellicart_vendors:type',
                'email' => 'privacy:metadata:local_intellicart_vendors:email',
                'company' => 'privacy:metadata:local_intellicart_vendors:company',
                'url' => 'privacy:metadata:local_intellicart_vendors:url',
                'status' => 'privacy:metadata:local_intellicart_vendors:status',
                'timecreated' => 'privacy:metadata:local_intellicart_vendors:timecreated',
                'timemodified' => 'privacy:metadata:local_intellicart_vendors:timemodified',
            ],
            'privacy:metadata:local_intellicart_vendors'
        );

        // The 'local_intellicart_users' table stores the metadata about users relations.
        $collection->add_database_table(
            'local_intellicart_users',
            [
                'instanceid' => 'privacy:metadata:local_intellicart_users:instanceid',
                'type' => 'privacy:metadata:local_intellicart_users:type',
                'userid' => 'privacy:metadata:local_intellicart_users:userid',
                'role' => 'privacy:metadata:local_intellicart_users:role',
                'timemodified' => 'privacy:metadata:local_intellicart_users:timemodified',
                'status' => 'privacy:metadata:local_intellicart_users:status',
            ],
            'privacy:metadata:local_intellicart_users'
        );

        // The 'local_intellicart_history' table stores user history
        $collection->add_database_table(
            'local_intellicart_history',
            [
                'userid' => 'privacy:metadata:local_intellicart_history:userid',
                'instanceid' => 'privacy:metadata:local_intellicart_history:instanceid',
                'type' => 'privacy:metadata:local_intellicart_history:type',
                'action' => 'privacy:metadata:local_intellicart_history:action',
                'details' => 'privacy:metadata:local_intellicart_history:details',
                'timemodified' => 'privacy:metadata:local_intellicart_history:timemodified',
            ],
            'privacy:metadata:local_intellicart_history'
        );

        // The 'local_intellicart_seats' table stores the metadata about seats.
        $collection->add_database_table(
            'local_intellicart_seats',
            [
                'userid' => 'privacy:metadata:local_intellicart_seats:userid',
                'productid' => 'privacy:metadata:local_intellicart_seats:productid',
                'seatkey' => 'privacy:metadata:local_intellicart_seats:seatkey',
                'timecreated' => 'privacy:metadata:local_intellicart_seats:timecreated',
                'timemodified' => 'privacy:metadata:local_intellicart_seats:timemodified',
                'quantity' => 'privacy:metadata:local_intellicart_seats:quantity',
                'checkoutid' => 'privacy:metadata:local_intellicart_seats:checkoutid',
                'active' => 'privacy:metadata:local_intellicart_seats:active',
                'expiration' => 'privacy:metadata:local_intellicart_seats:expiration',
            ],
            'privacy:metadata:local_intellicart_seats'
        );

        // The 'local_intellicart_subscr' table stores the metadata about user subscriptions.
        $collection->add_database_table(
            'local_intellicart_subscr',
            [
                'userid' => 'privacy:metadata:local_intellicart_subscr:userid',
                'productid' => 'privacy:metadata:local_intellicart_subscr:productid',
                'subscr_id' => 'privacy:metadata:local_intellicart_subscr:subscr_id',
                'amount' => 'privacy:metadata:local_intellicart_subscr:amount',
                'recur_times' => 'privacy:metadata:local_intellicart_subscr:recur_times',
                'recur_period' => 'privacy:metadata:local_intellicart_subscr:recur_period',
                'subscr_date' => 'privacy:metadata:local_intellicart_subscr:subscr_date',
                'timemodified' => 'privacy:metadata:local_intellicart_subscr:timemodified',
                'checkoutid' => 'privacy:metadata:local_intellicart_subscr:checkoutid',
                'expiration' => 'privacy:metadata:local_intellicart_subscr:expiration',
                'status' => 'privacy:metadata:local_intellicart_subscr:status',
            ],
            'privacy:metadata:local_intellicart_subscr'
        );

        // The 'local_intellicart_shipping' table stores the metadata about shippings.
        $collection->add_database_table(
            'local_intellicart_shipping',
            [
                'checkoutid' => 'privacy:metadata:local_intellicart_shipping:checkoutid',
                'name' => 'privacy:metadata:local_intellicart_shipping:name',
                'email' => 'privacy:metadata:local_intellicart_shipping:email',
                'address' => 'privacy:metadata:local_intellicart_shipping:address',
                'city' => 'privacy:metadata:local_intellicart_shipping:city',
                'telephone_number' => 'privacy:metadata:local_intellicart_shipping:telephone_number',
                'timecreated' => 'privacy:metadata:local_intellicart_shipping:timecreated',
                'timemodified' => 'privacy:metadata:local_intellicart_shipping:timemodified',
                'userid' => 'privacy:metadata:local_intellicart_shipping:userid',
                'state' => 'privacy:metadata:local_intellicart_shipping:state',
                'zipcode' => 'privacy:metadata:local_intellicart_shipping:zipcode',
                'notes' => 'privacy:metadata:local_intellicart_shipping:notes',
            ],
            'privacy:metadata:local_intellicart_shipping'
        );

        // The 'local_intellicart_wishlist' table stores the metadata about user wishlist.
        $collection->add_database_table(
            'local_intellicart_wishlist',
            [
                'productid' => 'privacy:metadata:local_intellicart_wishlist:productid',
                'userid' => 'privacy:metadata:local_intellicart_wishlist:userid',
                'timecreated' => 'privacy:metadata:local_intellicart_wishlist:timecreated',
            ],
            'privacy:metadata:local_intellicart_wishlist'
        );

        // The 'local_intellicart_reviews' table stores the metadata about users reviews.
        $collection->add_database_table(
            'local_intellicart_reviews',
            [
                'userid' => 'privacy:metadata:local_intellicart_reviews:userid',
                'instanceid' => 'privacy:metadata:local_intellicart_reviews:instanceid',
                'type' => 'privacy:metadata:local_intellicart_reviews:type',
                'rate' => 'privacy:metadata:local_intellicart_reviews:rate',
                'reviewtext' => 'privacy:metadata:local_intellicart_reviews:reviewtext',
                'approved' => 'privacy:metadata:local_intellicart_reviews:approved',
                'timecreated' => 'privacy:metadata:local_intellicart_reviews:timecreated',
                'timemodified' => 'privacy:metadata:local_intellicart_reviews:timemodified',
            ],
            'privacy:metadata:local_intellicart_reviews'
        );

        // The 'local_intellicart_comments' table stores the metadata about users comments.
        $collection->add_database_table(
            'local_intellicart_comments',
            [
                'userid' => 'privacy:metadata:local_intellicart_comments:userid',
                'instanceid' => 'privacy:metadata:local_intellicart_comments:instanceid',
                'type' => 'privacy:metadata:local_intellicart_comments:type',
                'commenttext' => 'privacy:metadata:local_intellicart_comments:commenttext',
                'approved' => 'privacy:metadata:local_intellicart_comments:approved',
                'timecreated' => 'privacy:metadata:local_intellicart_comments:timecreated',
                'timemodified' => 'privacy:metadata:local_intellicart_comments:timemodified',
            ],
            'privacy:metadata:local_intellicart_comments'
        );

        // The 'local_intellicart_likes' table stores the metadata about users likes.
        $collection->add_database_table(
            'local_intellicart_likes',
            [
                'userid' => 'privacy:metadata:local_intellicart_likes:userid',
                'instanceid' => 'privacy:metadata:local_intellicart_likes:instanceid',
                'type' => 'privacy:metadata:local_intellicart_likes:type',
                'status' => 'privacy:metadata:local_intellicart_likes:status',
                'timecreated' => 'privacy:metadata:local_intellicart_likes:timecreated',
            ],
            'privacy:metadata:local_intellicart_likes'
        );

        // The 'local_intellicart_catsubscru' table stores the metadata about user subscriptions.
        $collection->add_database_table(
            'local_intellicart_catsubscru',
            [
                'userid' => 'privacy:metadata:local_intellicart_catsubscru:userid',
                'subscrid' => 'privacy:metadata:local_intellicart_catsubscru:subscrid',
                'amount' => 'privacy:metadata:local_intellicart_catsubscru:amount',
                'recur_times' => 'privacy:metadata:local_intellicart_catsubscru:recur_times',
                'recur_period' => 'privacy:metadata:local_intellicart_catsubscru:recur_period',
                'subscr_date' => 'privacy:metadata:local_intellicart_catsubscru:subscr_date',
                'timemodified' => 'privacy:metadata:local_intellicart_catsubscru:timemodified',
                'checkoutid' => 'privacy:metadata:local_intellicart_catsubscru:checkoutid',
                'expiration' => 'privacy:metadata:local_intellicart_catsubscru:expiration',
                'status' => 'privacy:metadata:local_intellicart_catsubscru:status',
            ],
            'privacy:metadata:local_intellicart_catsubscru'
        );

        // The 'local_intellicart_icheckout' table stores the metadata about users orders for instance.
        $collection->add_database_table(
            'local_intellicart_icheckout',
            [
                'item_name' => 'privacy:metadata:local_intellicart_icheckout:item_name',
                'userid' => 'privacy:metadata:local_intellicart_icheckout:userid',
                'instanceid' => 'privacy:metadata:local_intellicart_icheckout:instanceid',
                'type' => 'privacy:metadata:local_intellicart_icheckout:type',
                'payment_status' => 'privacy:metadata:local_intellicart_icheckout:payment_status',
                'pending_reason' => 'privacy:metadata:local_intellicart_icheckout:pending_reason',
                'reason_code' => 'privacy:metadata:local_intellicart_icheckout:reason_code',
                'txn_id' => 'privacy:metadata:local_intellicart_icheckout:txn_id',
                'amount' => 'privacy:metadata:local_intellicart_icheckout:amount',
                'subtotal' => 'privacy:metadata:local_intellicart_icheckout:subtotal',
                'discount' => 'privacy:metadata:local_intellicart_icheckout:discount',
                'tax' => 'privacy:metadata:local_intellicart_icheckout:tax',
                'payment_type' => 'privacy:metadata:local_intellicart_icheckout:payment_type',
                'timeupdated' => 'privacy:metadata:local_intellicart_icheckout:timeupdated',
                'paymentid' => 'privacy:metadata:local_intellicart_icheckout:paymentid',
                'email' => 'privacy:metadata:local_intellicart_icheckout:email',
                'notes' => 'privacy:metadata:local_intellicart_icheckout:notes',
                'token' => 'privacy:metadata:local_intellicart_icheckout:token',
            ],
            'privacy:metadata:local_intellicart_icheckout'
        );

        // The 'local_intellicart_seats' table stores the metadata about seats.
        $collection->add_database_table(
            'local_intellicart_erequests',
            [
                'userid' => 'privacy:metadata:local_intellicart_erequests:userid',
                'enrolid' => 'privacy:metadata:local_intellicart_erequests:enrolid',
                'productid' => 'privacy:metadata:local_intellicart_erequests:productid',
                'courseid' => 'privacy:metadata:local_intellicart_erequests:courseid',
                'timecreated' => 'privacy:metadata:local_intellicart_erequests:timecreated',
                'timemodified' => 'privacy:metadata:local_intellicart_erequests:timemodified',
                'updatedbyuserid' => 'privacy:metadata:local_intellicart_erequests:updatedbyuserid',
                'status' => 'privacy:metadata:local_intellicart_erequests:status',
                'notes' => 'privacy:metadata:local_intellicart_erequests:notes',
            ],
            'privacy:metadata:local_intellicart_erequests'
        );

        // The 'local_intellicart_cissues' table stores the metadata about certificates issued data.
        $collection->add_database_table(
            'local_intellicart_cissues',
            [
                'userid' => 'privacy:metadata:local_intellicart_cissues:userid',
                'templateid' => 'privacy:metadata:local_intellicart_cissues:templateid',
                'certificationid' => 'privacy:metadata:local_intellicart_cissues:certificationid',
                'certificateid' => 'privacy:metadata:local_intellicart_cissues:certificateid',
                'code' => 'privacy:metadata:local_intellicart_cissues:code',
                'emailed' => 'privacy:metadata:local_intellicart_cissues:emailed',
                'timecreated' => 'privacy:metadata:local_intellicart_cissues:timecreated',
                'expires' => 'privacy:metadata:local_intellicart_cissues:expires',
                'data' => 'privacy:metadata:local_intellicart_cissues:data',
                'pdfdata' => 'privacy:metadata:local_intellicart_cissues:pdfdata',
                'component' => 'privacy:metadata:local_intellicart_cissues:component',
            ],
            'privacy:metadata:local_intellicart_cissues'
        );

        // The 'local_intellicart_cpages' table stores the metadata about template pages.
        $collection->add_database_table(
            'local_intellicart_cpages',
            [
                'userid' => 'privacy:metadata:local_intellicart_cpages:userid',
                'templateid' => 'privacy:metadata:local_intellicart_cpages:templateid',
                'width' => 'privacy:metadata:local_intellicart_cpages:width',
                'height' => 'privacy:metadata:local_intellicart_cpages:height',
                'leftmargin' => 'privacy:metadata:local_intellicart_cpages:leftmargin',
                'rightmargin' => 'privacy:metadata:local_intellicart_cpages:rightmargin',
                'sequence' => 'privacy:metadata:local_intellicart_cpages:sequence',
                'timecreated' => 'privacy:metadata:local_intellicart_cpages:timecreated',
                'timemodified' => 'privacy:metadata:local_intellicart_cpages:timemodified',
            ],
            'privacy:metadata:local_intellicart_cpages'
        );

        // The 'local_intellicart_requests' table stores the metadata about user requesrs.
        $collection->add_database_table(
            'local_intellicart_requests',
            [
                'userid'          => 'privacy:metadata:local_intellicart_requests:userid',
                'type'            => 'privacy:metadata:local_intellicart_requests:type',
                'productid'       => 'privacy:metadata:local_intellicart_requests:productid',
                'checkoutid'      => 'privacy:metadata:local_intellicart_requests:checkoutid',
                'status'          => 'privacy:metadata:local_intellicart_requests:status',
                'notes'           => 'privacy:metadata:local_intellicart_requests:notes',
                'updatedbyuserid' => 'privacy:metadata:local_intellicart_requests:updatedbyuserid',
                'timecreated'     => 'privacy:metadata:local_intellicart_requests:timecreated',
                'timemodified'    => 'privacy:metadata:local_intellicart_requests:timemodified',
            ],
            'privacy:metadata:local_intellicart_requests'
        );

        // The 'local_intellicart_ch_details' table stores checkout details.
        $collection->add_database_table(
            'local_intellicart_ch_details',
            [
                'userid' => 'privacy:metadata:local_intellicart_ch_details:userid',
                'checkoutid' => 'privacy:metadata:local_intellicart_ch_details:checkoutid',
                'name' => 'privacy:metadata:local_intellicart_ch_details:name',
                'value' => 'privacy:metadata:local_intellicart_ch_details:value',
                'timecreated' => 'privacy:metadata:local_intellicart_ch_details:timecreated',
                'timemodified' => 'privacy:metadata:local_intellicart_ch_details:timemodified',
            ],
            'privacy:metadata:local_intellicart_ch_details'
        );

        // The local_intellicart user preference setting 'intellicart_categories_filter'.
        $collection->add_user_preference(
                'intellicart_categories_filter',
                'privacy:metadata:local_intellicart:preferences:intellicart_categories_filter'
        );
        // The local_intellicart user preference setting 'intellicart_couponsstatus_filter'.
        $collection->add_user_preference(
                'intellicart_couponsstatus_filter',
                'privacy:metadata:local_intellicart:preferences:intellicart_couponsstatus_filter'
        );
        // The local_intellicart user preference setting 'intellicart_discountsstatus_filter'.
        $collection->add_user_preference(
                'intellicart_discountsstatus_filter',
                'privacy:metadata:local_intellicart:preferences:intellicart_discountsstatus_filter'
        );
        // The local_intellicart user preference setting 'intellicart_productsstatus_filter'.
        $collection->add_user_preference(
                'intellicart_productsstatus_filter',
                'privacy:metadata:local_intellicart:preferences:intellicart_productsstatus_filter'
        );
        // The local_intellicart user preference setting 'intellicart_paymentsstatus_filter'.
        $collection->add_user_preference(
                'intellicart_paymentsstatus_filter',
                'privacy:metadata:local_intellicart:preferences:intellicart_paymentsstatus_filter'
        );
        // The local_intellicart user preference setting 'intellicart_vendorsstatus_filter'.
        $collection->add_user_preference(
                'intellicart_vendorsstatus_filter',
                'privacy:metadata:local_intellicart:preferences:intellicart_vendorsstatus_filter'
        );
        // The local_intellicart user preference setting 'intellicart_usersstatus_filter'.
        $collection->add_user_preference(
                'intellicart_usersstatus_filter',
                'privacy:metadata:local_intellicart:preferences:intellicart_usersstatus_filter'
        );
        // The local_intellicart user preference setting 'intellicart_customfields_view_filter'.
        $collection->add_user_preference(
                'intellicart_customfields_view_filter',
                'privacy:metadata:local_intellicart:preferences:intellicart_customfields_view_filter'
        );
        // The local_intellicart user preference setting 'intellicart_vendor_filter'.
        $collection->add_user_preference(
                'intellicart_vendor_filter',
                'privacy:metadata:local_intellicart:preferences:intellicart_vendor_filter'
        );
        // The local_intellicart user preference setting 'intellicart_catsubscriptions_filter'.
        $collection->add_user_preference(
                'intellicart_catsubscriptions_filter',
                'privacy:metadata:local_intellicart:preferences:intellicart_catsubscriptions_filter'
        );
        // The local_intellicart user preference setting 'intellicart_giftcardsstatus_filter'.
        $collection->add_user_preference(
                'intellicart_giftcardsstatus_filter',
                'privacy:metadata:local_intellicart:preferences:intellicart_giftcardsstatus_filter'
        );
        // The local_intellicart user preference setting 'intellicart_table_limit'.
        $collection->add_user_preference(
                'intellicart_table_limit',
                'privacy:metadata:local_intellicart:preferences:intellicart_table_limit'
        );
        // The local_intellicart users preference setting 'intellicart_salesstatus_filter'.
        $collection->add_user_preference(
                'intellicart_salesstatus_filter',
                'privacy:metadata:local_intellicart:preferences:intellicart_salesstatus_filter'
        );
        // The local_intellicart users preference setting 'intellicart_salesbt_filter'.
        $collection->add_user_preference(
                'intellicart_salesbt_filter',
                'privacy:metadata:local_intellicart:preferences:intellicart_salesbt_filter'
        );
        // The local_intellicart users preference setting 'intellicart_certificationsstatus_filter'.
        $collection->add_user_preference(
                'intellicart_certificationsstatus_filter',
                'privacy:metadata:local_intellicart:preferences:intellicart_certificationsstatus_filter'
        );
        // The local_intellicart users preference setting 'intellicart_awardcertificatesstatus_filter'.
        $collection->add_user_preference(
                'intellicart_awardcertificatesstatus_filter',
                'privacy:metadata:local_intellicart:preferences:intellicart_awardcertificatesstatus_filter'
        );
        // The local_intellicart users preference setting 'intellicart_billingtype'.
        $collection->add_user_preference(
                'intellicart_billingtype',
                'privacy:metadata:local_intellicart:preferences:intellicart_billingtype'
        );

        return $collection;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int $userid The user to search.
     * @return  contextlist $contextlist The contextlist containing the list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new contextlist();

        $params = ['userid' => $userid, 'contextuser' => CONTEXT_USER];
        $sql = "SELECT DISTINCT(ctx.id) as id
                  FROM {context} ctx
                   LEFT JOIN {local_intellicart} li ON li.userid = ctx.instanceid
                   LEFT JOIN {local_intellicart_checkout} ch ON ch.userid = ctx.instanceid
                   LEFT JOIN {local_intellicart_logs} l ON l.userid = ctx.instanceid
                   LEFT JOIN {local_intellicart_waitlist} w ON w.userid = ctx.instanceid
                   LEFT JOIN {local_intellicart_users} iu ON iu.userid = ctx.instanceid
                   LEFT JOIN {local_intellicart_history} h ON h.userid = ctx.instanceid
                   LEFT JOIN {local_intellicart_seats} s ON s.userid = ctx.instanceid
                   LEFT JOIN {local_intellicart_subscr} sub ON sub.userid = ctx.instanceid
                   LEFT JOIN {local_intellicart_shipping} sh ON sh.userid = ctx.instanceid
                   LEFT JOIN {local_intellicart_wishlist} wsh ON wsh.userid = ctx.instanceid
                   LEFT JOIN {local_intellicart_reviews} rw ON rw.userid = ctx.instanceid
                   LEFT JOIN {local_intellicart_comments} com ON com.userid = ctx.instanceid
                   LEFT JOIN {local_intellicart_likes} lk ON lk.userid = ctx.instanceid
                   LEFT JOIN {local_intellicart_catsubscru} csub ON csub.userid = ctx.instanceid
                   LEFT JOIN {local_intellicart_icheckout} ich ON ich.userid = ctx.instanceid
                   LEFT JOIN {local_intellicart_erequests} er ON er.userid = ctx.instanceid
                   LEFT JOIN {local_intellicart_cissues} cis ON cis.userid = ctx.instanceid
                   LEFT JOIN {local_intellicart_cpages} cpg ON cpg.userid = ctx.instanceid
                   LEFT JOIN {local_intellicart_requests} urq ON urq.userid = ctx.instanceid
                   LEFT JOIN {local_intellicart_ch_details} chd ON chd.userid = ctx.instanceid
                 WHERE ctx.instanceid = :userid AND ctx.contextlevel = :contextuser AND 
                      (li.userid IS NOT NULL OR
                       ch.userid IS NOT NULL OR
                       l.userid IS NOT NULL OR
                       w.userid IS NOT NULL OR
                       iu.userid IS NOT NULL OR
                       h.userid IS NOT NULL OR
                       s.userid IS NOT NULL OR 
                       sub.userid IS NOT NULL OR
                       sh.userid IS NOT NULL OR
                       wsh.userid IS NOT NULL OR
                       rw.userid IS NOT NULL OR
                       com.userid IS NOT NULL OR
                       lk.userid IS NOT NULL OR 
                       csub.userid IS NOT NULL OR
                       ich.userid IS NOT NULL OR
                       er.userid IS NOT NULL OR
                       cis.userid IS NOT NULL OR
                       cpg.userid IS NOT NULL OR
                       urq.userid IS NOT NULL OR
                       chd.userid IS NOT NULL
                       )";

        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Export all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist)) {
            return;
        }

        $context = $contextlist->current();
        $user = \core_user::get_user($contextlist->get_user()->id);

        self::export_user_local_intellicart_data($user, $context);
        self::export_user_local_intellicart_checkout_data($user, $context);
        self::export_user_local_intellicart_logs_data($user, $context);
        self::export_user_local_intellicart_waitlist_data($user, $context);
        self::export_user_local_intellicart_usersrelations_data($user, $context);
        self::export_user_local_intellicart_history_data($user, $context);
        self::export_user_local_intellicart_seats_data($user, $context);
        self::export_user_local_intellicart_subscriptions_data($user, $context);
        self::export_user_local_intellicart_shipping_data($user, $context);
        self::export_user_local_intellicart_wishlist_data($user, $context);
        self::export_user_local_intellicart_reviews_data($user, $context);
        self::export_user_local_intellicart_comments_data($user, $context);
        self::export_user_local_intellicart_likes_data($user, $context);
        self::export_user_local_intellicart_catsubscriptions_data($user, $context);
        self::export_user_local_intellicart_icheckout_data($user, $context);
        self::export_user_local_intellicart_erequests_data($user, $context);
        self::export_user_local_intellicart_cissues_data($user, $context);
        self::export_user_local_intellicart_cpages_data($user, $context);
        self::export_user_local_intellicart_requests_data($user, $context);
        self::export_user_local_intellicart_ch_details_data($user, $context);
    }

    protected static function export_user_local_intellicart_data(\stdClass $user, \context $context) {
        global $DB;
        $categories = DBHelper::get_operator('GROUP_CONCAT', 'lic.name', ['separator' => ', ']);
        $ids = DBHelper::get_operator('GROUP_CONCAT', 'lic.id', ['separator' => ', ']);

        $sql = "SELECT p.name, cat_rel.categories, li.timecreated, li.quantity, li.type
                  FROM {local_intellicart} li
                    LEFT JOIN {local_intellicart_products} p ON p.id = li.productid
                    LEFT JOIN (
                        SELECT lir.productid, {$categories} as categories,
                               {$ids} as categories_ids
                          FROM {local_intellicart_relations} lir
                          JOIN {local_intellicart_cat} lic ON lic.id = lir.instanceid
                         WHERE lir.type = :prod_cat_rel
                      GROUP BY lir.productid
                    ) cat_rel ON cat_rel.productid = p.id
                 WHERE li.userid = :userid";
        $params = ['userid' => $user->id];
        $records = $DB->get_records_sql($sql, $params);

        if ($records) {
            $data = [];

            foreach ($records as $record) {
                $data[] = (object) [
                    'name' => format_string($record->name, true, ['context' => $context]),
                    'category' => format_string($record->categories, true, ['context' => $context]),
                    'created' => transform::datetime($record->timecreated),
                    'quantity' => $record->quantity,
                    'type' => format_string($record->type, true, ['context' => $context]),
                ];
            }

            writer::with_context($context)->export_data([get_string('privacy:local_intellicart', 'local_intellicart')], (object) $data);
        }
    }

    protected static function export_user_local_intellicart_checkout_data(\stdClass $user, \context $context) {
        global $DB;

        $sql = "SELECT ch.*, p.name as paymenttype 
                  FROM {local_intellicart_checkout} ch
                  LEFT JOIN {local_intellicart_payments} p ON p.id = ch.paymentid
                 WHERE ch.userid = :userid";
        $params = ['userid' => $user->id];
        $records = $DB->get_records_sql($sql, $params);

        if ($records) {
            $data = [];

            foreach ($records as $record) {
                $data[] = (object) [
                    'item_name' => format_string($record->item_name, true, ['context' => $context]),
                    'payment_status' => format_string($record->payment_status, true, ['context' => $context]),
                    'pending_reason' => format_string($record->pending_reason, true, ['context' => $context]),
                    'reason_code' => format_string($record->reason_code, true, ['context' => $context]),
                    'txn_id' => format_string($record->txn_id, true, ['context' => $context]),
                    'amount' => format_string($record->amount, true, ['context' => $context]),
                    'subtotal' => format_string($record->subtotal, true, ['context' => $context]),
                    'discount' => format_string($record->discount, true, ['context' => $context]),
                    'tax' => format_string($record->tax, true, ['context' => $context]),
                    'payment_type' => format_string($record->payment_type, true, ['context' => $context]),
                    'timeupdated' => transform::datetime($record->timeupdated),
                    'paymentid' => format_string($record->paymenttype, true, ['context' => $context]),
                    'invoicepayment' => transform::yesno($record->invoicepayment),
                ];
            }

            writer::with_context($context)->export_data([get_string('privacy:local_intellicart_checkout', 'local_intellicart')], (object) $data);
        }
    }

    protected static function export_user_local_intellicart_logs_data(\stdClass $user, \context $context) {
        global $DB;

        $sql = "SELECT l.*
                  FROM {local_intellicart_logs} l
                 WHERE l.userid = :userid";
        $params = ['userid' => $user->id];
        $records = $DB->get_records_sql($sql, $params);

        if ($records) {
            $data = [];

            foreach ($records as $record) {
                $data[] = (object) [
                    'instanceid' => $record->instanceid,
                    'type' => format_string($record->type, true, ['context' => $context]),
                    'status' => format_string($record->status, true, ['context' => $context]),
                    'timecreated' => transform::datetime($record->timecreated),
                    'timemodified' => transform::datetime($record->timemodified),
                    'checkoutid' => format_string($record->checkoutid, true, ['context' => $context]),
                    'price' => format_string($record->price, true, ['context' => $context]),
                    'discountprice' => format_string($record->discountprice, true, ['context' => $context]),
                    'discount' => format_string($record->discount, true, ['context' => $context]),
                    'items' => format_string($record->items, true, ['context' => $context]),
                    'details' => format_string($record->items, true, ['details' => $context]),
                    'quantity' => $record->quantity,
                    'tax' => $record->tax,
                    'sessionid' => $record->sessionid,
                    'groupid' => $record->groupid,
                    'customint1' => $record->customint1,
                ];
            }

            writer::with_context($context)->export_data([get_string('privacy:local_intellicart_logs', 'local_intellicart')], (object) $data);
        }
    }

    protected static function export_user_local_intellicart_waitlist_data(\stdClass $user, \context $context) {
        global $DB;

        $sql = "SELECT w.*, p.name
                  FROM {local_intellicart_waitlist} w
                  LEFT JOIN {local_intellicart_products} p ON p.id = w.productid
                 WHERE w.userid = :userid";
        $params = ['userid' => $user->id];
        $records = $DB->get_records_sql($sql, $params);

        if ($records) {
            $data = [];

            foreach ($records as $record) {
                $data[] = (object) [
                    'product' => format_string($record->name, true, ['context' => $context]),
                    'timemodified' => transform::datetime($record->timemodified),
                    'sent' => transform::yesno($record->sent),
                ];
            }

            writer::with_context($context)->export_data([get_string('privacy:local_intellicart_waitlist', 'local_intellicart')], (object) $data);
        }
    }

    protected static function export_user_local_intellicart_usersrelations_data(\stdClass $user, \context $context) {
        global $DB;

        $sql = "SELECT u.*, v.name as vendor
                  FROM {local_intellicart_users} u
                  LEFT JOIN {local_intellicart_vendors} v ON v.id = u.instanceid
                 WHERE u.userid = :userid AND u.type = :rtype";
        $params = ['userid' => $user->id, 'rtype' => \local_intellicart\users::USER_TYPE_VENDOR];
        $records = $DB->get_records_sql($sql, $params);

        if ($records) {
            $data = [];

            foreach ($records as $record) {
                $data[] = (object) [
                    'instanceid' => $record->instanceid,
                    'type' => format_string($record->type, true, ['context' => $context]),
                    'vendor' => format_string($record->vendor, true, ['context' => $context]),
                    'timemodified' => transform::datetime($record->timemodified),
                    'role' => format_string($record->role, true, ['context' => $context]),
                    'approved' => transform::yesno($record->status),
                ];
            }

            writer::with_context($context)->export_data([get_string('privacy:local_intellicart_users', 'local_intellicart')], (object) $data);
        }
    }

    protected static function export_user_local_intellicart_history_data(\stdClass $user, \context $context) {
        global $DB;

        $sql = "SELECT h.*
                  FROM {local_intellicart_history} h
                 WHERE h.userid = :userid";
        $params = ['userid' => $user->id];
        $records = $DB->get_records_sql($sql, $params);

        if ($records) {
            $data = [];

            foreach ($records as $record) {
                $data[] = (object) [
                    'instanceid' => $record->instanceid,
                    'type' => format_string($record->type, true, ['context' => $context]),
                    'action' => format_string($record->action, true, ['context' => $context]),
                    'details' => format_string($record->details, true, ['details' => $context]),
                    'timemodified' => transform::datetime($record->timemodified),
                ];
            }

            writer::with_context($context)->export_data([get_string('privacy:local_intellicart_history', 'local_intellicart')], (object) $data);
        }
    }

    protected static function export_user_local_intellicart_seats_data(\stdClass $user, \context $context) {
        global $DB;

        $sql = "SELECT s.*, p.name
                  FROM {local_intellicart_seats} s
                  LEFT JOIN {local_intellicart_products} p ON p.id = s.productid
                 WHERE s.userid = :userid";
        $params = ['userid' => $user->id];
        $records = $DB->get_records_sql($sql, $params);

        if ($records) {
            $data = [];

            foreach ($records as $record) {
                $data[] = (object) [
                    'product' => format_string($record->name, true, ['context' => $context]),
                    'seatkey' => format_string($record->seatkey, true, ['context' => $context]),
                    'quantity' => $record->quantity,
                    'checkoutid' => $record->checkoutid,
                    'timecreated' => transform::datetime($record->timecreated),
                    'timemodified' => transform::datetime($record->timemodified),
                    'active' => transform::yesno($record->active),
                    'expiration' => transform::datetime($record->expiration),
                ];
            }

            writer::with_context($context)->export_data([get_string('privacy:local_intellicart_seats', 'local_intellicart')], (object) $data);
        }
    }

    protected static function export_user_local_intellicart_subscriptions_data(\stdClass $user, \context $context) {
        global $DB;

        $sql = "SELECT s.*, p.name
                  FROM {local_intellicart_subscr} s
                  LEFT JOIN {local_intellicart_products} p ON p.id = s.productid
                 WHERE s.userid = :userid";
        $params = ['userid' => $user->id];
        $records = $DB->get_records_sql($sql, $params);

        if ($records) {
            $data = [];

            foreach ($records as $record) {
                $data[] = (object) [
                    'product' => format_string($record->name, true, ['context' => $context]),
                    'subscr_id' => format_string($record->subscr_id, true, ['context' => $context]),
                    'amount' => format_string($record->amount, true, ['context' => $context]),
                    'recur_times' => $record->recur_times,
                    'recur_period' => $record->recur_period,
                    'checkoutid' => $record->checkoutid,
                    'subscr_date' => transform::datetime($record->subscr_date),
                    'timemodified' => transform::datetime($record->timemodified),
                    'expiration' => transform::datetime($record->expiration),
                    'status' => $record->status,
                ];
            }

            writer::with_context($context)->export_data([get_string('privacy:local_intellicart_subscr', 'local_intellicart')], (object) $data);
        }
    }

    protected static function export_user_local_intellicart_shipping_data(\stdClass $user, \context $context) {
        global $DB;

        $sql = "SELECT sh.* 
                  FROM {local_intellicart_shipping} sh
                 WHERE sh.userid = :userid";
        $params = ['userid' => $user->id];
        $records = $DB->get_records_sql($sql, $params);

        if ($records) {
            $data = [];

            foreach ($records as $record) {
                $data[] = (object) [
                    'checkoutid' => $record->name,
                    'name' => format_string($record->name, true, ['context' => $context]),
                    'email' => format_string($record->email, true, ['context' => $context]),
                    'address' => format_string($record->address, true, ['context' => $context]),
                    'city' => format_string($record->city, true, ['context' => $context]),
                    'state' => format_string($record->state, true, ['context' => $context]),
                    'zipcode' => format_string($record->zipcode, true, ['context' => $context]),
                    'notes' => format_string($record->notes, true, ['context' => $context]),
                    'telephone_number' => format_string($record->telephone_number, true, ['context' => $context]),
                    'timecreated' => transform::datetime($record->timecreated),
                    'timemodified' => transform::datetime($record->timemodified),
                ];
            }

            writer::with_context($context)->export_data([get_string('privacy:local_intellicart_shipping', 'local_intellicart')], (object) $data);
        }
    }

    protected static function export_user_local_intellicart_wishlist_data(\stdClass $user, \context $context) {
        global $DB;

        $sql = "SELECT wsh.*, p.name
                  FROM {local_intellicart_wishlist} wsh
                  LEFT JOIN {local_intellicart_products} p ON p.id = wsh.productid
                 WHERE wsh.userid = :userid";
        $params = ['userid' => $user->id];
        $records = $DB->get_records_sql($sql, $params);

        if ($records) {
            $data = [];

            foreach ($records as $record) {
                $data[] = (object) [
                    'product' => format_string($record->name, true, ['context' => $context]),
                    'timecreated' => transform::datetime($record->timecreated),
                ];
            }

            writer::with_context($context)->export_data([get_string('privacy:local_intellicart_wishlist', 'local_intellicart')], (object) $data);
        }
    }

    protected static function export_user_local_intellicart_reviews_data(\stdClass $user, \context $context) {
        global $DB;

        $sql = "SELECT rw.*
                  FROM {local_intellicart_reviews} rw
                 WHERE rw.userid = :userid";
        $params = ['userid' => $user->id];
        $records = $DB->get_records_sql($sql, $params);

        if ($records) {
            $data = [];

            foreach ($records as $record) {
                $data[] = (object) [
                    'instanceid' => $record->instanceid,
                    'type' => format_string($record->type, true, ['context' => $context]),
                    'rate' => $record->rate,
                    'reviewtext' => format_string($record->reviewtext, true, ['context' => $context]),
                    'approved' => transform::yesno($record->approved),
                    'timecreated' => transform::datetime($record->timecreated),
                    'timemodified' => transform::datetime($record->timemodified),
                ];
            }

            writer::with_context($context)->export_data([get_string('privacy:local_intellicart_reviews', 'local_intellicart')], (object) $data);
        }
    }

    protected static function export_user_local_intellicart_comments_data(\stdClass $user, \context $context) {
        global $DB;

        $sql = "SELECT com.*
                  FROM {local_intellicart_comments} com
                 WHERE com.userid = :userid";
        $params = ['userid' => $user->id];
        $records = $DB->get_records_sql($sql, $params);

        if ($records) {
            $data = [];

            foreach ($records as $record) {
                $data[] = (object) [
                    'instanceid' => $record->instanceid,
                    'type' => format_string($record->type, true, ['context' => $context]),
                    'commenttext' => format_string($record->commenttext, true, ['context' => $context]),
                    'approved' => transform::yesno($record->approved),
                    'timecreated' => transform::datetime($record->timecreated),
                    'timemodified' => transform::datetime($record->timemodified),
                ];
            }

            writer::with_context($context)->export_data([get_string('privacy:local_intellicart_comments', 'local_intellicart')], (object) $data);
        }
    }

    protected static function export_user_local_intellicart_likes_data(\stdClass $user, \context $context) {
        global $DB;

        $sql = "SELECT lk.*
                  FROM {local_intellicart_likes} lk
                 WHERE lk.userid = :userid";
        $params = ['userid' => $user->id];
        $records = $DB->get_records_sql($sql, $params);

        if ($records) {
            $data = [];

            foreach ($records as $record) {
                $data[] = (object) [
                    'instanceid' => $record->instanceid,
                    'type' => format_string($record->type, true, ['context' => $context]),
                    'status' => transform::yesno($record->status),
                    'timecreated' => transform::datetime($record->timecreated),
                ];
            }

            writer::with_context($context)->export_data([get_string('privacy:local_intellicart_likes', 'local_intellicart')], (object) $data);
        }
    }

    protected static function export_user_local_intellicart_catsubscriptions_data(\stdClass $user, \context $context) {
        global $DB;

        $sql = "SELECT su.*, s.name
                  FROM {local_intellicart_catsubscru} su
                  LEFT JOIN {local_intellicart_catsubscr} s ON s.id = su.subscrid
                 WHERE su.userid = :userid";
        $params = ['userid' => $user->id];
        $records = $DB->get_records_sql($sql, $params);

        if ($records) {
            $data = [];

            foreach ($records as $record) {
                $data[] = (object) [
                    'subscription' => format_string($record->name, true, ['context' => $context]),
                    'subscrid' => format_string($record->subscrid, true, ['context' => $context]),
                    'amount' => format_string($record->amount, true, ['context' => $context]),
                    'recur_times' => $record->recur_times,
                    'recur_period' => $record->recur_period,
                    'checkoutid' => $record->checkoutid,
                    'subscr_date' => transform::datetime($record->subscr_date),
                    'timemodified' => transform::datetime($record->timemodified),
                    'expiration' => transform::datetime($record->expiration),
                    'status' => $record->status,
                ];
            }

            writer::with_context($context)->export_data([get_string('privacy:local_intellicart_catsubscru', 'local_intellicart')], (object) $data);
        }
    }

    protected static function export_user_local_intellicart_icheckout_data(\stdClass $user, \context $context) {
        global $DB;

        $sql = "SELECT ich.*, p.name as paymenttype 
                  FROM {local_intellicart_icheckout} ich
                  LEFT JOIN {local_intellicart_payments} p ON p.id = ich.paymentid
                 WHERE ich.userid = :userid";
        $params = ['userid' => $user->id];
        $records = $DB->get_records_sql($sql, $params);

        if ($records) {
            $data = [];

            foreach ($records as $record) {
                $data[] = (object) [
                    'item_name' => format_string($record->item_name, true, ['context' => $context]),
                    'type' => format_string($record->type, true, ['context' => $context]),
                    'payment_status' => format_string($record->payment_status, true, ['context' => $context]),
                    'pending_reason' => format_string($record->pending_reason, true, ['context' => $context]),
                    'reason_code' => format_string($record->reason_code, true, ['context' => $context]),
                    'txn_id' => format_string($record->txn_id, true, ['context' => $context]),
                    'amount' => format_string($record->amount, true, ['context' => $context]),
                    'subtotal' => format_string($record->subtotal, true, ['context' => $context]),
                    'discount' => format_string($record->discount, true, ['context' => $context]),
                    'tax' => format_string($record->tax, true, ['context' => $context]),
                    'payment_type' => format_string($record->payment_type, true, ['context' => $context]),
                    'timeupdated' => transform::datetime($record->timeupdated),
                    'paymentid' => format_string($record->paymenttype, true, ['context' => $context]),
                    'email' => format_string($record->email, true, ['context' => $context]),
                    'notes' => format_string($record->notes, true, ['context' => $context]),
                ];
            }

            writer::with_context($context)->export_data([get_string('privacy:local_intellicart_icheckout', 'local_intellicart')], (object) $data);
        }
    }

    protected static function export_user_local_intellicart_erequests_data(\stdClass $user, \context $context) {
        global $DB;

        $sql = "SELECT er.*
                  FROM {local_intellicart_erequests} er
                 WHERE er.userid = :userid";
        $params = ['userid' => $user->id];
        $records = $DB->get_records_sql($sql, $params);

        if ($records) {
            $data = [];

            foreach ($records as $record) {
                $data[] = (object) [
                    'enrolid' => $record->enrolid,
                    'productid' => $record->productid,
                    'courseid' => $record->courseid,
                    'timecreated' => transform::datetime($record->timecreated),
                    'timemodified' => transform::datetime($record->timemodified),
                    'updatedbyuserid' => $record->updatedbyuserid,
                    'status' => $record->status,
                    'notes' => format_string($record->notes, true, ['context' => $context]),
                ];
            }

            writer::with_context($context)->export_data([get_string('privacy:local_intellicart_erequests', 'local_intellicart')], (object) $data);
        }
    }

    protected static function export_user_local_intellicart_cissues_data(\stdClass $user, \context $context) {
        global $DB;

        $sql = "SELECT cis.*
                  FROM {local_intellicart_cissues} cis
                 WHERE cis.userid = :userid";
        $params = ['userid' => $user->id];
        $records = $DB->get_records_sql($sql, $params);

        if ($records) {
            $data = [];

            foreach ($records as $record) {
                $data[] = (object) [
                    'templateid' => $record->templateid,
                    'certificationid' => $record->certificationid,
                    'certificateid' => $record->certificateid,
                    'code' => $record->code,
                    'emailed' => $record->emailed,
                    'timecreated' => transform::datetime($record->timecreated),
                    'expires' => transform::datetime($record->expires),
                    'data' => format_string($record->data, true, ['context' => $context]),
                    'pdfdata' => format_string($record->pdfdata, true, ['context' => $context]),
                    'component' => format_string($record->component, true, ['context' => $context]),
                ];
            }

            writer::with_context($context)->export_data([get_string('privacy:local_intellicart_cissues', 'local_intellicart')], (object) $data);
        }
    }

    protected static function export_user_local_intellicart_cpages_data(\stdClass $user, \context $context) {
        global $DB;

        $sql = "SELECT cpg.*
                  FROM {local_intellicart_cpages} cpg
                 WHERE cpg.userid = :userid";
        $params = ['userid' => $user->id];
        $records = $DB->get_records_sql($sql, $params);

        if ($records) {
            $data = [];

            foreach ($records as $record) {
                $data[] = (object) [
                    'templateid' => $record->templateid,
                    'width' => $record->width,
                    'height' => $record->height,
                    'leftmargin' => $record->leftmargin,
                    'rightmargin' => $record->rightmargin,
                    'sequence' => $record->sequence,
                    'timecreated' => transform::datetime($record->timecreated),
                    'timemodified' => transform::datetime($record->timemodified),
                ];
            }

            writer::with_context($context)->export_data([get_string('privacy:local_intellicart_cpages', 'local_intellicart')], (object) $data);
        }
    }

    protected static function export_user_local_intellicart_requests_data(\stdClass $user, \context $context) {
        global $DB;

        $sql = "SELECT urq.*
                  FROM {local_intellicart_requests} urq
                 WHERE urq.userid = :userid";
        $params = ['userid' => $user->id];
        $records = $DB->get_records_sql($sql, $params);

        if ($records) {
            $data = [];

            foreach ($records as $record) {
                $data[] = (object) [
                    'type'            => $record->type,
                    'productid'       => $record->productid,
                    'checkoutid'      => $record->checkoutid,
                    'status'          => $record->status,
                    'updatedbyuserid' => $record->updatedbyuserid,
                    'notes'           => $record->notes,
                    'timecreated'     => transform::datetime($record->timecreated),
                    'timemodified'    => transform::datetime($record->timemodified),
                ];
            }

            writer::with_context($context)->export_data(
                [get_string('privacy:metadata:local_intellicart_requests', 'local_intellicart')], (object) $data);
        }
    }

    protected static function export_user_local_intellicart_ch_details_data(\stdClass $user, \context $context) {
        global $DB;

        $sql = "SELECT chd.*
                  FROM {local_intellicart_ch_details} chd
                 WHERE chd.userid = :userid";
        $params = ['userid' => $user->id];
        $records = $DB->get_records_sql($sql, $params);

        if ($records) {
            $data = [];

            foreach ($records as $record) {
                $data[] = (object) [
                    'checkoutid'      => $record->checkoutid,
                    'name'            => $record->name,
                    'value'           => $record->value,
                    'timecreated'     => transform::datetime($record->timecreated),
                    'timemodified'    => transform::datetime($record->timemodified),
                ];
            }

            writer::with_context($context)->export_data(
                [get_string('privacy:metadata:local_intellicart_ch_details', 'local_intellicart')], (object) $data);
        }
    }

    /**
     * Export all user preferences for the plugin.
     *
     * @param   int $userid The userid of the user whose data is to be exported.
     */
    public static function export_user_preferences(int $userid) {

        $filteroptions = array(3 => get_string('showall', 'local_intellicart'), 2 => get_string('active', 'local_intellicart'),
                1 => get_string('inactive', 'local_intellicart'));

        $intellicart_categories_filter = get_user_preferences('intellicart_categories_filter', 3, $userid);
        $intellicart_couponsstatus_filter = get_user_preferences('intellicart_couponsstatus_filter', 3, $userid);
        $intellicart_discountsstatus_filter = get_user_preferences('intellicart_discountsstatus_filter', 3, $userid);
        $intellicart_productsstatus_filter = get_user_preferences('intellicart_productsstatus_filter', 3, $userid);
        $intellicart_paymentsstatus_filter = get_user_preferences('intellicart_paymentsstatus_filter', 3, $userid);
        $intellicart_vendorsstatus_filter = get_user_preferences('intellicart_vendorsstatus_filter', 3, $userid);
        $intellicart_usersstatus_filter = get_user_preferences('intellicart_usersstatus_filter', 3, $userid);
        $intellicart_customfields_view_filter = get_user_preferences('intellicart_customfields_view_filter', 3, $userid);
        $intellicart_vendor_filter = get_user_preferences('intellicart_vendor_filter', 3, $userid);
        $intellicart_catsubscriptions_filter = get_user_preferences('intellicart_catsubscriptions_filter', 3, $userid);
        $intellicart_giftcardsstatus_filter = get_user_preferences('intellicart_giftcardsstatus_filter', 3, $userid);
        $intellicart_table_limit = get_user_preferences('intellicart_table_limit', 25, $userid);
        $intellicart_salesstatus_filter = get_user_preferences('intellicart_salesstatus_filter', '', $userid);
        $intellicart_salesbt_filter = get_user_preferences('intellicart_salesbt_filter', '', $userid);
        $intellicart_certificationsstatus_filter = get_user_preferences('intellicart_certificationsstatus_filter', '', $userid);
        $intellicart_certificatestatus_filter = get_user_preferences('intellicart_certificatestatus_filter', '', $userid);
        $intellicart_awardcertificatesstatus_filter = get_user_preferences('intellicart_awardcertificatesstatus_filter', '', $userid);
        $intellicart_billingtype = get_user_preferences('intellicart_billingtype', '', $userid);

        if (null !== $intellicart_categories_filter) {
            writer::export_user_preference(
                    'local_intellicart',
                    'intellicart_categories_filter',
                    $filteroptions[$intellicart_categories_filter],
                    get_string('privacy:metadata:local_intellicart:preferences:intellicart_categories_filter', 'local_intellicart')
            );
        }
        if (null !== $intellicart_couponsstatus_filter) {
            writer::export_user_preference(
                    'local_intellicart',
                    'intellicart_couponsstatus_filter',
                    $filteroptions[$intellicart_couponsstatus_filter],
                    get_string('privacy:metadata:local_intellicart:preferences:intellicart_couponsstatus_filter', 'local_intellicart')
            );
        }
        if (null !== $intellicart_discountsstatus_filter) {
            writer::export_user_preference(
                    'local_intellicart',
                    'intellicart_discountsstatus_filter',
                    $filteroptions[$intellicart_discountsstatus_filter],
                    get_string('privacy:metadata:local_intellicart:preferences:intellicart_discountsstatus_filter', 'local_intellicart')
            );
        }
        if (null !== $intellicart_productsstatus_filter) {
            writer::export_user_preference(
                    'local_intellicart',
                    'intellicart_productsstatus_filter',
                    $filteroptions[$intellicart_productsstatus_filter],
                    get_string('privacy:metadata:local_intellicart:preferences:intellicart_productsstatus_filter', 'local_intellicart')
            );
        }
        if (null !== $intellicart_paymentsstatus_filter) {
            writer::export_user_preference(
                    'local_intellicart',
                    'intellicart_paymentsstatus_filter',
                    $filteroptions[$intellicart_paymentsstatus_filter],
                    get_string('privacy:metadata:local_intellicart:preferences:intellicart_paymentsstatus_filter', 'local_intellicart')
            );
        }
        if (null !== $intellicart_vendorsstatus_filter) {
            writer::export_user_preference(
                    'local_intellicart',
                    'intellicart_vendorsstatus_filter',
                    $filteroptions[$intellicart_vendorsstatus_filter],
                    get_string('privacy:metadata:local_intellicart:preferences:intellicart_vendorsstatus_filter', 'local_intellicart')
            );
        }
        if (null !== $intellicart_usersstatus_filter) {
            writer::export_user_preference(
                    'local_intellicart',
                    'intellicart_usersstatus_filter',
                    $filteroptions[$intellicart_usersstatus_filter],
                    get_string('privacy:metadata:local_intellicart:preferences:intellicart_usersstatus_filter', 'local_intellicart')
            );
        }
        if (null !== $intellicart_customfields_view_filter) {
            writer::export_user_preference(
                    'local_intellicart',
                    'intellicart_customfields_view_filter',
                    $filteroptions[$intellicart_customfields_view_filter],
                    get_string('privacy:metadata:local_intellicart:preferences:intellicart_customfields_view_filter', 'local_intellicart')
            );
        }
        if (null !== $intellicart_vendor_filter) {
            writer::export_user_preference(
                    'local_intellicart',
                    'intellicart_vendor_filter',
                    $filteroptions[$intellicart_vendor_filter],
                    get_string('privacy:metadata:local_intellicart:preferences:intellicart_vendor_filter', 'local_intellicart')
            );
        }
        if (null !== $intellicart_catsubscriptions_filter) {
            writer::export_user_preference(
                    'local_intellicart',
                    'intellicart_catsubscriptions_filter',
                    $filteroptions[$intellicart_catsubscriptions_filter],
                    get_string('privacy:metadata:local_intellicart:preferences:intellicart_catsubscriptions_filter', 'local_intellicart')
            );
        }
        if (null !== $intellicart_giftcardsstatus_filter) {
            writer::export_user_preference(
                    'local_intellicart',
                    'intellicart_giftcardsstatus_filter',
                    $filteroptions[$intellicart_giftcardsstatus_filter],
                    get_string('privacy:metadata:local_intellicart:preferences:intellicart_giftcardsstatus_filter', 'local_intellicart')
            );
        }
        if (null !== $intellicart_table_limit) {
            writer::export_user_preference(
                    'local_intellicart',
                    'intellicart_table_limit',
                    $intellicart_table_limit,
                    get_string('privacy:metadata:local_intellicart:preferences:intellicart_table_limit', 'local_intellicart')
            );
        }
        if (null !== $intellicart_salesstatus_filter) {
            writer::export_user_preference(
                    'local_intellicart',
                    'intellicart_salesstatus_filter',
                    $intellicart_salesstatus_filter,
                    get_string('privacy:metadata:local_intellicart:preferences:intellicart_salesstatus_filter', 'local_intellicart')
            );
        }
        if (null !== $intellicart_salesbt_filter) {
            writer::export_user_preference(
                    'local_intellicart',
                    'intellicart_salesbt_filter',
                    $intellicart_salesbt_filter,
                    get_string('privacy:metadata:local_intellicart:preferences:intellicart_salesbt_filter', 'local_intellicart')
            );
        }
        if (null !== $intellicart_certificationsstatus_filter) {
            writer::export_user_preference(
                    'local_intellicart',
                    'intellicart_certificationsstatus_filter',
                    $intellicart_certificationsstatus_filter,
                    get_string('privacy:metadata:local_intellicart:preferences:intellicart_certificationsstatus_filter', 'local_intellicart')
            );
        }

        if (null !== $intellicart_certificatestatus_filter) {
            writer::export_user_preference(
                'local_intellicart',
                'intellicart_certificatestatus_filter',
                $intellicart_certificatestatus_filter,
                get_string('privacy:metadata:local_intellicart:preferences:intellicart_certificatestatus_filter', 'local_intellicart')
            );
        }

        if (null !== $intellicart_awardcertificatesstatus_filter) {
            writer::export_user_preference(
                'local_intellicart',
                'intellicart_awardcertificatesstatus_filter',
                $intellicart_awardcertificatesstatus_filter,
                get_string('privacy:metadata:local_intellicart:preferences:intellicart_awardcertificatesstatus_filter', 'local_intellicart')
            );
        }

        if (null !== $intellicart_billingtype) {
            writer::export_user_preference(
                'local_intellicart',
                'intellicart_billingtype',
                $intellicart_billingtype,
                get_string('privacy:metadata:local_intellicart:preferences:intellicart_billingtype', 'local_intellicart')
            );
        }
    }

    /**
     * Delete all use data which matches the specified context.
     *
     * @param context $context A user context.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        // Delete data only for user context.
        if ($context->contextlevel == CONTEXT_USER) {
            static::delete_data($context->instanceid);
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        if (empty($contextlist->count())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            static::delete_data($userid);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(\core_privacy\local\request\approved_userlist $userlist) {
        $context = $userlist->get_context();

        $users = $userlist->get_userids();
        foreach ($users as $userid) {
            static::delete_data($userid);
        }
    }

    /**
     * Get the list of users within a specific context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(\core_privacy\local\request\userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_user) {
            return;
        }

        $params = [
            'contextuser' => CONTEXT_USER,
            'contextid' => $context->id,
        ];

        // Table local_intellicart.
        $sql = "SELECT ctx.instanceid as userid
                  FROM {local_intellicart} ic
                  JOIN {context} ctx
                       ON ctx.instanceid = ic.userid
                       AND ctx.contextlevel = :contextuser
                 WHERE ctx.id = :contextid";
        $userlist->add_from_sql('userid', $sql, $params);

        // Table local_intellicart_checkout.
        $sql = "SELECT ctx.instanceid as userid
                  FROM {local_intellicart_checkout} ch
                  JOIN {context} ctx
                       ON ctx.instanceid = ch.userid
                       AND ctx.contextlevel = :contextuser
                 WHERE ctx.id = :contextid";
        $userlist->add_from_sql('userid', $sql, $params);

        // Table local_intellicart_logs.
        $sql = "SELECT ctx.instanceid as userid
                  FROM {local_intellicart_logs} l
                  JOIN {context} ctx
                       ON ctx.instanceid = l.userid
                       AND ctx.contextlevel = :contextuser
                 WHERE ctx.id = :contextid";
        $userlist->add_from_sql('userid', $sql, $params);

        // Table local_intellicart_waitlist.
        $sql = "SELECT ctx.instanceid as userid
                  FROM {local_intellicart_waitlist} w
                  JOIN {context} ctx
                       ON ctx.instanceid = w.userid
                       AND ctx.contextlevel = :contextuser
                 WHERE ctx.id = :contextid";
        $userlist->add_from_sql('userid', $sql, $params);

        // Table local_intellicart_users.
        $sql = "SELECT ctx.instanceid as userid
                  FROM {local_intellicart_users} iu
                  JOIN {context} ctx
                       ON ctx.instanceid = iu.userid
                       AND ctx.contextlevel = :contextuser
                 WHERE ctx.id = :contextid";
        $userlist->add_from_sql('userid', $sql, $params);

        // Table local_intellicart_history.
        $sql = "SELECT ctx.instanceid as userid
                  FROM {local_intellicart_history} h
                  JOIN {context} ctx
                       ON ctx.instanceid = h.userid
                       AND ctx.contextlevel = :contextuser
                 WHERE ctx.id = :contextid";
        $userlist->add_from_sql('userid', $sql, $params);

        // Table local_intellicart_seats.
        $sql = "SELECT ctx.instanceid as userid
                  FROM {local_intellicart_seats} s
                  JOIN {context} ctx
                       ON ctx.instanceid = s.userid
                       AND ctx.contextlevel = :contextuser
                 WHERE ctx.id = :contextid";
        $userlist->add_from_sql('userid', $sql, $params);

        // Table local_intellicart_subscr.
        $sql = "SELECT ctx.instanceid as userid
                  FROM {local_intellicart_subscr} sub
                  JOIN {context} ctx
                       ON ctx.instanceid = sub.userid
                       AND ctx.contextlevel = :contextuser
                 WHERE ctx.id = :contextid";
        $userlist->add_from_sql('userid', $sql, $params);

        // Table local_intellicart_shipping.
        $sql = "SELECT ctx.instanceid as userid
                  FROM {local_intellicart_shipping} sh
                  JOIN {context} ctx
                       ON ctx.instanceid = sh.userid
                       AND ctx.contextlevel = :contextuser
                 WHERE ctx.id = :contextid";
        $userlist->add_from_sql('userid', $sql, $params);

        // Table local_intellicart_wishlist.
        $sql = "SELECT ctx.instanceid as userid
                  FROM {local_intellicart_wishlist} wsh
                  JOIN {context} ctx
                       ON ctx.instanceid = wsh.userid
                       AND ctx.contextlevel = :contextuser
                 WHERE ctx.id = :contextid";
        $userlist->add_from_sql('userid', $sql, $params);

        // Table local_intellicart_reviews.
        $sql = "SELECT ctx.instanceid as userid
                  FROM {local_intellicart_reviews} rw
                  JOIN {context} ctx
                       ON ctx.instanceid = rw.userid
                       AND ctx.contextlevel = :contextuser
                 WHERE ctx.id = :contextid";
        $userlist->add_from_sql('userid', $sql, $params);

        // Table local_intellicart_reviews.
        $sql = "SELECT ctx.instanceid as userid
                  FROM {local_intellicart_comments} com
                  JOIN {context} ctx
                       ON ctx.instanceid = com.userid
                       AND ctx.contextlevel = :contextuser
                 WHERE ctx.id = :contextid";
        $userlist->add_from_sql('userid', $sql, $params);

        // Table local_intellicart_likes.
        $sql = "SELECT ctx.instanceid as userid
                  FROM {local_intellicart_likes} lk
                  JOIN {context} ctx
                       ON ctx.instanceid = lk.userid
                       AND ctx.contextlevel = :contextuser
                 WHERE ctx.id = :contextid";
        $userlist->add_from_sql('userid', $sql, $params);

        // Table local_intellicart_catsubscru.
        $sql = "SELECT ctx.instanceid as userid
                  FROM {local_intellicart_catsubscru} csub
                  JOIN {context} ctx
                       ON ctx.instanceid = csub.userid
                       AND ctx.contextlevel = :contextuser
                 WHERE ctx.id = :contextid";
        $userlist->add_from_sql('userid', $sql, $params);

        // Table local_intellicart_icheckout.
        $sql = "SELECT ctx.instanceid as userid
                  FROM {local_intellicart_icheckout} ich
                  JOIN {context} ctx
                       ON ctx.instanceid = ich.userid
                       AND ctx.contextlevel = :contextuser
                 WHERE ctx.id = :contextid";
        $userlist->add_from_sql('userid', $sql, $params);

        // Table local_intellicart_erequests.
        $sql = "SELECT ctx.instanceid as userid
                  FROM {local_intellicart_erequests} er
                  JOIN {context} ctx
                       ON ctx.instanceid = er.userid
                       AND ctx.contextlevel = :contextuser
                 WHERE ctx.id = :contextid";
        $userlist->add_from_sql('userid', $sql, $params);

        // Table local_intellicart_cissues.
        $sql = "SELECT ctx.instanceid as userid
                  FROM {local_intellicart_cissues} cis
                  JOIN {context} ctx
                       ON ctx.instanceid = cis.userid
                       AND ctx.contextlevel = :contextuser
                 WHERE ctx.id = :contextid";
        $userlist->add_from_sql('userid', $sql, $params);

        // Table local_intellicart_cpages.
        $sql = "SELECT ctx.instanceid as userid
                  FROM {local_intellicart_cpages} cpg
                  JOIN {context} ctx
                       ON ctx.instanceid = cpg.userid
                       AND ctx.contextlevel = :contextuser
                 WHERE ctx.id = :contextid";
        $userlist->add_from_sql('userid', $sql, $params);

        // Table local_intellicart_requests.
        $sql = "SELECT ctx.instanceid AS userid
                  FROM {local_intellicart_requests} urq
                  JOIN {context} ctx
                       ON ctx.instanceid = urq.userid
                       AND ctx.contextlevel = :contextuser
                 WHERE ctx.id = :contextid";
        $userlist->add_from_sql('userid', $sql, $params);

        // Table local_intellicart_ch_details.
        $sql = "SELECT ctx.instanceid AS userid
                  FROM {local_intellicart_ch_details} chd
                  JOIN {context} ctx
                       ON ctx.instanceid = chd.userid
                       AND ctx.contextlevel = :contextuser
                 WHERE ctx.id = :contextid";
        $userlist->add_from_sql('userid', $sql, $params);
    }

    /**
     * Delete data related to a context and user (if defined).
     *
     * @param context $context A context.
     * @param int $userid The user ID.
     */
    protected static function delete_data(int $userid = null) {
        global $DB;

        $params = ['userid' => $userid];

        // Table local_intellicart.
        $DB->delete_records('local_intellicart', $params);

        // Table local_intellicart_checkout.
        $DB->delete_records('local_intellicart_checkout', $params);

        // Table local_intellicart_logs.
        $DB->delete_records('local_intellicart_logs', $params);

        // Table local_intellicart_waitlist.
        $DB->delete_records('local_intellicart_waitlist', $params);

        // Table local_intellicart_users.
        $DB->delete_records('local_intellicart_users', $params);

        // Table local_intellicart_history.
        $DB->delete_records('local_intellicart_history', $params);

        // Table local_intellicart_seats.
        $DB->delete_records('local_intellicart_seats', $params);

        // Table local_intellicart_subscr.
        $DB->delete_records('local_intellicart_subscr', $params);

        // Table local_intellicart_shipping.
        $DB->delete_records('local_intellicart_shipping', $params);

        // Table local_intellicart_wishlist.
        $DB->delete_records('local_intellicart_wishlist', $params);

        // Table local_intellicart_reviews.
        $DB->delete_records('local_intellicart_reviews', $params);

        // Table local_intellicart_comments.
        $DB->delete_records('local_intellicart_comments', $params);

        // Table local_intellicart_likes.
        $DB->delete_records('local_intellicart_likes', $params);

        // Table local_intellicart_catsubscru.
        $DB->delete_records('local_intellicart_catsubscru', $params);

        // Table local_intellicart_icheckout.
        $DB->delete_records('local_intellicart_icheckout', $params);

        // Table local_intellicart_erequests.
        $DB->delete_records('local_intellicart_erequests', $params);

        // Table local_intellicart_cissues.
        $DB->delete_records('local_intellicart_cissues', $params);

        // Table local_intellicart_cpages.
        $DB->delete_records('local_intellicart_cpages', $params);

        // Table local_intellicart_requests.
        $DB->delete_records('local_intellicart_requests', $params);

        // Table local_intellicart_ch_details.
        $DB->delete_records('local_intellicart_ch_details', $params);
    }
}
