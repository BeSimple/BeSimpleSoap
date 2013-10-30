# BeSimpleSoapClient

The BeSimpleSoapClient is a component that extends the native PHP SoapClient with further features like SwA, MTOM and WS-Security.

# Features (only subsets of the linked specs implemented)

* SwA: SOAP Messages with Attachments [Spec](http://www.w3.org/TR/SOAP-attachments)
* MTOM: SOAP Message Transmission Optimization Mechanism [Spec](http://www.w3.org/TR/soap12-mtom/)
* WS-Security [Spec1](http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-soap-message-security-1.0.pdf), [Spec2](http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0.pdf)
* WS-Adressing [Spec](http://www.w3.org/2002/ws/addr/)

# Installation

If you do not yet have composer, install it like this:

```sh
curl -s http://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin
```

Create a `composer.json` file:

```json
{
    "require": {
        "besimple/soap-client": "0.2.*@dev"
    }
}
```

Now you are ready to install the library:

```sh
php /usr/local/bin/composer.phar install
```
