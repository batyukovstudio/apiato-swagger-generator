<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Enums;

class SchemaParameterFormatsEnum
{
    const DATE = 'data';
    const TIME = 'time';
    const DATE_TIME = 'date-time';
    const DURATION = 'duration';
    const REGEX = 'regex';
    const EMAIL = 'email';
    const IDN_EMAIL = 'idn-email';
    const HOSTNAME = 'hostname';
    const IDN_HOSTNAME = 'idn-hostname';
    const IPV4 = 'ipv4';
    const IPV6 = 'ipv6';
    const JSON_POINTER = 'json-pointer';
    const RELATIVE_JSON_POINTER = 'relative-json-pointer';
    const URI = 'uri-reference';
    const URI_REFERENCE = 'uri-reference';
    const URI_TEMPLATE = 'uri-template';
    const IRI = 'iri';
    const IRI_REFERENCE = 'iri-reference';
    const UUID = 'uuid';
}
