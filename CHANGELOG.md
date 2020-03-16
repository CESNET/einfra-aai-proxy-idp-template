# Change Log
All notable changes to this project will be documented in this file.

## [Unreleased]

## [v2.3.2]
#### Fixed
- Removed CERIT logo from the footer

## [v2.3.1]
#### Fixed
- Log error when CESNET LDAP return more than one item in getAllowedAffiliations() in IsCesnetEligible.php

## [v2.3.0]
#### Changed
- Updating 'IsCesnetEligible' attribute in Perun asynchronously

#### Fixed
- Text in h1 tag in header is no longer as link
- Fixed color of h2 in footer
 
## [v2.2.2]
#### Fixed
- IsCesnetEligible - Resolved problem with poor evaluation resolving, if user was or wasn't verified by Hostel

## [v2.2.1]
#### Fixed
- Changed cesnet.css: color of h[1-6] is no longer overwritten by other css files

## [v2.2.0]
#### Fixed
- Fixed the bug where isCesnetEligibleLastSeen was not set if user is empty

#### Changed
- Warning in disco-tpl modified due to changes in module perun
- Removed warning template - it is no longer needed here because it was moved to module perun
- is_null() changed to === null
- == and != changed to === and !==
- Double quotes changed to single quotes

## [v2.1.0]
#### Added
- Possibility to read isCesnetEligible attribute from LDAP

#### Changed
- Using of short array syntax ([] instead of array())

#### Fixed
- Fixed the style of changelog

## [v2.0.0]
#### Added
- Added file phpcs.xml

#### Changed
- Changed code style to PSR-2
- Module uses namespaces
- Some templates are included from module perun

## [v1.4.2]
#### Fixed
- Added verification for empty response from LDAP in IsCesnetEligible::getAllowedAffiliations which is valid state

## [v1.4.1]
#### Fixed
- Set the default LoA to 0 for IdP without or with empty attribute 'entityCategory'

## [v1.4.0]
#### Changed
- Changed ComputeLoA Process filter to add LoA=2 for people from university with affiliation='alum'  

#### Fixed
- Fix for situation when request does not contain Perun data

## [v1.3.1]
#### Fixed
- Fixed required version of module 'cesnet/simplesamlphp-module-perun'
- Fixed css

## [v1.3.0]
#### Added
- Added logo for Metacentrum

## [v1.2.0]
#### Added
- Added License
- Possibility to show a warning in disco-tpl
- Added example how to configure ComputeLoA process filter

#### Changed
- Updated README
- Changed calls of RpcConnector methods (More information about changes in connectors and adapters in perun-simplesamlphp-module you can find in this [PR])
- Filter IsCesnetEligible is now computed by combination of attributes from user and CESNET LDAP

[PR]:https://github.com/CESNET/perun-simplesamlphp-module/pull/34

#### Fixed
- Fixed duplicate property
- Fixed the bug with hidden language bar if the $_POST is not empty

#### Removed
- Removed function present_attributes($t, $attributes, $nameParent) from consentform.php

## [v1.1.5]
#### Fixed
-Fixed translations

## [v1.1.4]
#### Added
- Added logos for Social Providers 

#### Changed
- Whole module now uses a dictionary

#### Fixed
- Fixed the changelog

## [v1.1.3]
#### Changed
- Fixed the pattern in isCesnetEligibleLastSeen
- Changed the email address for support in template

## [v1.1.2]
#### Changed
- Fixed the filter for isCesnetEligibleLastSeen that checks the eduPersonScopedAffiliations
- Fixed the bug, that attribute isCesnetEligibleLastSeen wasn't filled for Hostel Idp

## [v1.1.1]
#### Changed
- Fixed the bug in isCesnetEligibleLastSeen and ComputeLoA, that throw error when the attribute 'EntityCategory' isn't in IdP metadata or if UserAttributes 'eduPersonScopedAffiliation' isn't received from IdP.
- Removed the line in disco-tpl that log defaultFilter without any other information

## [v1.1.0]
#### Added
- Added support for Czech language for reporting error 
- Class sspmod_cesnet_Auth_Process_ComputeLoA for compute Level of Assurance
- Class sspmod_cesnet_Auth_Process_IsCesnetEligible for get the timestamp of last login with account that pass through 
the eduid filter (More information about this filter you can get [here] )

[here]: https://www.eduid.cz/en/tech/userfiltering#include_filter

#### Changed
- Removed all deprecated items from dictionaries
- Filling email is now required for reporting error

## [v1.0.0]
#### Added
- Changelog

[Unreleased]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/master
[v2.3.2]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v2.3.2
[v2.3.1]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v2.3.1
[v2.3.0]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v2.3.0
[v2.2.2]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v2.2.2
[v2.2.1]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v2.2.1
[v2.2.0]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v2.2.0
[v2.1.0]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v2.1.0
[v2.0.0]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v2.0.0
[v1.4.2]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v1.4.2
[v1.4.1]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v1.4.1
[v1.4.0]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v1.4.0
[v1.3.1]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v1.3.1
[v1.3.0]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v1.3.0
[v1.2.0]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v1.2.0
[v1.1.5]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v1.1.5
[v1.1.4]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v1.1.4
[v1.1.3]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v1.1.3
[v1.1.2]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v1.1.2
[v1.1.1]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v1.1.1
[v1.1.0]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v1.1.0
[v1.0.0]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v1.0.0
