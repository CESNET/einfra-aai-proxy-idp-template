## [4.0.2](https://github.com/CESNET/einfra-aai-proxy-idp-template/compare/v4.0.1...v4.0.2) (2022-06-08)


### Bug Fixes

* update einfraCZ templates ([1a36322](https://github.com/CESNET/einfra-aai-proxy-idp-template/commit/1a363227c01c404e44f6459a17c5aee6a6de6ce6))

## [4.0.1](https://github.com/CESNET/einfra-aai-proxy-idp-template/compare/v4.0.0...v4.0.1) (2022-06-08)


### Bug Fixes

* fix ECS ([1ccc75c](https://github.com/CESNET/einfra-aai-proxy-idp-template/commit/1ccc75c249f6212dc3702e9a8b8dc0a2137c59e2))

# [4.0.0](https://github.com/CESNET/einfra-aai-proxy-idp-template/compare/v3.1.5...v4.0.0) (2022-05-20)


### Features

* new template for privacyIDEA included from module Perun ([8f9a8ec](https://github.com/CESNET/einfra-aai-proxy-idp-template/commit/8f9a8ecbc3ca16d941f3f0c896e043728b1811ec))


### BREAKING CHANGES

* requires cesnet/simplesamlphp-module-perun >= v8

## [3.1.5](https://github.com/CESNET/einfra-aai-proxy-idp-template/compare/v3.1.4...v3.1.5) (2022-01-20)


### Bug Fixes

* fix dependency on perun-module ([f805f93](https://github.com/CESNET/einfra-aai-proxy-idp-template/commit/f805f934e06c6851a3fdc5c434f3a22deea1edc2))
* update ECS, update configuration and fix the code ([ad38c1d](https://github.com/CESNET/einfra-aai-proxy-idp-template/commit/ad38c1d17b2adfca243d29af587ee87040f957d3))

## [3.1.4](https://github.com/CESNET/einfra-aai-proxy-idp-template/compare/v3.1.3...v3.1.4) (2021-12-03)


### Bug Fixes

* change the eINFRA black small logo ([c2d5c3e](https://github.com/CESNET/einfra-aai-proxy-idp-template/commit/c2d5c3e815a438ad59ff0b7de02728ca9b7eb64a))

## [3.1.3](https://github.com/CESNET/einfra-aai-proxy-idp-template/compare/v3.1.2...v3.1.3) (2021-12-01)


### Bug Fixes

* add missing small version of eINFRA CZ black logo ([26d041e](https://github.com/CESNET/einfra-aai-proxy-idp-template/commit/26d041e94c2bf31b9e7ee0b092ec173326631536))

## [3.1.2](https://github.com/CESNET/einfra-aai-proxy-idp-template/compare/v3.1.1...v3.1.2) (2021-11-24)


### Bug Fixes

* update footer ([d1fa85f](https://github.com/CESNET/einfra-aai-proxy-idp-template/commit/d1fa85fcef00cf828b0aa88a3ede014fac5b75cc))

## [3.1.1](https://github.com/CESNET/einfra-aai-proxy-idp-template/compare/v3.1.0...v3.1.1) (2021-11-22)


### Bug Fixes

* fix bad mail address in footer ([2fc92c0](https://github.com/CESNET/einfra-aai-proxy-idp-template/commit/2fc92c0a1a3337897f28d2dee7532524dabb85e3))

# [3.1.0](https://github.com/CESNET/einfra-aai-proxy-idp-template/compare/v3.0.3...v3.1.0) (2021-11-15)


### Features

* prepare theme:einfra_idp ([9c3abf1](https://github.com/CESNET/einfra-aai-proxy-idp-template/commit/9c3abf1285169d8e4cc053d06b32e530a6ca2f0b))

## [3.0.3](https://github.com/CESNET/einfra-aai-proxy-idp-template/compare/v3.0.2...v3.0.3) (2021-11-15)


### Bug Fixes

* fix duplicate or unused consts ([ef595e0](https://github.com/CESNET/einfra-aai-proxy-idp-template/commit/ef595e0a972531b8fc7fbd5893d6d7994721105d))

## [3.0.2](https://github.com/CESNET/einfra-aai-proxy-idp-template/compare/v3.0.1...v3.0.2) (2021-11-15)


### Bug Fixes

* fix the translations ([7afa534](https://github.com/CESNET/einfra-aai-proxy-idp-template/commit/7afa53422a1afc23c338600d8e1b3836f4bca85d))

## [3.0.1](https://github.com/CESNET/einfra-aai-proxy-idp-template/compare/v3.0.0...v3.0.1) (2021-11-15)


### Bug Fixes

* change page width for e-infra template ([2966f9e](https://github.com/CESNET/einfra-aai-proxy-idp-template/commit/2966f9ea260e4ce103bfcad51ac42ee9fb67c83d))
* changes in e-INFRA CZ template footer ([40caab3](https://github.com/CESNET/einfra-aai-proxy-idp-template/commit/40caab3bb81bdcda1a3ae5451582c4b4ec8a3403))

# Change Log
All notable changes to this project will be documented in this file.

## [Unreleased]

## [v3.0.0]
#### Added
- Security improvements in script calls
* Add theme 'einfra'

#### Fixed
- isCesnetEligible - wrong 'attribute was set' check
- Each log has just one line output

# [v2.7.0]
#### Changed
* Updated disco-tpl.php to be compatible with the new style of configuration from module Perun v5.0

## [v2.6.1]
#### Fixed
* Fixed the bug in case, that some field from CESNET LDAP has some space around the value

#### Changed
* Change logo for Facebook

## [v2.6.0]
#### Changed
* Process filter isCesnetEligible now store boolean attribute to $request['attributes']
    * TRUE if isCesnetEligible is not older than one year, FALSE otherwise

## [v2.5.2]
#### Fixed
* Fixed some bugs in counting isCesnetElibile for sponsored accounts

## [v2.5.1]
#### Fixed
* Use the correct rpc method for get user attributes from rpc for perun-simplesamlphp-module in version 3.x in IsCesnetEligible.php

## [v2.5.0]
#### Added
- Add support for isCesnetEligible for sponsored accounts

#### Changed
- Compare eduPersonScopedAffiliation instead of unscopedAffiliation in isCesnetEligible.php

## [v2.4.0]
#### Addded
- Added apple logo

## [v2.3.3]
#### Fixed
- Changes in footer
    * Removed FAX contact
    * Using multi-language links

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
[v3.0.0]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v3.0.0
[v2.7.0]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v2.7.0
[v2.6.1]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v2.6.1
[v2.6.0]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v2.6.0
[v2.5.2]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v2.5.2
[v2.5.1]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v2.5.1
[v2.5.0]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v2.5.0
[v2.4.0]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v2.4.0
[v2.3.3]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v2.3.3
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
