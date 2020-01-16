<?php

namespace SimpleSAML\Module\cesnet\Auth\Process;

use DateTime;
use SimpleSAML\Auth\ProcessingFilter;
use SimpleSAML\Auth\State;
use SimpleSAML\Configuration;
use SimpleSAML\Locale\Translate;
use SimpleSAML\Logger;
use SimpleSAML\Module;
use SimpleSAML\Module\perun\Exception;
use SimpleSAML\Session;
use SimpleSAML\Utils\HTTP;

/**
 * Class CheckIsCesnetEligible
 * @package SimpleSAML\Module\cesnet\Auth\Process
 *
 * @author Pavel Vyskocil <pavel.vyskocil@cesnet.cz>
 */
class CheckIsCesnetEligible extends ProcessingFilter
{
    const PAGE_HEADER = '{cesnet:einfra:check_cesnet_eligible-header}';
    const PAGE_TEXT_OLD_VALUE = '{cesnet:einfra:check_cesnet_eligible-old_value_text}';
    const PAGE_TEXT_NONE_VALUE = '{cesnet:einfra:check_cesnet_eligible-none_value_text}';
    const IS_CESNET_ELIGIBLE = 'isCesnetEligibleLastSeen';

    public function __construct($config, $reserved)
    {
        parent::__construct($config, $reserved);
    }

    public function process(&$request)
    {
        assert('is_array($request)');

        // Get SP required attributes
        $requiredAttributes = [];
        if (isset($request['Destination']['attributes'])) {
            $requiredAttributes = $request['Destination']['attributes'];
        }

        if (!in_array(self::IS_CESNET_ELIGIBLE, $requiredAttributes)) {
            Logger::debug('cesnet:CheckIsCesnetEligible - SP didn\'t request attribute \'isCesnetEligible\'. 
            Skipping to next filter. ');
            return;
        }

        $isCesnetEligibleLastSeenString = null;
        if (isset($request['Attributes'][self::IS_CESNET_ELIGIBLE])) {
            $isCesnetEligibleLastSeenString = $request['Attributes'][self::IS_CESNET_ELIGIBLE][0];
        } else {
            Logger::debug('cesnet:CheckIsCesnetEligible - SP requested attribute \'isCesnetEligible\', but user 
            didn\'t have any value in attribute. => Redirected to unauthorized page.');
            $this->unauthorized();
        }

        $isCesnetEligible = DateTime::createFromFormat('Y-m-d H:i:s', $isCesnetEligibleLastSeenString);
        $isCesnetEligibleExpiration = $isCesnetEligible->modify('+12months');
        $now = new DateTime();

        if ($isCesnetEligibleExpiration < $now) {
            Logger::debug('cesnet:CheckIsCesnetEligible - SP requested attribute \'isCesnetEligible\', but 
            value is too old.');
            $this->unauthorized(true);
        }
        Logger::debug('cesnet:CheckIsCesnetEligible - SP requested attribute \'isCesnetEligible\',
            value is OK. Skipping to next filter.');
    }

    public function unauthorized($hasValue = false)
    {
        $id = State::saveState($request, 'perunauthorize:Perunauthorize');
        $url = Module::getModuleURL('perunauthorize/perunauthorize_custom.php');

        try {
            $session = Session::getSessionFromRequest();
            $session->doLogout('default-sp');
        } catch (Exception $exception) {
            Logger::debug('cesnet:CheckIsCesnetEligible - Error when logging user out. User didn\' logged out!');
        }

        if ($hasValue) {
            $textTag = self::PAGE_TEXT_OLD_VALUE;
        } else {
            $textTag = self::PAGE_TEXT_NONE_VALUE;
        }

        HTTP::redirectTrustedURL(
            $url,
            [
                'StateId' => $id,
                '403_header_tag' => self::PAGE_HEADER,
                '403_text_tag' => $textTag,
            ]
        );
    }
}
