<?php

namespace BeSimple\SoapClient;

@trigger_error('Deprecated after version v1.1.4 smartbox/besimple-soap', E_USER_DEPRECATED);

/**
 * Class BasicAuthSoapClient
 * @deprecated Deprecated after version v1.1.4 of smartbox/besimple-soap
 * @package \BeSimple\SoapClient
 */
class BasicAuthSoapClient extends SoapClient
{
    /**
     * {@inheritDoc}
     */
    protected function filterRequestHeaders(SoapRequest $soapRequest, array $headers)
    {
        if (isset($this->_login) && isset($this->_password)) {
            $authToken = base64_encode(sprintf('%s:%s', $this->_login, $this->_password));
            $headers[] = sprintf('Authorization: Basic %s', $authToken);
        }

        return parent::filterRequestHeaders($soapRequest, $headers);
    }
}
