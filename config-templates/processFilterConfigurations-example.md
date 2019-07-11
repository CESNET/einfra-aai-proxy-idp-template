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

* Interface says if attribute will be read from RPC or LDAP
* If interface is LDAP, LDAP.attributeName has to be filled
* RPC.attributeName has to be filled
* Put something like this into saml20-idp-hosted.php:

    ```php
    25 => [
        'class' => 'cesnet:IsCesnetEligible',
        'interface' => 'RPC/LDAP',
        'RPC.attributeName' => 'urn:perun:user:attribute-def:def:isCesnetEligibleLastSeen',
        'LDAP.attributeName' => 'isCesnetEligible',
    ],
    ```