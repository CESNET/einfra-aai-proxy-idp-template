# Change Log
All notable changes to this project will be documented in this file.

## [Unreleased]

## [v1.1.3]
[Changed]
- Fixed the pattern in isCesnetEligibleLastSeen
- Changed the email address for support in template

## [v1.1.2]
[Changed]
- Fixed the filter for isCesnetEligibleLastSeen that checks the eduPersonScopedAffiliations
- Fixed the bug, that attribute isCesnetEligibleLastSeen wasn't filled for Hostel Idp

## [v1.1.1]
[Changed]
- Fixed the bug in isCesnetEligibleLastSeen and ComputeLoA, that throw error when the attribute 'EntityCategory' isn't in IdP metadata or if UserAttributes 'eduPersonScopedAffiliation' isn't received from IdP.
- Removed the line in disco-tpl that log defaultFilter without any other information

## [v1.1.0]
[Added]
- Added support for Czech language for reporting error 
- Class sspmod_cesnet_Auth_Process_ComputeLoA for compute Level of Assurance
- Class sspmod_cesnet_Auth_Process_IsCesnetEligible for get the timestamp of last login with account that pass through 
the eduid filter (More information about this filter you can get [here] )

[here]: https://www.eduid.cz/en/tech/userfiltering#include_filter

[Changed]
- Removed all deprecated items from dictionaries
- Filling email is now required for reporting error

## [v1.0.0]
[Added]
- Changelog

[Unreleased]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/master
[v1.1.0]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v1.1.3
[v1.1.0]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v1.1.2
[v1.1.0]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v1.1.1
[v1.1.0]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v1.1.0
[v1.0.0]: https://github.com/CESNET/einfra-aai-proxy-idp-template/tree/v1.0.0