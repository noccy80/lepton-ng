<?php

class XsnpAuthorization {

    const SUB_BOTH = 'both';
    const SUB_PENDING = 'pending';
    const SUB_NONE = 'none';

    public function __construct(UserRecord $user) {
    }

    public function addSubscription($identity) {
    }

    public function removeSubscription($identity) {
    }

    public function getPendingSubscriptionRequests() {
    }

    public function acceptSubscriptionRequest($id) {
    }

    public function removeSubscriptionRequest($id) {
    }

    public function listSubscriptions() {
    }

    public function checkSubscription($identity) {
    }

}
