## ComputeLoA
Example how to configure ComputeLoA filter:

* Put something like this into saml20-idp-hosted.php:

    ```php
    11 => [
            'class' => 'cesnet:ComputeLoA',
    ],
    ```


## IsCesnetEligible
Example how to configure IsCesnetEligible filter:

* Put something like this into saml20-idp-hosted.php:

    ```php
    25 => [
            'class' => 'cesnet:IsCesnetEligible',
                    'cesnetEligibleLastSeenAttr' => 'urn:perun:user:attribute-def:def:isCesnetEligibleLastSeen',
                    'listOfPerunEntityIds' => ['entityId1', 'entityId2'],
    ],
    ```