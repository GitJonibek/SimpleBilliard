<?php
App::import('Service', 'AppService');

/**
 * Class ApiStripeService
 */
class CreditCardService extends AppService
{
    /**
     * Accept a credit card token and register it as new customer on Stripe.
     *
     * @param string      $token
     * @param string      $email
     * @param string|null $description
     *
     * @return array
     */
    public function registerCustomer(string $token, string $email, string $description = null): array
    {
        $result = [
            "error"   => false,
            "message" => null
        ];

        if (empty($token) || empty($email)) {
            $result["error"] = true;
            $result["message"] = __("Parameter is invalid.");

            return $result;
        }

        $customer = array(
            "source"      => $token,
            "email"       => $email,
            "description" => $description
        );

        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

        try {
            $response = \Stripe\Customer::create($customer);

            $result["customer_id"] = $response->id;
            $result["card"] = $response->sources->data[0];
        } catch (Exception $e) {
            $result["error"] = true;
            $result["message"] = $e->getMessage();

            if (property_exists($e, "stripeCode")) {
                $result["errorCode"] = $e->stripeCode;
            }

            $this->log(sprintf("[%s]%s", __METHOD__, $e->getMessage()));
            $this->log($e->getTraceAsString());
        }

        return $result;
    }

    /**
     * Delete customer from Stripe
     *
     * @param string $customerId
     *
     * @return array
     */
    public function deleteCustomer(string $customerId): array
    {
        $result = [
            "error"   => false,
            "message" => null
        ];

        if (empty($customerId)) {
            $result["error"] = true;
            $result["message"] = __("Parameter is invalid.");

            return $result;
        }

        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

        try {
            $customer = \Stripe\Customer::retrieve($customerId);
            $response = $customer->delete();

            $result["deleted"] = $response->deleted;
        } catch (Exception $e) {
            $result["error"] = true;
            $result["message"] = $e->getMessage();

            if (property_exists($e, "stripeCode")) {
                $result["errorCode"] = $e->stripeCode;
            }

            $this->log(sprintf("[%s]%s", __METHOD__, $e->getMessage()));
            $this->log($e->getTraceAsString());
        }

        return $result;
    }

    /**
     * Charge an existing customer from Stripe
     *
     * @param string $customerId
     * @param string $currency
     * @param float  $value
     * @param string $description
     *
     * @return array
     */
    public function chargeCustomer(string $customerId, string $currency, float $value, string $description)
    {
        $result = [
            "error"   => false,
            "message" => null
        ];

        // Validate Customer
        if (empty($customerId)) {
            $result["error"] = true;
            $result["message"] = __("Parameter is invalid.");
            $result["field"] = 'customerId';
            return $result;
        }

        // validate currency
        if (empty($currency) || $value <= 0) {
            $result["error"] = true;
            $result["message"] = __("Parameter is invalid.");
            $result["field"] = 'currency';
            return $result;
        }

        // Validate Value
        if ($value <= 0) {
            $result["error"] = true;
            $result["message"] = __("Parameter is invalid.");
            $result["field"] = 'value';
            return $result;
        }

        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

        $charge = [
            'customer' => $customerId,
            'amount' => $value,
            'currency' => $currency,
            'description' => $description
        ];

        try {
            $response = \Stripe\Charge::create($charge);

            $result["success"] = $response->paid;
            $result["paymentId"] = $response->id;
            $result["status"] = $response->status;
            $result["paymentData"] = $response;
        } catch (Exception $e) {
            $result["error"] = true;
            $result["message"] = $e->getMessage();

            if (property_exists($e, "stripeCode")) {
                $result["errorCode"] = $e->stripeCode;
            }

            $this->log(sprintf("[%s]%s", __METHOD__, $e->getMessage()));
            $this->log($e->getTraceAsString());
        }

        return $result;
    }

    /**
     * Return a list with all registered customers on Stripe
     *
     * @return array
     */
    public function listAllCustomers()
    {
        $result = [
            "error"   => false,
            "message" => null
        ];

        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

        $list = array();
        $options = array(
            "limit" => 100 // 100 is the maximum that can be fetch in as single request. Default is 10
        );

        try {
            do {
                // Get a list of 100 customers
                $response = \Stripe\Customer::all($options);
                $list = am($list, $response->data);
                $hasMore = $response->has_more;

                // If there is more to be fetch, add to the list.
                if ($hasMore) {
                    $item = $list[count($list) -1];
                    $options = am($options, ["starting_after" => $item["id"]]);
                }
            } while($hasMore);

            $result["customers"] = $list;
        }
        catch (Exception $e) {
            $result["error"] = true;
            $result["message"] = $e->getMessage();

            if (property_exists($e, "stripeCode")) {
                $result["errorCode"] = $e->stripeCode;
            }

            $this->log(sprintf("[%s]%s", __METHOD__, $e->getMessage()));
            $this->log($e->getTraceAsString());
        }
        return $result;
    }
}
