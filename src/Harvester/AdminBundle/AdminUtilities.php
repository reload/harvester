<?php
namespace Harvester\AdminBundle;

use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;

class AdminUtilities
{
    public function generatePassword() {
        $generator = new UriSafeTokenGenerator();
        $token = $generator->generateToken();
        return substr($token, 0, 6);
    }
}

