<?php

declare(strict_types=1);

namespace SimpleSAML\Module\cesnet\Auth\Process;

use SimpleSAML\Logger;
use SimpleSAML\Metadata\MetaDataStorageHandler;

/**
 * Class ComputeLoA.
 *
 * Filter compute the LoA and save it to attribute defined by 'attrName' config property.
 */
class ComputeLoA extends \SimpleSAML\Auth\ProcessingFilter
{
    public const UNIVERSITY = 'university';

    public const AVCR = 'avcr';

    public const LIBRARY = 'library';

    public const HOSPITAL = 'hospital';

    public const OTHER = 'other';

    public const EDUID_IDP_GROUP = 'http://eduid.cz/uri/idp-group/';

    public const DEFAULT_ATTR_NAME = 'loa';

    private $attrName;

    private $metadata;

    private $entityCategory;

    private $eduPersonScopedAffiliation = [];

    public function __construct($config, $reserved)
    {
        parent::__construct($config, $reserved);

        if (isset($config['attrName'])) {
            $this->attrName = $config['attrName'];
        } else {
            $this->attrName = self::DEFAULT_ATTR_NAME;
        }
    }

    public function process(&$request)
    {
        assert('is_array($request)');

        if (isset($request['Attributes'][$this->attrName])) {
            return;
        }
        $this->metadata = MetaDataStorageHandler::getMetadataHandler();
        $sourceIdpMeta = $this->metadata->getMetaData($request['saml:sp:IdP'], 'saml20-idp-remote');

        if (isset($sourceIdpMeta['EntityAttributes']['http://macedir.org/entity-category'])) {
            $entityCategoryAttributes = $sourceIdpMeta['EntityAttributes']['http://macedir.org/entity-category'];
        } else {
            Logger::error('cesnet:ComputeLoA - There are no element with name \'EntityAttributes\' '
                . 'and subelement with name \'http://macedir.org/entity-category\' in metadata for IdP with entityId '
                . $request['saml:sp:IdP'] . '!');
            $entityCategoryAttributes = [];
        }

        if (isset($request['Attributes']['eduPersonScopedAffiliation'])) {
            $this->eduPersonScopedAffiliation = $request['Attributes']['eduPersonScopedAffiliation'];
        } else {
            Logger::error(
                'cesnet:ComputeLoA - Attribute with name \'eduPersonScopedAffiliation\' did not received from IdP!'
            );
        }

        foreach ($entityCategoryAttributes as $entityCategoryAttribute) {
            if (substr($entityCategoryAttribute, 0, strlen(self::EDUID_IDP_GROUP)) === self::EDUID_IDP_GROUP) {
                $this->entityCategory = substr(
                    $entityCategoryAttribute,
                    strlen(self::EDUID_IDP_GROUP),
                    strlen($entityCategoryAttribute) - strlen(self::EDUID_IDP_GROUP)
                );
            }
        }

        $loa = $this->getLoA();

        $request['Attributes'][$this->attrName] = [$loa];
        Logger::debug('cesnet:ComputeLoA: loa \'$loa\' was saved to attribute ' . $this->attrName);
    }

    /**
     * Get LoA by CESNET filter.
     *
     * @return int 2 if combination of IdP attributes and User attributes corresponds to the filter, 0 if not
     */
    private function getLoA()
    {
        if ($this->entityCategory === null || empty($this->entityCategory)) {
            return 0;
        }
        if ($this->entityCategory === self::UNIVERSITY) {
            foreach ($this->eduPersonScopedAffiliation as $affiliation) {
                if (preg_match(
                    '/(^employee@.+\.cz$)|' .
                    '(^faculty@.+\.cz$)|' .
                    '(^member@.+\.cz$)|' .
                    '(^student@.+\.cz$)|' .
                    '(^staff@.+\.cz$)|' .
                    '(^alum@.+\.cz$)/',
                    $affiliation,
                    $matches
                )) {
                    return 2;
                }
            }
        } elseif ($this->entityCategory === self::AVCR) {
            foreach ($this->eduPersonScopedAffiliation as $affiliation) {
                if (preg_match('/^member@.+\.cz$/', $affiliation, $matches)) {
                    return 2;
                }
            }
        } elseif ($this->entityCategory === self::LIBRARY) {
            foreach ($this->eduPersonScopedAffiliation as $affiliation) {
                if (preg_match('/^employee@.+\.cz$/', $affiliation, $matches)) {
                    return 2;
                }
            }
        } elseif ($this->entityCategory === self::HOSPITAL) {
            foreach ($this->eduPersonScopedAffiliation as $affiliation) {
                if (preg_match('/^employee@.+\.cz$/', $affiliation, $matches)) {
                    return 2;
                }
            }
        } elseif ($this->entityCategory === self::OTHER) {
            foreach ($this->eduPersonScopedAffiliation as $affiliation) {
                if (preg_match('/(^employee@.+\.cz$)|(^member@.+\.cz$)/', $affiliation, $matches)) {
                    return 2;
                }
            }
        }

        return 0;
    }
}
