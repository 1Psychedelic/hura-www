<?php

use Hafo\DI\Container;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\IdentifiedBy;

return [
    Configuration::class => function (Container $c) {
        return Configuration::forSymmetricSigner(
            new Sha256(),
            InMemory::plainText('zipHSm#nk2dZ3LWpH2AnpL880HgcsJ%%#6rMGK!wSh7l6GSfdI')
        );
    },

    'jwt.refreshToken.cookie' => function (Container $c) {
        return 'refreshToken=%s; Secure; HttpOnly; Domain=hura-tabory.cz; Path=/; Max-Age=7889231; SameSite=Lax';
    }
];
